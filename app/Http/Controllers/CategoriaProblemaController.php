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
        $id = $request->input('id') ?? $request->id;
        $categoria = $id ? Categoria_Problema::find($id) : null;
        if (!$categoria) {
            return redirect()->route('categorias.index')->with('error', 'La categoría solicitada no fue encontrada.');
        }
        return view('categoria_problemas.editar', compact('categoria'));
    }

    public function store(Request $request){
        $validated = $request->validate(Categoria_Problema::$rules);
        
        DB::beginTransaction();
        try{
            $categoria = new Categoria_Problema;
            $categoria->nombre = $request->input('nombre');
            $categoria->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        }
        return redirect()->route('categorias.index')->with('success', 'La categoría "' . $categoria->nombre . '" ha sido creada');
    }

    public function update(Request $request){
        $id = $request->input('id') ?? $request->id;
        $categoria = $id ? Categoria_Problema::find($id) : null;
        if (!$categoria) {
            return redirect()->route('categorias.index')->with('error', 'La categoría solicitada no fue encontrada.');
        }

        $validated = $request->validate(Categoria_Problema::$rules);
        try{
            DB::beginTransaction();
            $categoria->nombre = $request->input('nombre');
            $categoria->save();
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        }
        return redirect()->route('categorias.index')->with('success', 'La categoría ha sido modificada');
    }
    public function eliminar(Request $request)
    {
        $id = $request->input('id') ?? $request->id;
        $categoria = $id ? Categoria_Problema::find($id) : null;
        if (!$categoria) {
            return redirect()->route('categorias.index')->with('error', 'La categoría solicitada no fue encontrada.');
        }
        try{
            DB::beginTransaction();
            $categoria->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('categorias.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('categorias.index')->with('success', 'La categoría "' . $categoria->nombre . '" ha sido eliminada');
    }
}
