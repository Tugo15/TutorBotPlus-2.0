<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certamenes;
use Illuminate\Support\Facades\DB;
use App\Models\Cursos;
use App\Models\ResolucionCertamenes;
use App\Models\SeleccionProblemasCertamenes;
use App\Models\Problemas;
use App\Models\EnvioSolucionProblema;
use App\Models\Resolver;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

class CertamenesController extends Controller
{
    public function index(Request $request){
        $userCursos = auth()->user()->cursos();
        $cursos = $userCursos->withCount('certamenes')->get();
        $id_curso_activo = $request->input('id_curso');
        $curso_activo = null;

        if ($id_curso_activo) {
            $curso_activo = Cursos::find($id_curso_activo);
            $query = Certamenes::where('id_curso', $id_curso_activo);
        } else {
            $cursos_auth = $cursos->pluck('id')->toArray();
            $query = Certamenes::whereIn('id_curso', $cursos_auth);
        }

        $certamenes = $query->get()->map(function ($item){
            $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            $item->fecha_termino = Carbon::parse($item->fecha_termino)->locale('es_ES')->isoFormat('lll');
            $item->creado = Carbon::parse($item->created_at)->locale('es_ES')->isoFormat('lll');
            return $item;
        });

        return view('certamen.index', compact('certamenes', 'cursos', 'id_curso_activo', 'curso_activo'));
    }

    public function crear(Request $request){
        $cursos = auth()->user()->cursos()->get();
        $id_curso_preseleccionado = $request->input('id_curso', $request->input('curso'));

        if (!$id_curso_preseleccionado && $cursos->isNotEmpty()) {
            $id_curso_preseleccionado = $cursos->first()->id;
        }

        if ($id_curso_preseleccionado) {
            $curso_modelo = Cursos::find($id_curso_preseleccionado);
            $todos_problemas = $curso_modelo ? $curso_modelo->problemas()->with('categorias')->get() : Problemas::with('categorias')->get();
        } else {
            return redirect()->route('certamen.index')->with('error', 'Debe seleccionar un curso para crear una evaluación.');
        }
        $categorias_existentes = \App\Models\Categoria_Problema::orderBy('nombre')->get();
        return view('certamen.crear', compact('cursos', 'todos_problemas', 'id_curso_preseleccionado', 'categorias_existentes'));
    }
    public function editar(Request $request){
        $cursos = auth()->user()->cursos()->get();
        $certamen = Certamenes::find($request->id);
        $certamen->fecha_inicio = Carbon::parse($certamen->fecha_inicio);
        $certamen->fecha_termino = Carbon::parse($certamen->fecha_termino);
        $todos_problemas = Problemas::with('categorias')->get();
        $problemas_seleccionados = $certamen->categorias()->pluck('categoria__problemas.id')->toArray();
        $categorias_existentes = \App\Models\Categoria_Problema::orderBy('nombre')->get();
        return view('certamen.editar', compact('cursos', 'certamen', 'todos_problemas', 'problemas_seleccionados', 'categorias_existentes'));
    }
    public function store(Request $request){
        $validated = $request->validate(Certamenes::$rules);
        try{
            DB::beginTransaction();
            $certamen = new Certamenes;
            $certamen->nombre = $request->input("nombre");
            $certamen->descripcion = $request->input("descripcion");
            $certamen->fecha_inicio = Carbon::parse($request->input("fecha_inicio"));
            $certamen->fecha_termino = Carbon::parse($request->input("fecha_termino"));
            $certamen->dificultad = $request->input("dificultad", "Medio");
            if(isset($request->penalizacion_error)){
                $certamen->penalizacion_error = $request->input("penalizacion_error");
            }
            if(isset($request->cantidad_penalizacion)){
                $certamen->cantidad_penalizacion = $request->input("cantidad_penalizacion");
            }
            $certamen->restriccion_red = $request->boolean("restriccion_red", false);
            $certamen->ips_autorizadas = $request->input("ips_autorizadas");
            $certamen->curso()->associate(Cursos::find($request->curso));
            
            $problemasSeleccionados = $request->input("problemas", []);
            $certamen->cantidad_problemas = count($problemasSeleccionados);
            $certamen->save();

            if (!empty($problemasSeleccionados)) {
                // Sincronizar categorías asociadas a los problemas seleccionados
                $categoriasIds = DB::table('pertenece')->whereIn('id_problema', $problemasSeleccionados)->pluck('id_categoria')->unique()->toArray();
                $certamen->categorias()->sync($categoriasIds);
            }

            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->withInput()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.index')->with('success', 'La evaluación "'.$certamen->nombre.'" ha sido creada de manera correcta.');
    }

    public function update(Request $request){
        $validated = $request->validate(Certamenes::$rules);
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id);
            $certamen->nombre = $request->input("nombre");
            $certamen->descripcion = $request->input("descripcion");
            $certamen->fecha_inicio = Carbon::parse($request->input("fecha_inicio"));
            $certamen->fecha_termino = Carbon::parse($request->input("fecha_termino"));
            $certamen->dificultad = $request->input("dificultad", "Medio");
            if(isset($request->penalizacion_error)){
                $certamen->penalizacion_error = $request->input("penalizacion_error");
            }
            if(isset($request->cantidad_penalizacion)){
                $certamen->cantidad_penalizacion = $request->input("cantidad_penalizacion");
            }
            $certamen->restriccion_red = $request->boolean("restriccion_red", false);
            $certamen->ips_autorizadas = $request->input("ips_autorizadas");
            if($certamen->curso->id != $request->input('curso')){
                $certamen->curso()->dissociate();
                $certamen->curso()->associate(Cursos::find($request->input("curso")));
            }

            $problemasSeleccionados = $request->input("problemas", []);
            $certamen->cantidad_problemas = count($problemasSeleccionados);
            $certamen->save();

            if (!empty($problemasSeleccionados)) {
                $categoriasIds = DB::table('pertenece')->whereIn('id_problema', $problemasSeleccionados)->pluck('id_categoria')->unique()->toArray();
                $certamen->categorias()->sync($categoriasIds);
            }

            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->withInput()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.index')->with('success', "La evaluación ha sido actualizada correctamente.");
    }

    public function eliminar(Request $request){
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id);
            $certamen->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.index')->with('success', 'La evaluación "'.$certamen->nombre.'" ha sido eliminado.');
    }

    public function duplicar(Request $request){
        $request->validate([
            'id_certamen' => 'required|exists:certamenes,id',
            'id_curso' => 'required|exists:cursos,id',
        ]);
        try {
            DB::beginTransaction();
            $original = Certamenes::with('categorias')->findOrFail($request->id_certamen);
            $nuevo = new Certamenes();
            $nuevo->nombre = $original->nombre . ' (Copia)';
            $nuevo->descripcion = $original->descripcion;
            $nuevo->fecha_inicio = Carbon::now();
            $nuevo->fecha_termino = Carbon::now()->addDays(7);
            $nuevo->penalizacion_error = $original->penalizacion_error;
            $nuevo->cantidad_penalizacion = $original->cantidad_penalizacion;
            $nuevo->restriccion_red = $original->restriccion_red;
            $nuevo->ips_autorizadas = $original->ips_autorizadas;
            $nuevo->dificultad = $original->dificultad ?? 'Medio';
            $nuevo->cantidad_problemas = $original->cantidad_problemas ?? 0;
            $nuevo->curso()->associate(Cursos::findOrFail($request->id_curso));
            $nuevo->save();

            if ($original->categorias->isNotEmpty()) {
                $nuevo->categorias()->sync($original->categorias->pluck('id')->toArray());
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with("error", "Error al duplicar la evaluación: " . $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with("error", "Error inesperado al duplicar: " . $e->getMessage());
        }
        return redirect()->route('certamen.index', ['id_curso' => $nuevo->id_curso])
            ->with('success', 'La evaluación "' . $nuevo->nombre . '" ha sido duplicada exitosamente.');
    }

    public function listado_certamenes(Request $request){
        try{
            $id_curso_activo = $request->input('id_curso');
            $cursos = auth()->user()->cursos()->withCount('certamenes')->get();
            $curso_activo = null;

            if ($id_curso_activo) {
                $curso_activo = Cursos::find($id_curso_activo);
                $cursos_filtro = [$id_curso_activo];
            } else {
                $cursos_filtro = $cursos->pluck('id')->toArray();
            }

            $resultados_certamenes = DB::table('resolucion_certamenes')
            ->leftJoin('envio_solucion_problemas', 'envio_solucion_problemas.id_certamen', '=', 'resolucion_certamenes.id')
            ->where('id_usuario','=', auth()->user()->id)
            ->select('resolucion_certamenes.id_certamen',  DB::raw('max(finalizado) as estado_finalizado'), 'fecha_finalizado', DB::raw('max(puntaje_obtenido) as puntaje_maximo'), DB::raw('max(problemas_resueltos) as maximo_resuelto'))
            ->groupBy('resolucion_certamenes.id_certamen', 'fecha_finalizado')
            ->orderBy('fecha_finalizado', 'desc');

            $evaluaciones = Certamenes::leftJoinSub($resultados_certamenes, 'resultados_certamenes', function (JoinClause $join){
                $join->on('resultados_certamenes.id_certamen', '=', 'certamenes.id');
            })->whereIn('id_curso', $cursos_filtro)->orderBy('fecha_inicio', 'desc')->get()->map(function($item){
                $fecha_inicio = Carbon::parse($item->fecha_inicio);
                $item->fecha_inicio = Carbon::parse( $item->fecha_inicio)->locale('es_ES')->isoFormat('lll');
                $item->fecha_termino = Carbon::parse( $item->fecha_termino)->locale('es_ES')->isoFormat('lll');
                if(isset($item->fecha_finalizado)){
                    $fecha_termino = Carbon::parse($item->fecha_finalizado);
                    $item->tiempo_desarrollo = $fecha_termino->diffInSeconds($fecha_inicio);
                }
                return $item;
            });
            
        }catch(\PDOException $e){
            return redirect()->route('cursos.listado')->with("error", $e->getMessage());
        }
        return view('plataforma.certamen.index', compact('evaluaciones', 'cursos', 'id_curso_activo', 'curso_activo'));
    }

    public function ver_certamen(Request $request){
        try{
            $certamen = Certamenes::find($request->id_certamen);
            $_now = Carbon::now();
            $certamen->disponibilidad = true;
            if(!($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino)))){
                $certamen->disponibilidad = false;
            }
            if($certamen->categorias()->count()==0){
                $certamen->disponibilidad = false;
            }
            $res_certamen = $certamen->resoluciones()->with(['ProblemasSeleccionadas','envios'])->where('id_usuario', '=', auth()->user()->id)->first();
            $resultado = null;
            if(isset($res_certamen)){
                $resultado_certamenes = DB::table('envio_solucion_problemas')
                ->leftJoin('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')
                ->select('resolver.id_problema', DB::raw('count(envio_solucion_problemas.id) as cantidad_intentos'), DB::raw('COALESCE(max(envio_solucion_problemas.puntaje),0) as maximo_puntaje'), DB::raw('COALESCE(max(solucionado),0) as resuelto'), DB::raw('COALESCE(max(cant_casos_resuelto), 0) as max_casos_resueltos'))
                ->where('envio_solucion_problemas.id_certamen', '=', $res_certamen->id)
                ->whereNotNull('termino')
                ->groupBy('resolver.id_problema');

                $resultado = DB::table('problemas')
                ->join('casos__pruebas', 'casos__pruebas.id_problema', '=', 'problemas.id')
                ->leftJoinSub($resultado_certamenes,'resultados_certamenes','problemas.id', '=', 'resultados_certamenes.id_problema')
                ->select('problemas.id', 'problemas.nombre', DB::raw('sum(casos__pruebas.puntos) as puntos_total'), DB::raw('count(casos__pruebas.id) as total_casos'), DB::raw('COALESCE(cantidad_intentos, 0) as cantidad_intentos'), DB::raw('COALESCE(maximo_puntaje, 0) as maximo_puntaje'), DB::raw('COALESCE(resuelto, 0) as resuelto'),DB::raw('COALESCE(max_casos_resueltos, 0) as max_casos_resueltos'))
                ->groupBy('problemas.id', 'problemas.nombre', 'cantidad_intentos', 'maximo_puntaje', 'resuelto', 'max_casos_resueltos')
                ->whereIn('problemas.id',$res_certamen->ProblemasSeleccionadas()->pluck('id_problema'))
                ->get()->map(function($item) use($certamen){
                    if($item->cantidad_intentos>0){
                        $errores = $item->cantidad_intentos - 1 <= $certamen->cantidad_penalizacion? $item->cantidad_intentos - 1 : $certamen->cantidad_penalizacion;
                        $item->maximo_puntaje = $item->maximo_puntaje - ($errores*$certamen->penalizacion_error);
                    }
                    return $item;
                });
            }
            
            $certamen->fecha_inicio = Carbon::parse( $certamen->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            $certamen->fecha_termino = Carbon::parse( $certamen->fecha_termino)->locale('es_ES')->isoFormat('lll');
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        return view('plataforma.certamen.ver_certamen', compact('certamen', 'res_certamen', 'resultado'));
    }

    public function inicializar_certamen(Request $request){
        try{
            DB::beginTransaction();
            $res_certamen = auth()->user()->evaluaciones()->where('id_certamen','=',$request->id_certamen)->first();
            if(isset($res_certamen)){
                if($res_certamen->finalizado == true){
                    throw new \Exception("Error: Ya has resuelto este certamen");
                }
            }else{
                $certamen = Certamenes::find($request->id_certamen);
                if (!$certamen) {
                    throw new \Exception("Error: La evaluación seleccionada no existe.");
                }

                $res_certamen = new ResolucionCertamenes;
                $res_certamen->token = Str::random(55);
                $res_certamen->id_usuario = auth()->user()->id;
                $certamen->resoluciones()->save($res_certamen);
                
                $categorias = $certamen->categorias()->get();
                $problemas_seleccionados = [];
                $problemas_seleccionados_id = [];
                foreach ($categorias as $categoria){
                    // Selecciona un problema aleatorio de la categoría para este curso
                    $problema_aleatorio = $categoria->problemas()
                        ->join('disponible', 'disponible.id_problema', '=', 'problemas.id')
                        ->where('disponible.id_curso', '=', $certamen->id_curso)
                        ->whereNotIn('problemas.id', $problemas_seleccionados_id)
                        ->select('problemas.*')
                        ->inRandomOrder()->first();

                    // Fallback: Si la categoría no tiene un problema disponible sin asignar, buscar cualquier problema disponible del curso
                    if (!$problema_aleatorio) {
                        $problema_aleatorio = Problemas::join('disponible', 'disponible.id_problema', '=', 'problemas.id')
                            ->where('disponible.id_curso', '=', $certamen->id_curso)
                            ->whereNotIn('problemas.id', $problemas_seleccionados_id)
                            ->select('problemas.*')
                            ->inRandomOrder()->first();
                    }

                    if ($problema_aleatorio) {
                        $seleccion = new SeleccionProblemasCertamenes;
                        $seleccion->problema()->associate($problema_aleatorio);
                        array_push($problemas_seleccionados, $seleccion);        
                        array_push($problemas_seleccionados_id, $problema_aleatorio->id);
                    }
                }

                if (empty($problemas_seleccionados)) {
                    throw new \Exception("Error: No hay problemas disponibles configurados para esta evaluación.");
                }

                $res_certamen->ProblemasSeleccionadas()->saveMany($problemas_seleccionados);
            }
            DB::commit();
        }catch(\PDOException $e){
            DB::rollback();
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        return redirect()->route('certamenes.resolucion', ['token'=>$res_certamen->token]);
    }

    public function get_ultimos_envios(ResolucionCertamenes $res_certamen){
            $ultimos_envios = DB::table('envio_solucion_problemas')
            ->leftJoin('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
            ->leftJoin('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->where('envio_solucion_problemas.id_certamen', '=', $res_certamen->id)
            ->where('cursa.id_usuario', '=', auth()->user()->id)
            ->select('resolver.id_problema', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.puntaje');
            $problemas = Problemas::with(['lenguajes', 'casos_de_prueba' => function($q) {
                $q->where('ejemplo', true);
            }])->leftJoinSub($ultimos_envios, 'ultimos_envios', function (JoinClause $join){
                $join->on('ultimos_envios.id_problema', '=', 'problemas.id');
            })
            ->whereIn('problemas.id', $res_certamen->ProblemasSeleccionadas()->pluck('id_problema')->toArray())
            ->select('problemas.nombre', 'problemas.id', 'problemas.codigo','problemas.body_problema', 'problemas.memoria_limite', 'problemas.tiempo_limite', 'problemas.puntaje_total', DB::raw('max(ultimos_envios.solucionado) as resuelto'), DB::raw('max(ultimos_envios.puntaje) as puntaje_maximo'))
            ->groupBy('problemas.nombre', 'problemas.id','problemas.codigo' , 'problemas.body_problema', 'problemas.memoria_limite', 'problemas.tiempo_limite', 'problemas.puntaje_total')
            ->get()->map(function($item) use($res_certamen){
                $item->resolver_ruta = route('certamenes.resolver_problema', ['token_certamen'=>$res_certamen->token, 'codigo'=>$item->codigo, 'id_curso'=>$res_certamen->certamen->id_curso]);
                $item->pdf_ruta = route('problemas.pdf_enunciado', ['id_problema'=>$item->id]);
                return $item;
            });
            return $problemas;
    }

    public function obtener_ultimos_envios_json(Request $request){
        try{
            $res_certamen = ResolucionCertamenes::with(['certamen', 'ProblemasSeleccionadas'])->where('token', '=', $request->token)->first();
            $problemas = $this->get_ultimos_envios($res_certamen);
        }catch(\PDOException $e){
            return response($e->getMessage(), 500);
        }
        return response()->json($problemas, 200);
    }

    public function resolver_certamen(Request $request){
        try{
            $res_certamen = ResolucionCertamenes::with(['certamen', 'ProblemasSeleccionadas'])->where('token', '=', $request->token)->first();
            if(!isset($res_certamen)){
                throw new \Exception("Error: El token de resolución de certamen no existe");
            }
            if($res_certamen->id_usuario != auth()->user()->id){
                throw new \Exception("Error: No tienes permiso para acceder a esta resolución de certamen.");
            }

            $problemas = $this->get_ultimos_envios($res_certamen);
            
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }catch(\Exception $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
            return view('plataforma.certamen.ver_problemas', compact('problemas', 'res_certamen'));
    }

    public function finalizar_certamen(Request $request){
        try{
            $res_certamen = ResolucionCertamenes::where('token', '=', $request->token)->first();
            $res_certamen->finalizar_certamen();
        }catch(\PDOException $e){
            return redirect()->route('certamenes.ver', ['id_certamen'=>$res_certamen->id_certamen])->with("error", $e->getMessage());
        }
            return redirect()->route('certamenes.ver', ['id_certamen'=>$res_certamen->id_certamen])->with("success", "Has finalizado el certamen");
    }
    

    public function guardar_codigo_certamen(Request $request){
        try{
            DB::beginTransaction();
            $last_envio = EnvioSolucionProblema::where('id_certamen', '=', $request->id_certamen)->orderBy('created_at', 'DESC')->first();
            $last_envio->codigo = $request->codigo_save;
            if(isset($request->lenguaje_save)){
                $resolver = Resolver::where('id_problema', '=', $request->id_problema)->where('id_lenguaje', '=', $request->lenguaje_save)->first();
                $last_envio->ProblemaLenguaje()->dissociate();
                $last_envio->ProblemaLenguaje()->associate($resolver);
            }
            $last_envio->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('certamenes.resolucion', ['token'=>$request->token_certamen])->with('error', $e->getMessage());
        }
        return redirect()->route('certamenes.resolucion', ['token'=>$request->token_certamen])->with('succes', 'El código desarrollado ha sido almacenado');
    }
}


