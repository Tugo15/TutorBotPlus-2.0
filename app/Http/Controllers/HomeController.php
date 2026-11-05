<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Query\JoinClause;
use App\Models\User;
use App\Models\Cursos;
use App\Models\Problemas;
use App\Models\Casos_Pruebas;
use App\Models\Certamenes;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\SolicitudRaLlm;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Show the application executive dashboard on /inicio scoped to the user's courses.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $esAdmin = $user->hasRole('administrador');

        // Obtener los cursos a los que pertenece el usuario (si es admin y no está inscrito en cursa, ve todos los cursos)
        $misCursos = $user->cursos()->get();
        if ($esAdmin && $misCursos->isEmpty()) {
            $misCursos = Cursos::all();
        }

        $id_curso_seleccionado = $request->input('id_curso');
        $curso_activo = null;

        if ($id_curso_seleccionado && $misCursos->contains('id', $id_curso_seleccionado)) {
            $curso_activo = $misCursos->firstWhere('id', $id_curso_seleccionado);
            $cursos_ids = [$curso_activo->id];
        } else {
            $id_curso_seleccionado = null;
            $cursos_ids = $misCursos->pluck('id')->toArray();
        }

        $lifetimeMinutes = (int) config('session.lifetime', 120);
        $cutoffTimestamp = now()->subMinutes($lifetimeMinutes)->timestamp;
        $currentUserId = $user->id;

        // 1. Usuarios Conectados Ahora (pertenecientes a mis cursos)
        $candidatosQuery = User::with(['roles', 'cursos' => function ($q) use ($cursos_ids) {
            $q->whereIn('cursos.id', $cursos_ids);
        }])->whereNotNull('current_session_id');

        if (!$esAdmin || !empty($cursos_ids)) {
            $candidatosQuery->where(function ($q) use ($cursos_ids, $currentUserId) {
                $q->whereHas('cursos', function ($sq) use ($cursos_ids) {
                    $sq->whereIn('cursos.id', $cursos_ids);
                })->orWhere('users.id', $currentUserId);
            });
        }

        $candidatos = $candidatosQuery->get();

        $usuarios_conectados = $candidatos->filter(function ($u) use ($cutoffTimestamp, $currentUserId) {
            if ($u->id === $currentUserId) {
                return true;
            }
            $lastSeen = Cache::get('user_last_seen_' . $u->id);
            if ($lastSeen && $lastSeen >= $cutoffTimestamp) {
                return true;
            }
            $sessionFile = storage_path('framework/sessions/' . $u->current_session_id);
            if (is_file($sessionFile)) {
                $mtime = @filemtime($sessionFile);
                if ($mtime && $mtime >= $cutoffTimestamp) {
                    return true;
                }
            }
            return Cache::has('user_active_session_' . $u->id);
        })->map(function ($u) use ($currentUserId) {
            $lastSeen = Cache::get('user_last_seen_' . $u->id);
            $lastIp = Cache::get('user_last_ip_' . $u->id) ?: $u->last_login_ip;
            if ($u->id === $currentUserId && (!$lastIp || $lastIp === '127.0.0.1')) {
                $lastIp = request()->ip() ?: $lastIp;
            }
            if (!$lastSeen && $u->current_session_id) {
                $sessionFile = storage_path('framework/sessions/' . $u->current_session_id);
                if (is_file($sessionFile)) {
                    $lastSeen = @filemtime($sessionFile);
                }
            }
            if ($u->id === $currentUserId) {
                $u->ultima_actividad = 'Activo ahora (Tú)';
                $u->ultimo_ts = now()->timestamp;
            } elseif ($lastSeen) {
                $u->ultima_actividad = Carbon::createFromTimestamp($lastSeen)->locale('es_ES')->diffForHumans();
                $u->ultimo_ts = $lastSeen;
            } else {
                $u->ultima_actividad = 'Sesión activa';
                $u->ultimo_ts = 0;
            }
            $u->roles_str = $u->getRoleNames()->map(fn($r) => ucfirst($r))->implode(', ') ?: 'Estudiante';
            $u->ip = $lastIp ?: '127.0.0.1';
            return $u;
        })->sortByDesc('ultimo_ts')->values();

        $cantidad_conectados = $usuarios_conectados->count();
        $total_usuarios = User::whereHas('cursos', function ($q) use ($cursos_ids) {
            $q->whereIn('cursos.id', $cursos_ids);
        })->count();

        // 2. Métricas de Envíos en Mis Cursos: Correctos vs Incorrectos
        $enviosBaseQuery = DB::table('envio_solucion_problemas')
            ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->whereIn('cursa.id_curso', $cursos_ids)
            ->whereNotNull('envio_solucion_problemas.termino');

        $total_envios = (clone $enviosBaseQuery)->count();
        $envios_correctos = (clone $enviosBaseQuery)->where('envio_solucion_problemas.solucionado', true)->count();
        $envios_incorrectos = max(0, $total_envios - $envios_correctos);

        $envios_variacion_formato = (clone $enviosBaseQuery)
            ->join('evaluacion_solucions', 'evaluacion_solucions.id_envio', '=', 'envio_solucion_problemas.id')
            ->where('envio_solucion_problemas.solucionado', true)
            ->where('evaluacion_solucions.resultado', 'Aceptado (Variación de Formato)')
            ->distinct('envio_solucion_problemas.id')
            ->count('envio_solucion_problemas.id');

        $envios_aceptados_exactos = max(0, $envios_correctos - $envios_variacion_formato);
        $tasa_exito_global = $total_envios > 0 ? round(($envios_correctos / $total_envios) * 100, 1) : 0;

        // 3. Resumen de Recursos en Mis Cursos
        $total_cursos = count($cursos_ids);
        $problemasIdsEnCursos = DB::table('disponible')
            ->whereIn('id_curso', $cursos_ids)
            ->distinct()
            ->pluck('id_problema')
            ->toArray();

        $total_problemas = count($problemasIdsEnCursos);
        $problemas_visibles = DB::table('problemas')
            ->join('disponible', 'disponible.id_problema', '=', 'problemas.id')
            ->whereIn('disponible.id_curso', $cursos_ids)
            ->where('problemas.visible', true)
            ->where('disponible.visible', true)
            ->distinct('problemas.id')
            ->count('problemas.id');

        $total_casos_prueba = Casos_Pruebas::whereIn('id_problema', $problemasIdsEnCursos)->count();
        $total_certamenes = Certamenes::whereIn('id_curso', $cursos_ids)->count();

        $total_retroalimentaciones = DB::table('solicitud_ra_llms')
            ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'solicitud_ra_llms.id_envio')
            ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->whereIn('cursa.id_curso', $cursos_ids)
            ->count();

        // 4. Resumen funcional por cada uno de Mis Cursos
        $resumen_cursos = DB::table('cursos')
            ->leftJoin('disponible', 'disponible.id_curso', '=', 'cursos.id')
            ->whereIn('cursos.id', $cursos_ids)
            ->select(
                'cursos.id',
                'cursos.codigo',
                'cursos.nombre',
                DB::raw('COUNT(DISTINCT disponible.id_problema) as cantidad_problemas'),
                DB::raw('COALESCE(SUM(disponible.cantidad_intentos), 0) as cantidad_intentos'),
                DB::raw('COALESCE(SUM(disponible.cantidad_resueltos), 0) as cantidad_resueltos')
            )
            ->groupBy('cursos.id', 'cursos.codigo', 'cursos.nombre')
            ->orderBy('cursos.nombre')
            ->get();

        // 5. Últimos Envíos en Mis Cursos
        $ultima_evaluacion_sub = DB::table('evaluacion_solucions')
            ->select(
                'id_envio',
                DB::raw("MAX(CASE WHEN resultado = 'Aceptado (Variación de Formato)' THEN 1 ELSE 0 END) as variacion_formato"),
                DB::raw("MAX(CASE WHEN estado IN ('Rechazado', 'En Proceso', 'Error') THEN resultado ELSE NULL END) as resultado"),
                DB::raw("MAX(CASE WHEN estado IN ('Rechazado', 'En Proceso', 'Error') THEN estado ELSE NULL END) as estado")
            )
            ->groupBy('id_envio');

        $ultimos_envios = DB::table('envio_solucion_problemas')
            ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
            ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->join('cursos', 'cursos.id', '=', 'cursa.id_curso')
            ->join('problemas', 'problemas.id', '=', 'resolver.id_problema')
            ->join('users', 'users.id', '=', 'cursa.id_usuario')
            ->join('lenguajes_programaciones', 'lenguajes_programaciones.id', '=', 'resolver.id_lenguaje')
            ->leftJoinSub($ultima_evaluacion_sub, 'ultima_evaluacion', function (JoinClause $join) {
                $join->on('envio_solucion_problemas.id', '=', 'ultima_evaluacion.id_envio');
            })
            ->whereIn('cursa.id_curso', $cursos_ids)
            ->whereNotNull('envio_solucion_problemas.termino')
            ->select(
                'envio_solucion_problemas.id',
                'envio_solucion_problemas.token',
                'envio_solucion_problemas.solucionado',
                'envio_solucion_problemas.ip_origen',
                'envio_solucion_problemas.puntaje',
                'envio_solucion_problemas.cant_casos_resuelto',
                'envio_solucion_problemas.created_at',
                'users.firstname',
                'users.lastname',
                'users.rut',
                'problemas.id as id_problema',
                'problemas.nombre as nombre_problema',
                'problemas.codigo as codigo_problema',
                'cursos.id as id_curso',
                'cursos.codigo as codigo_curso',
                'cursos.nombre as nombre_curso',
                'lenguajes_programaciones.nombre as nombre_lenguaje',
                'ultima_evaluacion.resultado',
                'ultima_evaluacion.estado',
                'ultima_evaluacion.variacion_formato'
            )
            ->orderByDesc('envio_solucion_problemas.created_at')
            ->limit(10)
            ->get();

        return view('pages.inicio', compact(
            'misCursos',
            'curso_activo',
            'id_curso_seleccionado',
            'usuarios_conectados',
            'cantidad_conectados',
            'total_usuarios',
            'total_envios',
            'envios_correctos',
            'envios_incorrectos',
            'tasa_exito_global',
            'total_problemas',
            'problemas_visibles',
            'resumen_cursos',
            'ultimos_envios'
        ));
    }
}
