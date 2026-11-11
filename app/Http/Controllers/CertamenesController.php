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
            $todos_problemas = $curso_modelo ? $curso_modelo->problemas()->with('categorias')->get() : collect();
        } else {
            return redirect()->route('certamen.index')->with('error', 'Debe seleccionar un curso para crear una evaluación.');
        }
        $categorias_existentes = \App\Models\Categoria_Problema::orderBy('nombre')->get();
        $bancos_preguntas = [
            [
                'numero' => 1,
                'id_categoria' => null,
                'nombre_banco' => 'Pregunta 1',
                'problemas' => []
            ]
        ];
        return view('certamen.crear', compact('cursos', 'todos_problemas', 'id_curso_preseleccionado', 'categorias_existentes', 'bancos_preguntas'));
    }
    public function editar(Request $request){
        $cursos = auth()->user()->cursos()->get();
        $certamen = Certamenes::find($request->id);
        if (!$certamen) {
            return redirect()->route('certamen.index')->with('error', 'La evaluación no existe.');
        }
        $certamen->fecha_inicio = Carbon::parse($certamen->fecha_inicio);
        $certamen->fecha_termino = Carbon::parse($certamen->fecha_termino);
        $todos_problemas = $certamen->curso ? $certamen->curso->problemas()->with('categorias')->get() : collect();
        
        $bancos_preguntas = [];
        if (!empty($certamen->bancos_preguntas) && is_array($certamen->bancos_preguntas)) {
            $bancos_preguntas = $certamen->bancos_preguntas;
        } else {
            // Reconstruir bancos a partir de las categorías vinculadas al certamen (o problemas seleccionados)
            $categoriasCert = $certamen->categorias()->get();
            if ($categoriasCert->isNotEmpty()) {
                $num = 1;
                foreach ($categoriasCert as $cat) {
                    $probsCat = $todos_problemas->filter(function($p) use ($cat) {
                        return $p->categorias && $p->categorias->contains('id', $cat->id);
                    })->pluck('id')->values()->toArray();

                    $bancos_preguntas[] = [
                        'numero' => $num++,
                        'id_categoria' => $cat->id,
                        'nombre_banco' => $cat->nombre,
                        'problemas' => $probsCat
                    ];
                }
            } elseif (!empty($certamen->problemas_seleccionados) && is_array($certamen->problemas_seleccionados)) {
                $num = 1;
                foreach ($certamen->problemas_seleccionados as $probId) {
                    $bancos_preguntas[] = [
                        'numero' => $num++,
                        'id_categoria' => null,
                        'nombre_banco' => 'Pregunta ' . ($num - 1),
                        'problemas' => [(int)$probId]
                    ];
                }
            } else {
                $bancos_preguntas[] = [
                    'numero' => 1,
                    'id_categoria' => null,
                    'nombre_banco' => 'Pregunta 1',
                    'problemas' => []
                ];
            }
        }

        $categorias_existentes = \App\Models\Categoria_Problema::orderBy('nombre')->get();
        return view('certamen.editar', compact('cursos', 'certamen', 'todos_problemas', 'categorias_existentes', 'bancos_preguntas'));
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
            $certamen->password = $request->filled("password") ? $request->input("password") : null;
            $certamen->curso()->associate(Cursos::find($request->curso));
            
            // Procesar estructura de bancos por pregunta
            $bancosRaw = $request->input('bancos_preguntas_json');
            $bancosInput = $bancosRaw ? json_decode($bancosRaw, true) : $request->input('bancos', []);

            $bancosGuardar = [];
            $todosProblemasIds = [];
            $todasCategoriasIds = [];

            if (!empty($bancosInput) && is_array($bancosInput)) {
                $num = 1;
                foreach ($bancosInput as $b) {
                    $probIds = isset($b['problemas']) && is_array($b['problemas'])
                        ? array_values(array_filter(array_map('intval', $b['problemas'])))
                        : [];
                    
                    $catId = !empty($b['id_categoria']) ? (int)$b['id_categoria'] : null;
                    if (empty($probIds) && $catId) {
                        $probIds = DB::table('pertenece')
                            ->join('disponible', 'disponible.id_problema', '=', 'pertenece.id_problema')
                            ->where('pertenece.id_categoria', $catId)
                            ->where('disponible.id_curso', $request->input('curso'))
                            ->pluck('pertenece.id_problema')
                            ->unique()
                            ->map(fn($id) => (int)$id)
                            ->values()
                            ->toArray();
                    }

                    if (!empty($probIds)) {
                        $nombreBanco = !empty($b['nombre_banco']) ? $b['nombre_banco'] : ("Pregunta " . $num);
                        $bancosGuardar[] = [
                            'numero' => $num++,
                            'id_categoria' => $catId,
                            'nombre_banco' => $nombreBanco,
                            'problemas' => $probIds
                        ];
                        $todosProblemasIds = array_merge($todosProblemasIds, $probIds);
                        if ($catId) {
                            $todasCategoriasIds[] = $catId;
                        }
                    }
                }
            }

            // Fallback en caso de que venga como selección plana antigua
            if (empty($bancosGuardar) && $request->filled('problemas')) {
                $problemasSeleccionados = array_values(array_filter(array_map('intval', $request->input("problemas", []))));
                $todosProblemasIds = $problemasSeleccionados;
                $num = 1;
                foreach ($problemasSeleccionados as $pid) {
                    $bancosGuardar[] = [
                        'numero' => $num++,
                        'id_categoria' => null,
                        'nombre_banco' => 'Pregunta ' . ($num - 1),
                        'problemas' => [$pid]
                    ];
                }
            }

            $todosProblemasIds = array_values(array_unique($todosProblemasIds));
            $certamen->cantidad_problemas = !empty($bancosGuardar) ? count($bancosGuardar) : max(1, (int)$request->input('cantidad_problemas', 1));
            $certamen->bancos_preguntas = !empty($bancosGuardar) ? $bancosGuardar : null;
            $certamen->problemas_seleccionados = !empty($todosProblemasIds) ? $todosProblemasIds : null;
            $certamen->save();

            if (!empty($todosProblemasIds)) {
                $catsFromProbs = DB::table('pertenece')->whereIn('id_problema', $todosProblemasIds)->pluck('id_categoria')->toArray();
                $todasCategoriasIds = array_unique(array_merge($todasCategoriasIds, $catsFromProbs));
                $certamen->categorias()->sync($todasCategoriasIds);
            } else {
                $certamen->categorias()->detach();
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
            $certamen->password = $request->filled("password") ? $request->input("password") : null;
            if($certamen->curso->id != $request->input('curso')){
                $certamen->curso()->dissociate();
                $certamen->curso()->associate(Cursos::find($request->input("curso")));
            }

            // Procesar estructura de bancos por pregunta
            $bancosRaw = $request->input('bancos_preguntas_json');
            $bancosInput = $bancosRaw ? json_decode($bancosRaw, true) : $request->input('bancos', []);

            $bancosGuardar = [];
            $todosProblemasIds = [];
            $todasCategoriasIds = [];

            if (!empty($bancosInput) && is_array($bancosInput)) {
                $num = 1;
                foreach ($bancosInput as $b) {
                    $probIds = isset($b['problemas']) && is_array($b['problemas'])
                        ? array_values(array_filter(array_map('intval', $b['problemas'])))
                        : [];
                    
                    $catId = !empty($b['id_categoria']) ? (int)$b['id_categoria'] : null;
                    if (empty($probIds) && $catId) {
                        $probIds = DB::table('pertenece')
                            ->join('disponible', 'disponible.id_problema', '=', 'pertenece.id_problema')
                            ->where('pertenece.id_categoria', $catId)
                            ->where('disponible.id_curso', $request->input('curso', $certamen->id_curso))
                            ->pluck('pertenece.id_problema')
                            ->unique()
                            ->map(fn($id) => (int)$id)
                            ->values()
                            ->toArray();
                    }

                    if (!empty($probIds)) {
                        $nombreBanco = !empty($b['nombre_banco']) ? $b['nombre_banco'] : ("Pregunta " . $num);
                        $bancosGuardar[] = [
                            'numero' => $num++,
                            'id_categoria' => $catId,
                            'nombre_banco' => $nombreBanco,
                            'problemas' => $probIds
                        ];
                        $todosProblemasIds = array_merge($todosProblemasIds, $probIds);
                        if ($catId) {
                            $todasCategoriasIds[] = $catId;
                        }
                    }
                }
            }

            // Fallback en caso de que venga como selección plana antigua
            if (empty($bancosGuardar) && $request->filled('problemas')) {
                $problemasSeleccionados = array_values(array_filter(array_map('intval', $request->input("problemas", []))));
                $todosProblemasIds = $problemasSeleccionados;
                $num = 1;
                foreach ($problemasSeleccionados as $pid) {
                    $bancosGuardar[] = [
                        'numero' => $num++,
                        'id_categoria' => null,
                        'nombre_banco' => 'Pregunta ' . ($num - 1),
                        'problemas' => [$pid]
                    ];
                }
            }

            $todosProblemasIds = array_values(array_unique($todosProblemasIds));
            $certamen->cantidad_problemas = !empty($bancosGuardar) ? count($bancosGuardar) : max(1, (int)$request->input('cantidad_problemas', 1));
            $certamen->bancos_preguntas = !empty($bancosGuardar) ? $bancosGuardar : null;
            $certamen->problemas_seleccionados = !empty($todosProblemasIds) ? $todosProblemasIds : null;
            $certamen->save();

            if (!empty($todosProblemasIds)) {
                $catsFromProbs = DB::table('pertenece')->whereIn('id_problema', $todosProblemasIds)->pluck('id_categoria')->toArray();
                $todasCategoriasIds = array_unique(array_merge($todasCategoriasIds, $catsFromProbs));
                $certamen->categorias()->sync($todasCategoriasIds);
            } else {
                $certamen->categorias()->detach();
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
            $original = Certamenes::with('categorias', 'curso')->findOrFail($request->id_certamen);
            $cursoDestino = Cursos::findOrFail($request->id_curso);

            $nuevo = new Certamenes();
            $nuevo->nombre = $original->nombre . ' (Copia)';
            $nuevo->descripcion = $original->descripcion;
            $nuevo->fecha_inicio = $request->filled('fecha_inicio') ? Carbon::parse($request->input('fecha_inicio')) : Carbon::parse($original->fecha_inicio);
            $nuevo->fecha_termino = $request->filled('fecha_termino') ? Carbon::parse($request->input('fecha_termino')) : Carbon::parse($original->fecha_termino);
            $nuevo->penalizacion_error = $original->penalizacion_error;
            $nuevo->cantidad_penalizacion = $original->cantidad_penalizacion;
            $nuevo->restriccion_red = $original->restriccion_red;
            $nuevo->ips_autorizadas = $original->ips_autorizadas;
            $nuevo->password = $original->password;
            $nuevo->dificultad = $original->dificultad ?? 'Medio';

            // Copiar configuración de bancos de preguntas y variantes
            $bancosPreguntas = $original->bancos_preguntas;
            $problemasSeleccionados = $original->problemas_seleccionados;

            // Si el original no tenía bancos_preguntas explícitos, reconstruirlos a partir de sus categorías o problemas
            if (empty($bancosPreguntas)) {
                $categoriasCert = $original->categorias()->get();
                if ($categoriasCert->isNotEmpty()) {
                    $todosProblemas = $original->curso ? $original->curso->problemas()->with('categorias')->get() : collect();
                    $num = 1;
                    $bancosPreguntas = [];
                    foreach ($categoriasCert as $cat) {
                        $probsCat = $todosProblemas->filter(function($p) use ($cat) {
                            return $p->categorias && $p->categorias->contains('id', $cat->id);
                        })->pluck('id')->values()->toArray();

                        $bancosPreguntas[] = [
                            'numero' => $num++,
                            'id_categoria' => $cat->id,
                            'nombre_banco' => $cat->nombre,
                            'problemas' => $probsCat
                        ];
                    }
                } elseif (!empty($problemasSeleccionados) && is_array($problemasSeleccionados)) {
                    $num = 1;
                    $bancosPreguntas = [];
                    foreach ($problemasSeleccionados as $probId) {
                        $bancosPreguntas[] = [
                            'numero' => $num++,
                            'id_categoria' => null,
                            'nombre_banco' => 'Pregunta ' . ($num - 1),
                            'problemas' => [(int)$probId]
                        ];
                    }
                }
            }

            // Si no tenía problemas_seleccionados pero sí bancos, extraer todos los IDs de variantes
            if (empty($problemasSeleccionados) && !empty($bancosPreguntas)) {
                $todosIds = [];
                foreach ($bancosPreguntas as $b) {
                    if (!empty($b['problemas'])) {
                        $todosIds = array_merge($todosIds, $b['problemas']);
                    }
                }
                $problemasSeleccionados = array_values(array_unique($todosIds));
            }

            $nuevo->cantidad_problemas = !empty($bancosPreguntas) ? count($bancosPreguntas) : ($original->cantidad_problemas ?? 1);
            $nuevo->bancos_preguntas = !empty($bancosPreguntas) ? $bancosPreguntas : null;
            $nuevo->problemas_seleccionados = !empty($problemasSeleccionados) ? $problemasSeleccionados : null;
            $nuevo->curso()->associate($cursoDestino);
            $nuevo->save();

            // Sincronizar categorías asociadas
            if ($original->categorias->isNotEmpty()) {
                $nuevo->categorias()->sync($original->categorias->pluck('id')->toArray());
            }

            // Asegurar que los problemas involucrados estén disponibles en el curso de destino (tabla disponible)
            if (!empty($problemasSeleccionados)) {
                foreach ($problemasSeleccionados as $probId) {
                    if (!$cursoDestino->problemas()->where('problemas.id', $probId)->exists()) {
                        $cursoDestino->problemas()->attach($probId, ['visible' => false]);
                    }
                }
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
                ->get()->map(function($item) use($certamen, $res_certamen){
                    if($item->cantidad_intentos>0){
                        $errores = $item->cantidad_intentos - 1 <= $certamen->cantidad_penalizacion? $item->cantidad_intentos - 1 : $certamen->cantidad_penalizacion;
                        $item->maximo_puntaje = $item->maximo_puntaje - ($errores*$certamen->penalizacion_error);
                    }
                    $item->variacion_formato = false;
                    if ($item->resuelto) {
                        $item->variacion_formato = DB::table('evaluacion_solucions')
                            ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'evaluacion_solucions.id_envio')
                            ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
                            ->where('envio_solucion_problemas.id_certamen', '=', $res_certamen->id)
                            ->where('resolver.id_problema', '=', $item->id)
                            ->where('envio_solucion_problemas.solucionado', '=', true)
                            ->where('evaluacion_solucions.resultado', '=', 'Aceptado (Variación de Formato)')
                            ->exists();
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

                // Verificar contraseña si la evaluación está protegida
                if (!empty($certamen->password)) {
                    $passwordInput = $request->input('password');
                    if (empty($passwordInput) || $passwordInput !== $certamen->password) {
                        return redirect()->route('certamenes.ver', ['id_certamen' => $certamen->id])
                            ->with('error', 'Contraseña incorrecta. Por favor ingrese la clave de acceso proporcionada por el docente.');
                    }
                }

                $res_certamen = new ResolucionCertamenes;
                $res_certamen->token = Str::random(55);
                $res_certamen->id_usuario = auth()->user()->id;
                $certamen->resoluciones()->save($res_certamen);
                
                $problemas_seleccionados = [];
                $problemas_seleccionados_id = [];

                if (!empty($certamen->bancos_preguntas) && is_array($certamen->bancos_preguntas)) {
                    // Modo Bancos por Pregunta: cada pregunta selecciona 1 ejercicio al azar de su banco
                    foreach ($certamen->bancos_preguntas as $banco) {
                        $candidatos = isset($banco['problemas']) && is_array($banco['problemas']) ? $banco['problemas'] : [];
                        // Intentar no repetir problemas ya asignados a este estudiante en otras preguntas
                        $disponibles = array_values(array_diff($candidatos, $problemas_seleccionados_id));
                        if (empty($disponibles)) {
                            $disponibles = $candidatos;
                        }

                        if (!empty($disponibles)) {
                            $randomId = $disponibles[array_rand($disponibles)];
                            $probElegido = Problemas::find($randomId);
                            if ($probElegido) {
                                $seleccion = new SeleccionProblemasCertamenes;
                                $seleccion->problema()->associate($probElegido);
                                $problemas_seleccionados[] = $seleccion;
                                $problemas_seleccionados_id[] = $probElegido->id;
                            }
                        }
                    }
                } elseif (!empty($certamen->problemas_seleccionados) && is_array($certamen->problemas_seleccionados)) {
                    $idsDirectos = array_map('intval', $certamen->problemas_seleccionados);
                    $cantObjetivo = $certamen->cantidad_problemas > 0 ? $certamen->cantidad_problemas : count($idsDirectos);
                    if (count($idsDirectos) <= $cantObjetivo) {
                        $problemasAsignados = Problemas::whereIn('id', $idsDirectos)->get();
                    } else {
                        $problemasAsignados = Problemas::whereIn('id', $idsDirectos)->inRandomOrder()->take($cantObjetivo)->get();
                    }

                    foreach ($problemasAsignados as $prob) {
                        $seleccion = new SeleccionProblemasCertamenes;
                        $seleccion->problema()->associate($prob);
                        array_push($problemas_seleccionados, $seleccion);
                        array_push($problemas_seleccionados_id, $prob->id);
                    }
                } else {
                    $categorias = $certamen->categorias()->get();
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
            $last_envio = null;

            // 1. Buscar por id_envio específico si fue enviado
            if ($request->filled('id_envio')) {
                $last_envio = EnvioSolucionProblema::find($request->id_envio);
            }

            // 2. Si no, buscar por certamen y problema/resolver
            if (!$last_envio && $request->filled('id_certamen')) {
                $query = EnvioSolucionProblema::where('id_certamen', '=', $request->id_certamen);
                if ($request->filled('id_resolver')) {
                    $query->where('id_resolver', '=', $request->id_resolver);
                } elseif ($request->filled('id_problema')) {
                    $query->whereHas('problema', function($q) use ($request) {
                        $q->where('problemas.id', $request->id_problema);
                    });
                }
                $last_envio = $query->orderBy('created_at', 'DESC')->first();
            }

            if ($last_envio) {
                $last_envio->codigo = $request->codigo_save ?? '';

                if ($request->filled('lenguaje_save') && $request->filled('id_problema')) {
                    $resolver = Resolver::where('id_problema', '=', $request->id_problema)
                        ->where(function($q) use ($request) {
                            $q->where('id_lenguaje', '=', $request->lenguaje_save)
                              ->orWhere('id', '=', $request->lenguaje_save);
                        })->first();

                    if ($resolver) {
                        $last_envio->ProblemaLenguaje()->dissociate();
                        $last_envio->ProblemaLenguaje()->associate($resolver);
                    }
                }
                $last_envio->save();
            }

            DB::commit();
        }catch(\Throwable $e){
            DB::rollBack();
            \Log::error("Error en guardar_codigo_certamen: " . $e->getMessage());
            $token = $request->token_certamen ?? ($last_envio && $last_envio->certamen ? $last_envio->certamen->token : null);
            if ($token) {
                return redirect()->route('certamenes.resolucion', ['token' => $token])->with('error', 'Error al guardar: ' . $e->getMessage());
            }
            return back()->with('error', 'Error al guardar: ' . $e->getMessage());
        }

        $token = $request->token_certamen ?? ($last_envio && $last_envio->certamen ? $last_envio->certamen->token : null);
        if ($token) {
            return redirect()->route('certamenes.resolucion', ['token' => $token])->with('success', 'El código desarrollado ha sido almacenado');
        }
        return back()->with('success', 'El código desarrollado ha sido almacenado');
    }
}


