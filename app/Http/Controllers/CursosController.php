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
        $curso = Cursos::find($request->id);
        return view('cursos.editar', compact('curso'));
    }

    public function store(Request $request){
        $validated = $request->validate(Cursos::$createRules);
        DB::beginTransaction();
        try{
            $curso = new Cursos;
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion');
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
        $validated = $request->validate(Cursos::updateRules($request->id));
        try{
            DB::beginTransaction();
            $curso = Cursos::find($request->id);
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion');
            $curso->codigo = $request->input('codigo');
            $curso->save();
            DB::commit();
        }catch(\Exception $e){
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        }
        return redirect()->route('cursos.index')->with('success','El curso ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $curso = Cursos::find($request->id);
            $curso->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('cursos.index')->with('success', 'El curso '.$curso->nombre.' ha sido eliminado');
    }

    public function listado_cursos(Request $request){
        $cursos = auth()->user()->cursos();
        $busqueda = $request->buscar_curso;
        if(isset($busqueda)){
            $cursos = $cursos->where('nombre', 'LIKE', '%'.$busqueda.'%');
        }
        
        $cursos = $cursos->paginate(5);
        return view('plataforma.cursos.index', compact('cursos'))->with('busqueda', $busqueda);
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

        // Estudiantes disponibles para enrolar (que no estén ya en el curso)
        $estudiantes_disponibles = User::whereDoesntHave('cursos', function ($q) use ($id) {
                $q->where('cursos.id', $id);
            })
            ->whereHas('roles', function ($q) {
                $q->where('name', 'estudiante');
            })
            ->orderBy('lastname')
            ->orderBy('firstname')
            ->get();

        if ($estudiantes_disponibles->isEmpty()) {
            $estudiantes_disponibles = User::whereDoesntHave('cursos', function ($q) use ($id) {
                    $q->where('cursos.id', $id);
                })
                ->whereDoesntHave('roles', function ($q) {
                    $q->where('name', 'administrador');
                })
                ->orderBy('lastname')
                ->orderBy('firstname')
                ->get();
        }

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
