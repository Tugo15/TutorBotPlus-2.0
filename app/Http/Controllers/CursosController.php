<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CursosController extends Controller
{
    public function index(Request $request)
    {   
        if(auth()->user()->hasRole('administrador')){
            $cursos = Cursos::all();
        }else{
            $cursos = auth()->user()->cursos()->get();
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
        db::beginTransaction();
        try{
            $curso = new Cursos;
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion');
            $curso->codigo = $request->input('codigo');
            $curso->save();
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('cursos.index')->with('error', $e->getMessage());
        }
        return redirect()->route('cursos.index')->with('success','El curso "'.$curso->nombre.'" ha sido creado');
    }

    public function update(Request $request){
        $validated = $request->validate(Cursos::updateRules($request->id));
        try{
            db::beginTransaction();
            $curso = Cursos::find($request->id);
            $curso->nombre = $request->input('nombre');
            $curso->descripcion = $request->input('descripcion');
            $curso->codigo = $request->input('codigo');
            $curso->save();
            db::commit();
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
            db::rollBack();
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
}
