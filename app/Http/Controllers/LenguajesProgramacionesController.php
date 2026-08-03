<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\LenguajesProgramaciones;
use Carbon\Carbon;
class LenguajesProgramacionesController extends Controller
{
    public function index(Request $request)
    {
        $lenguajes = LenguajesProgramaciones::all()->map(function($item){
            $item->fecha = Carbon::parse($item->created_at)->locale('es_ES')->isoFormat('lll');
            return $item;
        });
        return view('lenguaje_programacion.index', compact('lenguajes'));
    }

    public function crear(){
        return view('lenguaje_programacion.crear');
    }

    public function editar(Request $request){
        $lenguaje = LenguajesProgramaciones::find($request->id);
        return view('lenguaje_programacion.editar', compact('lenguaje'));
    }

    public function store(Request $request){
        $validated = $request->validate(LenguajesProgramaciones::$createRules);
        
        db::beginTransaction();
        try{
            $lenguaje = new LenguajesProgramaciones;
            $lenguaje->nombre = $request->input('nombre');
            $lenguaje->codigo = $request->input('codigo');
            $lenguaje->abreviatura = $request->input('abreviatura');
            $lenguaje->extension = $request->input('extension');
            $lenguaje->save();
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('lenguaje_programacion.index')->with('error', $e->getMessage());
        }
        return redirect()->route('lenguaje_programacion.index')->with('success','El lenguaje de programacion "'.$lenguaje->nombre.'" ha sido creado');
    }

    public function update(Request $request){
        $validated = $request->validate(LenguajesProgramaciones::updateRules($request->id));
        try{
            db::beginTransaction();
            $lenguaje = LenguajesProgramaciones::find($request->id);
            $lenguaje->nombre = $request->input('nombre');
            $lenguaje->codigo = $request->input('codigo');
            $lenguaje->abreviatura = $request->input('abreviatura');
            $lenguaje->extension = $request->input('extension');
            $lenguaje->save();
            db::commit();
        }catch(\Exception $e){
            return redirect()->route('lenguaje_programacion.index')->with('error', $e->getMessage());
        }
        return redirect()->route('lenguaje_programacion.index')->with('success','El lenguaje de programación ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $lenguaje = LenguajesProgramaciones::find($request->id);
            $lenguaje->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('lenguaje_programacion.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('lenguaje_programacion.index')->with('success', 'El lenguaje de programación "'.$lenguaje->nombre.'" ha sido eliminado.');
    }
}
