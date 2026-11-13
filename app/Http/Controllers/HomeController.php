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
        $misCursos = $user->cursos()->orderBy('nombre')->get();
        if ($esAdmin && $misCursos->isEmpty()) {
            $misCursos = Cursos::orderBy('nombre')->get();
        }
        $misCursos = $misCursos->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values();

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
        $candidatosQuery = User::select([
            'users.id',
            'users.firstname',
            'users.lastname',
            'users.rut',
            'users.current_session_id',
            'users.last_login_ip',
        ])->with([
            'roles:id,name',
            'cursos' => function ($q) use ($cursos_ids) {
                $q->select('cursos.id')->whereIn('cursos.id', $cursos_ids);
            },
        ])->whereNotNull('current_session_id');

        if (!$esAdmin || !empty($cursos_ids)) {
            $candidatosQuery->where(function ($q) use ($cursos_ids, $currentUserId) {
                $q->whereHas('cursos', function ($sq) use ($cursos_ids) {
                    $sq->whereIn('cursos.id', $cursos_ids);
                })->orWhere('users.id', $currentUserId);
            });
        }

        $candidatos = $candidatosQuery->get();
        $reqIp = request()->ip();
        $nowTs = now()->timestamp;

        $usuarios_conectados = $candidatos->map(function ($u) use ($cutoffTimestamp, $currentUserId, $reqIp, $nowTs) {
            $isSelf = ($u->id === $currentUserId);
            $lastSeen = Cache::get('user_last_seen_' . $u->id);
            $isActive = $isSelf || ($lastSeen && $lastSeen >= $cutoffTimestamp);

            if (!$lastSeen && $u->current_session_id) {
                $sessionFile = storage_path('framework/sessions/' . $u->current_session_id);
                if (is_file($sessionFile)) {
                    $mtime = @filemtime($sessionFile);
                    if ($mtime) {
                        $lastSeen = $mtime;
                        if ($mtime >= $cutoffTimestamp) {
                            $isActive = true;
                        }
                    }
                }
            }

            if (!$isActive && !Cache::has('user_active_session_' . $u->id)) {
                return null;
            }

            $lastIp = Cache::get('user_last_ip_' . $u->id) ?: $u->last_login_ip;
            if ($isSelf && (!$lastIp || $lastIp === '127.0.0.1')) {
                $lastIp = $reqIp ?: $lastIp;
            }

            if ($isSelf) {
                $u->ultima_actividad = 'Activo ahora (Tú)';
                $u->ultimo_ts = $nowTs;
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
        })->filter()->sortByDesc('ultimo_ts')->values();

        $cantidad_conectados = $usuarios_conectados->count();
        $total_usuarios = User::whereHas('cursos', function ($q) use ($cursos_ids) {
            $q->whereIn('cursos.id', $cursos_ids);
        })->count();

        // 2. Métricas de Envíos en Mis Cursos: Correctos vs Incorrectos
        $envioStatsGlobal = DB::table('envio_solucion_problemas')
            ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->whereIn('cursa.id_curso', $cursos_ids)
            ->whereNotNull('envio_solucion_problemas.termino')
            ->selectRaw('COUNT(*) as total_envios, COALESCE(SUM(CASE WHEN envio_solucion_problemas.solucionado = 1 THEN 1 ELSE 0 END), 0) as envios_correctos')
            ->first();

        $total_envios = (int) ($envioStatsGlobal->total_envios ?? 0);
        $envios_correctos = (int) ($envioStatsGlobal->envios_correctos ?? 0);
        $envios_incorrectos = max(0, $total_envios - $envios_correctos);
        $tasa_exito_global = $total_envios > 0 ? round(($envios_correctos / $total_envios) * 100, 1) : 0;

        // 3. Resumen de Recursos en Mis Cursos
        $total_problemas = DB::table('disponible')
            ->whereIn('id_curso', $cursos_ids)
            ->distinct('id_problema')
            ->count('id_problema');

        $problemas_visibles = DB::table('problemas')
            ->join('disponible', 'disponible.id_problema', '=', 'problemas.id')
            ->whereIn('disponible.id_curso', $cursos_ids)
            ->where('problemas.visible', true)
            ->where('disponible.visible', true)
            ->distinct('problemas.id')
            ->count('problemas.id');

        // 4. Resumen funcional por cada uno de Mis Cursos
        $problemasPorCursoSub = DB::table('disponible')
            ->whereIn('id_curso', $cursos_ids)
            ->select('id_curso', DB::raw('COUNT(DISTINCT id_problema) as cantidad_problemas'))
            ->groupBy('id_curso');

        $enviosPorCursoSub = DB::table('envio_solucion_problemas')
            ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->whereIn('cursa.id_curso', $cursos_ids)
            ->whereNotNull('envio_solucion_problemas.termino')
            ->select(
                'cursa.id_curso',
                DB::raw('COUNT(envio_solucion_problemas.id) as cantidad_intentos'),
                DB::raw('COALESCE(SUM(CASE WHEN envio_solucion_problemas.solucionado = 1 THEN 1 ELSE 0 END), 0) as cantidad_resueltos')
            )
            ->groupBy('cursa.id_curso');

        $resumen_cursos = DB::table('cursos')
            ->leftJoinSub($problemasPorCursoSub, 'pc', function ($join) {
                $join->on('pc.id_curso', '=', 'cursos.id');
            })
            ->leftJoinSub($enviosPorCursoSub, 'ec', function ($join) {
                $join->on('ec.id_curso', '=', 'cursos.id');
            })
            ->whereIn('cursos.id', $cursos_ids)
            ->select(
                'cursos.id',
                'cursos.codigo',
                'cursos.nombre',
                DB::raw('COALESCE(pc.cantidad_problemas, 0) as cantidad_problemas'),
                DB::raw('COALESCE(ec.cantidad_intentos, 0) as cantidad_intentos'),
                DB::raw('COALESCE(ec.cantidad_resueltos, 0) as cantidad_resueltos')
            )
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
