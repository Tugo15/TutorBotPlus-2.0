<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Categoria_Problema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
class CategoriaProblemaController extends Controller
{
    public function index(Request $request)
    {
        $categorias = Categoria_Problema::all()->map(function($categoria){
            $categoria->fecha = carbon::parse($categoria->created_at)->locale('es_ES')->isoFormat('lll');
            return $categoria;
        });
        return view('categoria_problemas.index', compact('categorias'));
    }

    public function crear(){
        return view('categoria_problemas.crear');
    }

    public function editar(Request $request){
        $categoria = Categoria_Problema::find($request->id);
        return view('categoria_problemas.editar', compact('categoria'));
    }

    public function store(Request $request){
        $validated = $request->validate(Categoria_Problema::$rules);
        
        db::beginTransaction();
        try{
            $categoria = new Categoria_Problema;
            $categoria->nombre = $request->input('nombre');
            $categoria->save();
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        }
        return redirect()->route('categorias.index')->with('success','La categoria "'.$categoria->nombre.'"ha sido creado');
    }

    public function update(Request $request){
        $validated = $request->validate(Categoria_Problema::$rules);
        try{
            db::beginTransaction();
            $categoria = Categoria_Problema::find($request->id);
            $categoria->nombre = $request->input('nombre');
            $categoria->save();
            db::commit();
        }catch(\Exception $e){
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        }
        return redirect()->route('categorias.index')->with('success','La categoria ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $categoria = Categoria_Problema::find($request->id);
            $categoria->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('categorias.index')->with('success', 'La categoria "'.$categoria->nombre.'" ha sido eliminado');
    }
}
