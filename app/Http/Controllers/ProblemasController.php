<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cursos;
use App\Models\Casos_Pruebas;
use App\Models\Resolver;
use App\Models\LenguajesProgramaciones;
use App\Models\Categoria_Problema;
use App\Models\JuecesVirtuales;
use App\Models\ResolucionCertamenes;
use App\Models\Certamenes;
use App\Models\EnvioSolucionProblema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PDF;

class ProblemasController extends Controller
{
    public function index(Request $request)
    {
        $id_curso_activo = $request->input('id_curso');

        if (auth()->user()->hasRole('administrador')) {
            $cursos = Cursos::withCount('problemas')->get();
        } else {
            $cursos = auth()->user()->cursos()->withCount('problemas')->get();
        }

        $curso_activo = null;

        $problemas_disponibles_para_asignar = collect();
        if ($id_curso_activo) {
            $curso_activo = Cursos::find($id_curso_activo);
            $query = Problemas::with(['cursos', 'categorias', 'lenguajes'])->whereHas('cursos', function (Builder $q) use ($id_curso_activo) {
                $q->where('cursos.id', $id_curso_activo);
            });

            // Problemas que NO están en este curso y que pueden asignarse
            $problemas_disponibles_para_asignar = Problemas::with(['categorias', 'lenguajes'])
                ->whereDoesntHave('cursos', function (Builder $q) use ($id_curso_activo) {
                    $q->where('cursos.id', $id_curso_activo);
                })
                ->orderBy('nombre')
                ->get();
        } else {
            if (auth()->user()->hasRole('administrador')) {
                $query = Problemas::with(['cursos', 'categorias', 'lenguajes']);
            } else {
                $cursos_ids = $cursos->pluck('id')->toArray();
                $query = Problemas::with(['cursos', 'categorias', 'lenguajes'])->whereHas('cursos', function (Builder $q) use ($cursos_ids) {
                    $q->whereIn('cursos.id', $cursos_ids);
                });
            }
        }

        $problemas = $query->get()->map(function($item){
            $item->creado = Carbon::parse($item->created_at)->locale('es_ES')->isoFormat('lll');
            $item->fecha_inicio = isset($item->fecha_inicio) ? Carbon::parse($item->fecha_inicio)->locale('es_ES')->isoFormat('lll') : "No definido";
            $item->fecha_termino = isset($item->fecha_termino) ? Carbon::parse($item->fecha_termino)->locale('es_ES')->isoFormat('lll') : "No definido";
            return $item;
        });

        $categorias_filtro = Categoria_Problema::orderBy('nombre')->get();

        return view('problemas.index', compact('problemas', 'cursos', 'id_curso_activo', 'curso_activo', 'problemas_disponibles_para_asignar', 'categorias_filtro'));
    }

    public function crear(Request $request)
    {
        $categorias = Categoria_Problema::orderBy('nombre')->get();
        if (auth()->user()->hasRole('administrador')) {
            $cursos = Cursos::orderBy('nombre')->get();
        } else {
            $cursos = auth()->user()->cursos()->orderBy('nombre')->get();
            if ($cursos->isEmpty()) {
                $cursos = Cursos::orderBy('nombre')->get();
            }
        }

        if ($cursos->isEmpty()) {
            return redirect()->route('problemas.index')->with('error', 'Debe tener al menos un curso asignado para crear un problema.');
        }

        $id_curso_preseleccionado = $request->input('id_curso', $request->input('curso'));

        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.crear', compact('categorias', 'cursos', 'lenguajes', 'id_curso_preseleccionado'))->with('accion', "crear");
    }

    public function editar(Request $request)
    {
        $id_curso = $request->input('id_curso');
        $problema = Problemas::with(['cursos', 'categorias', 'lenguajes'])->find($request->id);
        if (!$problema) {
            if ($id_curso) {
                return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('error', 'El problema no existe.');
            }
            return redirect()->route('problemas.index')->with('error', 'El problema no existe.');
        }
        $problema->sql = $problema->lenguajes()->where('nombre', 'LIKE', '%sql%')->exists();
        if(isset($problema->fecha_inicio)){
            $problema->fecha_inicio = Carbon::parse($problema->fecha_inicio)->format('Y-m-d H:i');
        }
        if(isset($problema->fecha_termino)){
            $problema->fecha_termino = Carbon::parse($problema->fecha_termino)->format('Y-m-d H:i');
        }
        if($problema->memoria_limite==0){
            $problema->memoria_limite = null;
        }
        $categorias = Categoria_Problema::orderBy('nombre')->get();
        if (auth()->user()->hasRole('administrador')) {
            $cursos = Cursos::orderBy('nombre')->get();
        } else {
            $misCursos = auth()->user()->cursos()->orderBy('nombre')->get();
            $cursos = $misCursos->merge($problema->cursos)->unique('id');
            if ($cursos->isEmpty()) {
                $cursos = Cursos::orderBy('nombre')->get();
            } else {
                $cursos = $cursos->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values();
            }
        }
        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.editar', compact('problema', 'categorias', 'lenguajes', 'cursos', 'id_curso'))->with('accion', "editar");
    }
    public function editar_config_llm(Request $request)
    {
        $problema = Problemas::find($request->id);
        if (!$problema) {
            return redirect()->route('problemas.index')->with('error', 'El problema solicitado no existe.');
        }
        return view('problemas.llm_config', compact('problema'));
    }

    public function configurar_llm(Request $request)
    {
        $validated = $request->validate(Problemas::$llm_config_rules);
        try {
            $problema = Problemas::findOrFail($request->id);
            if (isset($request->habilitar_llm)) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }
            $problema->limite_llm = $request->input('limite_llm');
            $problema->body_problema_resumido = $request->input('body_problema_resumido');
            $problema->restricciones = $request->input('restricciones');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route("problemas.configurar_llm", ["id" => $request->id])->with("error", $e->getMessage());
        }
        return redirect()->route("problemas.index")->with("success", "Se ha configurado la Large Language Model en el problema " . $problema->codigo . " correctamente");
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Problemas::createRules(isset($request->fecha_inicio), isset($request->fecha_termino), null,$request->sql));
        try {
            DB::beginTransaction();
            $problema = new Problemas;
            $this->set_datos_problemas($problema, $request);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        session(['wizard_creacion_problema_' . $problema->id => true]);
        $id_curso = $request->input('id_curso');
        $params = ["id" => $problema->id, "wizard" => 1];
        if ($id_curso) {
            $params['id_curso'] = $id_curso;
            session(['id_curso_problema_' . $problema->id => $id_curso]);
        }
        return redirect()->route('casos_pruebas.assign', $params)->with('success', 'Paso 1 completado: El problema "' . $problema->nombre . '" ha sido creado. Ahora agregue los casos de prueba.');
    }
    private static function set_datos_problemas(Problemas $problema, Request $request){
            $problema->nombre = $request->input('nombre');
            $problema->codigo = $request->input('codigo');
            if(isset($request->set_fecha_inicio)){
                $problema->fecha_inicio = Carbon::parse( $request->input('fecha_inicio'));
            }else{
                $problema->fecha_inicio = null;
            }
            if(isset($request->set_fecha_termino)){
                $problema->fecha_termino = Carbon::parse( $request->input('fecha_termino'));
            }else{
                $problema->fecha_termino = null;
            }
            if(isset($request->memoria_limite) && $request->memoria_limite>0){
                $problema->memoria_limite = $request->input('memoria_limite');
            }else{
                $problema->memoria_limite = 0;
            }
            $problema->tiempo_limite = $request->input('tiempo_limite');
            $problema->body_problema = self::almacenarImagenesBase64($request->input('body_problema'));
            $problema->body_problema_resumido = $request->input('body_problema_resumido');
            $problema->restricciones = $request->input('restricciones');
            if (isset($request->visible)) {
                $problema->visible = true;
            } else {
                $problema->visible = false;
            }

            if (isset($request->habilitar_llm) && $request->habilitar_llm) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }

            if ($request->filled('limite_llm')) {
                $problema->limite_llm = (int)$request->input('limite_llm');
            } else if ($request->has('limite_llm')) {
                $problema->limite_llm = 0;
            }

            if ($request->hasFile('archivos_adicionales')) {
                $file = $request->file('archivos_adicionales');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.]/', '', $file->getClientOriginalName());
                $file->storeAs('public/archivos_adicionales', $filename);
                $problema->archivo_adicional = $filename;
            }

            $problema->save();
            if(isset($request->sql) && $request->sql==1){
                $id_sql = LenguajesProgramaciones::where('nombre', 'LIKE', '%sql%')->pluck('id');
                $problema->lenguajes()->sync($id_sql);
            }else if(isset($request->lenguajes)){
                $problema->lenguajes()->sync($request->input('lenguajes'));
            }
            if(isset($request->categorias)){
                $problema->categorias()->sync($request->input('categorias'));
            }

            if (isset($request->cursos)) {
                $problema->cursos()->sync($request->input('cursos'));
            }

            if (!$problema->visible) {
                DB::table('disponible')
                    ->where('id_problema', $problema->id)
                    ->update(['visible' => false]);
            } else {
                $tieneAlgunoVisible = DB::table('disponible')
                    ->where('id_problema', $problema->id)
                    ->where('visible', true)
                    ->exists();
                if (!$tieneAlgunoVisible) {
                    DB::table('disponible')
                        ->where('id_problema', $problema->id)
                        ->update(['visible' => true]);
                }
            }
    }
    public function update(Request $request)
    {
        $id_curso = $request->input('id_curso');
        $validated = $request->validate(Problemas::updateRules(isset($request->set_fecha_inicio) || isset($request->fecha_inicio), isset($request->set_fecha_termino) || isset($request->fecha_termino), $request->id, $request->sql));
        try {
            DB::beginTransaction();
            $problema = Problemas::find($request->id);
            $this->set_datos_problemas($problema, $request);
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            if ($id_curso) {
                return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('error', $e->getMessage());
            }
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }

        if ($id_curso) {
            return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('success', 'El problema ' . $problema->nombre . ' ha sido modificado');
        }

        return redirect()->route('problemas.index')->with('success', 'El problema ' . $problema->nombre . ' ha sido modificado');
    }

    public function eliminar(Request $request)
    {
        $id_curso = $request->input('id_curso');
        try {
            DB::beginTransaction();
            $problema = Problemas::find($request->id);
            $problema->delete();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            if ($id_curso) {
                return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('error', $e->getMessage());
            }
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        if ($id_curso) {
            return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('success', 'El Problema ' . $problema->nombre . ' ha sido eliminado');
        }
        return redirect()->route('problemas.index')->with('success', 'El Problema ' . $problema->nombre . ' ha sido eliminado');
    }

    public function toggle_visibilidad(Request $request)
    {
        $problema = Problemas::findOrFail($request->id);
        $id_curso = $request->input('id_curso');

        if ($id_curso) {
            $curso = Cursos::findOrFail($id_curso);
            $pivotRow = DB::table('disponible')
                ->where('id_problema', $problema->id)
                ->where('id_curso', $curso->id)
                ->first();

            if (!$pivotRow) {
                return redirect()->back()->with('error', 'El problema no está asignado a este curso.');
            }

            $estadoActualCurso = (bool)$problema->visible && (bool)($pivotRow->visible ?? true);
            $nuevoEstado = !$estadoActualCurso;

            // Si el problema estaba oculto globalmente y se activa en un curso específico,
            // habilitamos el problema globalmente y mantenemos ocultos los demás cursos.
            if ($nuevoEstado && !$problema->visible) {
                $problema->visible = true;
                $problema->save();
                DB::table('disponible')
                    ->where('id_problema', $problema->id)
                    ->where('id_curso', '!=', $curso->id)
                    ->update(['visible' => false]);
            }

            DB::table('disponible')
                ->where('id_problema', $problema->id)
                ->where('id_curso', $curso->id)
                ->update(['visible' => $nuevoEstado]);

            $accionTxt = $nuevoEstado ? 'visible' : 'oculto';
            return redirect()->back()->with('success', "El problema \"{$problema->nombre}\" ahora está {$accionTxt} en el curso {$curso->codigo}.");
        }

        // Cambio global en todos los cursos asignados
        $nuevoEstadoGlobal = !$problema->visible;
        $problema->visible = $nuevoEstadoGlobal;
        $problema->save();

        DB::table('disponible')
            ->where('id_problema', $problema->id)
            ->update(['visible' => $nuevoEstadoGlobal]);

        $accionTxt = $nuevoEstadoGlobal ? 'visible en todos sus cursos' : 'oculto en todos sus cursos';
        return redirect()->back()->with('success', "El problema \"{$problema->nombre}\" ahora está {$accionTxt}.");
    }

    public function update_editorial(Request $request)
    {
        $id_curso = $request->input('id_curso');
        try {
            $problema = Problemas::find($request->id);
            if (!$problema) {
                return redirect()->route('problemas.index')->with('error', 'El problema solicitado no existe.');
            }
            $problema->body_editorial = $request->input('body_editorial');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->back()->withInput($request->input())->with('error', $e->getMessage());
        }
        if ($id_curso) {
            return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('success', 'Las pistas para el problema "' . $problema->nombre . '" han sido modificadas');
        }
        return redirect()->route('problemas.index')->with('success', 'Las pistas para el problema "' . $problema->nombre . '" han sido modificadas');
    }

    public function editar_editorial(Request $request)
    {
        $id_curso = $request->input('id_curso');
        try {
            $problema = Problemas::find($request->id);
            if (!$problema) {
                if ($id_curso) {
                    return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('error', 'El problema solicitado no existe.');
                }
                return redirect()->route('problemas.index')->with('error', 'El problema solicitado no existe.');
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            if ($id_curso) {
                return redirect()->route('problemas.index', ['id_curso' => $id_curso])->with('error', $e->getMessage());
            }
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return view('problemas.editorial', compact('problema', 'id_curso'));
    }

    public function listado_problemas(Request $request)
    {
        try {
            $curso = auth()->user()->cursos()->find($request->id);
            //verifica si el usuario está registrado al curso que se quiere acceder 
            if (!isset($curso)) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al curso al que estás tratando de acceder');
            }
            //tabla intermedia entre usuario y curso
            $curso_usuario_pivot = $curso->pivot;
            $fecha_ahora = Carbon::now();
            $problemas = $curso->problemas()
                ->where('problemas.visible', '=', true)
                ->wherePivot('visible', true)
                ->select('problemas.id', 'problemas.codigo', 'problemas.nombre', 'problemas.dificultad', 'problemas.visible', 'problemas.created_at', 'problemas.fecha_inicio', 'problemas.fecha_termino')
                ->with('categorias:id,nombre')
                ->withSum('casos_de_prueba', 'puntos')
                ->get()
                ->unique('id')
                ->values();

            // Batch lookup de problemas resueltos y si el último resuelto fue con variación de formato (evita N+1 por problema)
            $problemaIds = $problemas->pluck('id')->all();
            $resueltosMap = [];
            if (!empty($problemaIds)) {
                $enviosResueltos = DB::table('envio_solucion_problemas as e')
                    ->join('resolver as r', 'r.id', '=', 'e.id_resolver')
                    ->leftJoin('evaluacion_solucions as ev', function (JoinClause $join) {
                        $join->on('ev.id_envio', '=', 'e.id')
                            ->where('ev.resultado', '=', 'Aceptado (Variación de Formato)');
                    })
                    ->where('e.id_cursa', '=', $curso_usuario_pivot->id)
                    ->whereNull('e.id_certamen')
                    ->where('e.solucionado', '=', true)
                    ->whereIn('r.id_problema', $problemaIds)
                    ->orderByDesc('e.created_at')
                    ->select(
                        'r.id_problema',
                        DB::raw('MAX(CASE WHEN ev.id IS NOT NULL THEN 1 ELSE 0 END) as variacion_formato')
                    )
                    ->groupBy('e.id', 'r.id_problema', 'e.created_at')
                    ->get();

                foreach ($enviosResueltos as $er) {
                    if (!isset($resueltosMap[$er->id_problema])) {
                        $resueltosMap[$er->id_problema] = ((int) $er->variacion_formato === 1);
                    }
                }
            }

            $problemas = $problemas->map(function ($problema) use ($resueltosMap) {
                $problema->puntaje_total = (int) ($problema->casos_de_prueba_sum_puntos ?? 0);
                $problema->categorias = $problema->categorias->pluck('nombre')->implode(', ');
                $problema->creado = Carbon::parse($problema->created_at)->locale('es_ES')->isoFormat('lll');
                $problema->resuelto = array_key_exists($problema->id, $resueltosMap);
                $problema->variacion_formato = $resueltosMap[$problema->id] ?? false;
                return $problema;
            });

            // Cargar ÚNICAMENTE las evaluaciones pertenecientes a este curso específico
            $resultados_certamenes = DB::table('resolucion_certamenes')
                ->leftJoin('envio_solucion_problemas', 'envio_solucion_problemas.id_certamen', '=', 'resolucion_certamenes.id')
                ->where('id_usuario', '=', auth()->user()->id)
                ->select(
                    'resolucion_certamenes.id_certamen',
                    DB::raw('max(finalizado) as estado_finalizado'),
                    DB::raw('min(resolucion_certamenes.created_at) as fecha_inicio_resolucion'),
                    DB::raw('max(fecha_finalizado) as fecha_finalizado'),
                    DB::raw('max(puntaje_obtenido) as puntaje_maximo'),
                    DB::raw('max(problemas_resueltos) as maximo_resuelto')
                )
                ->groupBy('resolucion_certamenes.id_certamen');

            $evaluaciones = Certamenes::leftJoinSub($resultados_certamenes, 'resultados_certamenes', function (JoinClause $join) {
                $join->on('resultados_certamenes.id_certamen', '=', 'certamenes.id');
            })->where('id_curso', '=', $curso->id)->orderBy('fecha_inicio', 'desc')->get()->map(function ($item) {
                $now = Carbon::now();
                $inicio = Carbon::parse($item->fecha_inicio);
                $termino = Carbon::parse($item->fecha_termino);

                $item->disponible = ($now->gte($inicio) && $now->lte($termino));
                $item->fecha_inicio_formatted = $inicio->locale('es_ES')->isoFormat('lll');
                $item->fecha_termino_formatted = $termino->locale('es_ES')->isoFormat('lll');
                if (isset($item->fecha_finalizado)) {
                    $inicioRef = isset($item->fecha_inicio_resolucion) ? Carbon::parse($item->fecha_inicio_resolucion) : $inicio;
                    $item->tiempo_desarrollo = Carbon::parse($item->fecha_finalizado)->diffInSeconds($inicioRef);
                }
                return $item;
            });

        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.index', compact('problemas', 'evaluaciones', 'curso'));
    }

    public function ver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            if (!$problema) {
                return redirect()->route('cursos.listado')->with('error', 'El problema que estás tratando de acceder no existe.');
            }
            $id_curso = $request->id_curso;
            if (!$id_curso) {
                $cursos_usuario_ids = auth()->user()->cursos()->pluck('cursos.id')->toArray();
                $id_curso = $problema->cursos()->whereIn('cursos.id', $cursos_usuario_ids)->value('cursos.id')
                    ?: $problema->cursos()->value('cursos.id');
            }
            if(!$id_curso || !Cursos::where('cursos.id','=',$id_curso)->exists()){
                return redirect()->route('cursos.listado')->with('error', 'El curso que estás tratando de acceder no existe.');
            }
            
            $curso_usuario = auth()->user()->cursos()->find($id_curso);
            if (!$curso_usuario && auth()->user()->hasRole('administrador')) {
                \App\Models\Cursa::firstOrCreate([
                    'id_usuario' => auth()->id(),
                    'id_curso' => $id_curso
                ]);
                $curso_usuario = auth()->user()->cursos()->find($id_curso);
            }
            if (!$curso_usuario) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al curso al que estás tratando de acceder.');
            }

            $disponibleEnCurso = $problema->cursos()->where('cursos.id', '=', $id_curso)->wherePivot('visible', true)->exists();
            if ((!$disponibleEnCurso || $problema->visible == false) && !auth()->user()->hasRole('administrador')) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }

            $problema->disponible = true;
            $id_cursa = $curso_usuario && isset($curso_usuario->pivot) ? $curso_usuario->pivot->id : 0;
            $ultimo_solucionado = $problema->envios()->where('id_cursa', '=', $id_cursa)->whereNull('id_certamen')->where('solucionado', '=', true)->orderBy('created_at', 'DESC')->first();
            $problema->estado = isset($ultimo_solucionado);
            $problema->variacion_formato = $ultimo_solucionado
                ? $ultimo_solucionado->evaluaciones()->where('resultado', '=', 'Aceptado (Variación de Formato)')->exists()
                : false;
            
            $now = Carbon::now();
            if (isset($problema->fecha_inicio)) {
                $fecha_inicio = Carbon::parse($problema->fecha_inicio);
                if ($now->lt($fecha_inicio)) {
                    $problema->disponible = false;
                }
                $problema->fecha_inicio = $fecha_inicio->locale('es_ES')->isoFormat('lll');
            } else {
                $problema->fecha_inicio = "No Definido";
            }
            
            if (isset($problema->fecha_termino)) {
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino)) {
                    $problema->disponible = false;
                }
                $problema->fecha_termino = $fecha_termino->locale('es_ES')->isoFormat('lll');
            } else {
                $problema->fecha_termino = "No Definido";
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_problema', compact('problema', 'id_curso'));
    }

    public function ver_editorial(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            if (!$problema) {
                return redirect()->route('cursos.listado')->with('error', 'El problema no existe.');
            }
            $cursos_usuario = auth()->user()->cursos()->get()->pluck('id')->toArray();
            $id_curso = $request->id_curso;
            if (!$id_curso) {
                $id_curso = $problema->cursos()->whereIn('cursos.id', $cursos_usuario)->value('cursos.id')
                    ?: $problema->cursos()->value('cursos.id');
            }
            $disponibleEnCurso = $problema->cursos()->whereIn('cursos.id', $cursos_usuario)->wherePivot('visible', true)->exists();
            if ((!$disponibleEnCurso || $problema->visible == false) && !auth()->user()->hasRole('administrador')) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }
            if (isset($problema->fecha_termino)) {
                $now = Carbon::now();
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino) && !auth()->user()->hasRole('administrador')) {
                    return redirect()->route('cursos.listado')->with('error', 'El problema ' . $problema->nombre . ' no está disponible');
                }
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_editorial', compact('problema', 'id_curso'));
    }

    public function resolver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            if (!$problema) {
                return redirect()->route('cursos.listado')->with('error', 'El problema no existe.');
            }

            $res_certamen = null;
            if (isset($request->token_certamen)) {
                $res_certamen = ResolucionCertamenes::with('certamen')->where('token', '=', $request->token_certamen)->first();
                if ($res_certamen && !$res_certamen->certamen) {
                    $res_certamen = null;
                }
                if ($res_certamen && $res_certamen->finalizado) {
                    return redirect()->route('certamenes.ver', ['id_certamen' => $res_certamen->id_certamen])
                        ->with('error', 'No puedes acceder porque esta evaluación ya ha sido finalizada.');
                }
            }

            $id_curso = $request->id_curso ?: ($res_certamen && $res_certamen->certamen ? $res_certamen->certamen->id_curso : $problema->cursos()->value('cursos.id'));
            $curso_usuario = auth()->user()->cursos()->find($id_curso);
            if (!$curso_usuario && auth()->user()->hasRole('administrador') && $id_curso) {
                \App\Models\Cursa::firstOrCreate([
                    'id_usuario' => auth()->id(),
                    'id_curso'   => $id_curso
                ]);
                $curso_usuario = auth()->user()->cursos()->find($id_curso);
            }
            if (!$curso_usuario) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso a este curso.');
            }

            if (!isset($res_certamen) && !auth()->user()->hasRole('administrador')) {
                $disponibleEnCurso = $problema->cursos()->where('cursos.id', '=', $id_curso)->wherePivot('visible', true)->exists();
                if (!$disponibleEnCurso || !$problema->visible) {
                    return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
                }
                $now = Carbon::now();
                if (isset($problema->fecha_inicio) && $now->lt(Carbon::parse($problema->fecha_inicio))) {
                    return redirect()->route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso])->with('error', 'El problema aún no está disponible para resolverse.');
                }
                if (isset($problema->fecha_termino) && $now->gt(Carbon::parse($problema->fecha_termino))) {
                    return redirect()->route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso])->with('error', 'El plazo para resolver este problema ha finalizado.');
                }
            }

            $lenguajes = $problema->lenguajes()->get();
            if ($lenguajes->isEmpty()) {
                return redirect()->route('problemas.ver', ['codigo' => $problema->codigo, 'id_curso' => $id_curso])
                    ->with('error', 'Este problema no tiene lenguajes de programación configurados.');
            }
            $jueces = JuecesVirtuales::all();

            if ($res_certamen) {
                $last_envio = $problema->envios()->where('id_certamen', '=', $res_certamen->id)->orderBy('created_at', 'DESC')->first();
            } else {
                $last_envio = $problema->envios()->where('id_cursa', '=', $curso_usuario->pivot->id)->whereNull('id_certamen')->orderBy('created_at', 'DESC')->first();
            }

            if (isset($last_envio->termino) || !isset($last_envio)) {
                DB::beginTransaction();
                $envio = new EnvioSolucionProblema;
                $envio->token = Str::random(40);
                $envio->ip_origen = $request->ip();
                $envio->inicio = Carbon::now();
                $prevLangId = isset($last_envio->termino) ? ($last_envio->lenguaje->id ?? optional($last_envio->ProblemaLenguaje)->id_lenguaje) : null;
                $matchedLang = $prevLangId ? $lenguajes->find($prevLangId) : null;
                if ($matchedLang && isset($matchedLang->pivot)) {
                    $envio->ProblemaLenguaje()->associate($matchedLang->pivot);
                } else {
                    $envio->ProblemaLenguaje()->associate($lenguajes[0]->pivot);
                }
                $envio->CursoUsuario()->associate($curso_usuario->pivot);
                if (isset($res_certamen)) {
                    $envio->id_certamen = $res_certamen->id;
                } else {
                    DB::table('disponible')->where('id_curso', '=', $id_curso)->where('id_problema', '=', $problema->id)->increment('cantidad_intentos');
                }
                if (isset($last_envio->termino)) {
                    if (!empty($last_envio->codigo)) {
                        $envio->codigo = $last_envio->codigo;
                    }
                    $envio->inicio = $last_envio->inicio;
                }
                $envio->save();
                $last_envio = $envio;
                DB::commit();
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        $response = response()->view('plataforma.problemas.resolver_problema', compact('problema', 'lenguajes', 'jueces', 'last_envio', 'res_certamen', 'id_curso'));
        if (isset($res_certamen)) {
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                     ->header('Pragma', 'no-cache')
                     ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }
        return $response;
    }

    public function pdf_enunciado(Request $request){
        $problema = Problemas::with(['categorias', 'lenguajes'])->find($request->id_problema);
        if (!$problema) {
            return redirect()->back()->with('error', 'El problema solicitado no fue encontrado.');
        }
        $problema->puntaje_total = $problema->casos_de_prueba()->sum('puntos');

        $pdf = PDF::loadView('plataforma.problemas.pdf_enunciado', compact('problema'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        $filename = ($problema->codigo ?: 'problema') . ' - enunciado.pdf';
        return $pdf->download($filename);
    }

    public function guardar_codigo(Request $request){
        try{
            DB::beginTransaction();
            $last_envio = null;

            if ($request->filled('id_envio')) {
                $last_envio = EnvioSolucionProblema::find($request->id_envio);
            }

            if (!$last_envio && $request->filled('id_cursa')) {
                $last_envio = EnvioSolucionProblema::where('id_cursa', '=', $request->id_cursa)
                    ->when($request->filled('id_resolver'), function($q) use ($request) {
                        $q->where('id_resolver', '=', $request->id_resolver);
                    })
                    ->orderBy('created_at', 'DESC')->first();
            }

            if ($last_envio) {
                $last_envio->codigo = $request->codigo_save ?? '';

                if($request->filled('lenguaje_save') && $request->filled('id_problema')){
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
            \Log::error("Error en guardar_codigo: " . $e->getMessage());
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['saved' => false, 'message' => $e->getMessage()], 500);
            }
            return redirect()->route('problemas.resolver', ['codigo'=>$request->codigo_problema, 'id_curso'=>$request->id_curso])->with('error', $e->getMessage());
        }
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['saved' => true]);
        }
        return redirect()->route('problemas.ver', ['codigo'=>$request->codigo_problema, 'id_curso'=>$request->id_curso])->with('success', 'El código desarrollado ha sido almacenado');
    }

    public function agregar_problemas_curso(Request $request, $id_curso)
    {
        $curso = Cursos::findOrFail($id_curso);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id_curso)->exists()) {
            abort(403);
        }

        $request->validate([
            'problemas' => 'required|array|min:1',
            'problemas.*' => 'exists:problemas,id',
        ]);

        $syncData = [];
        foreach ($request->problemas as $pId) {
            $syncData[$pId] = [
                'visible' => true,
                'cantidad_resueltos' => 0,
                'cantidad_intentos' => 0,
                'tiempo_total' => 0,
                'cant_retroalimentacion_solicitada' => 0,
            ];
        }

        $curso->problemas()->syncWithoutDetaching($syncData);

        return redirect()->route('problemas.index', ['id_curso' => $id_curso])
            ->with('success', 'Se han asignado ' . count($request->problemas) . ' ejercicio(s) al curso "' . $curso->nombre . '" correctamente.');
    }

    public function quitar_problema_curso(Request $request, $id_curso)
    {
        $curso = Cursos::findOrFail($id_curso);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id_curso)->exists()) {
            abort(403);
        }

        $ids = $request->input('problemas_ids');
        if (empty($ids) && $request->filled('id_problema')) {
            $ids = [$request->input('id_problema')];
        }

        if (empty($ids)) {
            return redirect()->route('problemas.index', ['id_curso' => $id_curso])
                ->with('error', 'No se especificó ningún ejercicio para desvincular.');
        }

        $curso->problemas()->detach($ids);

        return redirect()->route('problemas.index', ['id_curso' => $id_curso])
            ->with('success', 'Se ha(n) desvinculado ' . count($ids) . ' ejercicio(s) del curso correctamente.');
    }

    public function upload_imagen(Request $request)
    {
        $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp,svg', 'max:10240'],
        ]);

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension() ?: 'png';
        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $filename = time() . '_' . ($safeName ? substr($safeName, 0, 30) : 'imagen') . '.' . $extension;

        $path = $file->storeAs('problemas/imagenes', $filename, 'public');

        return response()->json([
            'url' => asset('storage/' . $path),
            'relative_url' => '/storage/' . $path,
            'filename' => $filename,
        ]);
    }

    public static function almacenarImagenesBase64($contenido)
    {
        if (empty($contenido) || !str_contains($contenido, 'data:image/')) {
            return $contenido;
        }

        $contenido = preg_replace('/!\[(.*?)\]\s*\r?\n\s*\(([^)]+)\)/', '![$1]($2)', $contenido);

        return preg_replace_callback(
            '/data:image\/([a-zA-Z0-9\+\-]+);base64,([A-Za-z0-9\/+=\s]+)/',
            function ($matches) {
                $rawExt = strtolower($matches[1]);
                $map = [
                    'jpeg' => 'jpg',
                    'jpg' => 'jpg',
                    'png' => 'png',
                    'gif' => 'gif',
                    'webp' => 'webp',
                    'svg+xml' => 'svg',
                    'svg' => 'svg'
                ];
                $extension = $map[$rawExt] ?? 'png';

                $cleanBase64 = preg_replace('/\s+/', '', $matches[2]);
                $decoded = base64_decode($cleanBase64, true);

                if ($decoded === false) {
                    return $matches[0];
                }

                $filename = time() . '_' . Str::random(12) . '.' . $extension;
                $relativePath = 'problemas/imagenes/' . $filename;

                Storage::disk('public')->put($relativePath, $decoded);

                return '/storage/' . $relativePath;
            },
            $contenido
        );
    }
}
