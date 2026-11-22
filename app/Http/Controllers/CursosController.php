<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CursosController extends Controller
{
    public function index(Request $request)
    {   
        if(auth()->user()->hasRole('administrador')){
            $cursos = Cursos::withCount(['users', 'problemas'])->get();
        }else{
            $cursos = auth()->user()->cursos()->withCount(['users', 'problemas'])->get();
        }
        $cursos = $cursos->map(function($curso){
            $curso->fecha = carbon::parse($curso->created_at)->locale('es_ES')->isoFormat('lll');
            return $curso;
        });
        return view('cursos.index', compact('cursos'));
    }

    public function crear(){
        return view('cursos.crear');
    }

    public function editar(Request $request){
        $id = $request->input('id') ?? $request->id;
        $curso = $id ? Cursos::find($id) : null;
        if (!$curso) {
            return redirect()->route('cursos.index')->with('error', 'El curso solicitado no fue encontrado.');
        }
        return view('cursos.editar', compact('curso'));
    }

    public function store(Request $request){
        $validated = $request->validate(Cursos::$createRules);
        DB::beginTransaction();
        try{
            $curso = new Cursos;
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion') ?? '';
            $curso->codigo = $request->input('codigo');
            $curso->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        }
        return redirect()->route('cursos.index')->with('success','El curso "'.$curso->nombre.'" ha sido creado');
    }

    public function update(Request $request){
        $id = $request->input('id') ?? $request->id;
        $curso = $id ? Cursos::find($id) : null;
        if (!$curso) {
            return redirect()->route('cursos.index')->with('error', 'El curso solicitado no fue encontrado.');
        }

        $validated = $request->validate(Cursos::updateRules($curso->id));
        try{
            DB::beginTransaction();
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion') ?? '';
            $curso->codigo = $request->input('codigo');
            $curso->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        }
        return redirect()->route('cursos.index')->with('success','El curso ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        $id = $request->input('id') ?? $request->id;
        $curso = $id ? Cursos::find($id) : null;
        if (!$curso) {
            return redirect()->route('cursos.index')->with('error', 'El curso solicitado no fue encontrado.');
        }
        try{
            DB::beginTransaction();
            $curso->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('cursos.index')->with('success', 'El curso '.$curso->nombre.' ha sido eliminado');
    }

    public function listado_cursos(Request $request){
        $user = auth()->user();
        $cursosQuery = $user->cursos();
        $busqueda = is_string($request->buscar_curso) ? trim($request->buscar_curso) : null;
        if ($busqueda !== null && $busqueda !== '') {
            $busquedaEscapada = addcslashes($busqueda, '%_\\');
            $cursosQuery = $cursosQuery->where(function ($query) use ($busquedaEscapada) {
                $query->where('cursos.nombre', 'LIKE', '%' . $busquedaEscapada . '%')
                      ->orWhere('cursos.codigo', 'LIKE', '%' . $busquedaEscapada . '%')
                      ->orWhere('cursos.descripcion', 'LIKE', '%' . $busquedaEscapada . '%');
            });
        }

        $cursos = $cursosQuery->orderBy('cursos.nombre')->paginate(6)->withQueryString();
        if ($cursos->lastPage() > 0 && (int) $request->input('page', 1) > $cursos->lastPage()) {
            return redirect()->to($request->fullUrlWithQuery(['page' => $cursos->lastPage()]));
        }

        $pageCourseIds = $cursos->pluck('id')->all();
        $problemasPorCurso = collect();
        $resueltosPorCurso = collect();
        $certamenesPorCurso = collect();

        if (!empty($pageCourseIds)) {
            $problemasPorCurso = DB::table('disponible')
                ->join('problemas', 'problemas.id', '=', 'disponible.id_problema')
                ->whereIn('disponible.id_curso', $pageCourseIds)
                ->where('disponible.visible', true)
                ->where('problemas.visible', true)
                ->groupBy('disponible.id_curso')
                ->selectRaw('disponible.id_curso, COUNT(DISTINCT problemas.id) as total')
                ->pluck('total', 'id_curso');

            $resueltosPorCurso = DB::table('envio_solucion_problemas')
                ->join('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
                ->join('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
                ->join('disponible', function ($join) {
                    $join->on('disponible.id_problema', '=', 'resolver.id_problema')
                         ->on('disponible.id_curso', '=', 'cursa.id_curso');
                })
                ->join('problemas', 'problemas.id', '=', 'resolver.id_problema')
                ->where('cursa.id_usuario', $user->id)
                ->whereIn('cursa.id_curso', $pageCourseIds)
                ->whereNull('envio_solucion_problemas.id_certamen')
                ->where('envio_solucion_problemas.solucionado', true)
                ->where('disponible.visible', true)
                ->where('problemas.visible', true)
                ->groupBy('cursa.id_curso')
                ->selectRaw('cursa.id_curso, COUNT(DISTINCT resolver.id_problema) as resueltos')
                ->pluck('resueltos', 'id_curso');

            $certamenesPorCurso = DB::table('certamenes')
                ->whereIn('id_curso', $pageCourseIds)
                ->groupBy('id_curso')
                ->selectRaw('id_curso, COUNT(*) as total')
                ->pluck('total', 'id_curso');
        }

        foreach ($cursos as $cursoItem) {
            $totalP = (int) ($problemasPorCurso[$cursoItem->id] ?? 0);
            $resP = min($totalP, (int) ($resueltosPorCurso[$cursoItem->id] ?? 0));
            $cursoItem->problemas_visibles_count = $totalP;
            $cursoItem->problemas_resueltos_count = $resP;
            $cursoItem->progreso_porcentaje = $totalP > 0 ? (int) round(($resP / $totalP) * 100) : 0;
            $cursoItem->certamenes_count = (int) ($certamenesPorCurso[$cursoItem->id] ?? 0);
        }

        return view('plataforma.cursos.index', compact(
            'cursos',
            'busqueda'
        ));
    }

    public function estudiantes(Request $request, $id)
    {
        $curso = Cursos::withCount(['users', 'problemas'])->findOrFail($id);

        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id)->exists()) {
            abort(403, 'No tienes permisos para gestionar estudiantes en este curso.');
        }

        // Estudiantes actualmente matriculados en este curso
        $estudiantes = $curso->users()
            ->with('roles')
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        // Estudiantes disponibles para enrolar (que no estén ya en el curso y no sean administradores)
        $estudiantes_disponibles = User::whereDoesntHave('cursos', function ($q) use ($id) {
                $q->where('cursos.id', $id);
            })
            ->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'administrador');
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        // Otros cursos para la opción de copiar estudiantes
        $otros_cursos = auth()->user()->hasRole('administrador')
            ? Cursos::where('id', '!=', $id)->withCount('users')->orderBy('nombre')->get()
            : auth()->user()->cursos()->where('cursos.id', '!=', $id)->withCount('users')->orderBy('nombre')->get();

        return view('cursos.estudiantes', compact('curso', 'estudiantes', 'estudiantes_disponibles', 'otros_cursos'));
    }

    public function agregar_estudiantes(Request $request, $id)
    {
        $curso = Cursos::findOrFail($id);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id)->exists()) {
            abort(403);
        }

        $request->validate([
            'estudiantes' => 'required|array|min:1',
            'estudiantes.*' => 'exists:users,id',
        ]);

        $curso->users()->syncWithoutDetaching($request->estudiantes);

        return redirect()->route('cursos.estudiantes', ['id' => $id])
            ->with('success', 'Se han inscrito ' . count($request->estudiantes) . ' estudiante(s) al curso correctamente.');
    }

    public function agregar_estudiantes_masivo(Request $request, $id)
    {
        $curso = Cursos::findOrFail($id);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id)->exists()) {
            abort(403);
        }

        $request->validate([
            'lista_estudiantes' => 'required|string',
        ]);

        $lineas = preg_split('/[\r\n,;]+/', $request->lista_estudiantes);
        $encontrados = collect();
        $no_encontrados = collect();

        foreach ($lineas as $linea) {
            $token = trim($linea);
            if (empty($token)) {
                continue;
            }

            // Normalizar RUT (quitar puntos y espacios)
            $rutLimpio = str_replace(['.', ' '], '', $token);

            $user = User::where('email', $token)
                ->orWhere('username', $token)
                ->orWhere('rut', $rutLimpio)
                ->orWhere('rut', $token)
                ->orWhere('rut', str_replace('-', '', $rutLimpio))
                ->first();

            if ($user) {
                $encontrados->push($user->id);
            } else {
                $no_encontrados->push($token);
            }
        }

        $encontrados = $encontrados->unique();
        if ($encontrados->isNotEmpty()) {
            $curso->users()->syncWithoutDetaching($encontrados->toArray());
        }

        $msg = 'Se inscribieron ' . $encontrados->count() . ' estudiante(s) correctamente.';
        if ($no_encontrados->isNotEmpty()) {
            $msg .= ' No se encontraron los siguientes registros (' . $no_encontrados->count() . '): ' . $no_encontrados->take(5)->implode(', ') . ($no_encontrados->count() > 5 ? '...' : '');
            return redirect()->route('cursos.estudiantes', ['id' => $id])->with('warning', $msg);
        }

        return redirect()->route('cursos.estudiantes', ['id' => $id])->with('success', $msg);
    }

    public function copiar_estudiantes_curso(Request $request, $id)
    {
        $curso_destino = Cursos::findOrFail($id);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id)->exists()) {
            abort(403);
        }

        $request->validate([
            'id_curso_origen' => 'required|exists:cursos,id|different:' . $id,
        ]);

        $curso_origen = Cursos::findOrFail($request->id_curso_origen);
        $ids = $curso_origen->users()->pluck('users.id')->toArray();

        if (empty($ids)) {
            return redirect()->route('cursos.estudiantes', ['id' => $id])
                ->with('warning', 'El curso de origen seleccionado no tiene estudiantes matriculados.');
        }

        $curso_destino->users()->syncWithoutDetaching($ids);

        return redirect()->route('cursos.estudiantes', ['id' => $id])
            ->with('success', 'Se han importado ' . count($ids) . ' estudiante(s) desde el curso "' . $curso_origen->nombre . '".');
    }

    public function eliminar_estudiante(Request $request, $id)
    {
        $curso = Cursos::findOrFail($id);
        if (!auth()->user()->hasRole('administrador') && !auth()->user()->cursos()->where('cursos.id', $id)->exists()) {
            abort(403);
        }

        $ids = $request->input('user_ids');
        if (empty($ids) && $request->filled('user_id')) {
            $ids = [$request->input('user_id')];
        }

        if (empty($ids)) {
            return redirect()->route('cursos.estudiantes', ['id' => $id])
                ->with('error', 'No se seleccionó ningún estudiante para desmatricular.');
        }

        $curso->users()->detach($ids);

        return redirect()->route('cursos.estudiantes', ['id' => $id])
            ->with('success', 'Se ha(n) desmatriculado a ' . count($ids) . ' estudiante(s) del curso.');
    }
}
