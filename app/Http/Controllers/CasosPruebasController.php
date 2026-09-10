<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use App\Models\Casos_Pruebas;
use App\Models\LenguajesProgramaciones;
class CasosPruebasController extends Controller
{
    public function asignacion_casos(Request $request){
        $problema = Problemas::find($request->id);
        $sql_language = $problema->lenguajes()->where('abreviatura', '=', 'sql')->exists();
        if(!$sql_language){
            $casos = $problema->casos_de_prueba()->orderBy('created_at','desc')->get();
            return view("problemas.casos_pruebas.assign", compact('problema', 'casos'));
        }else{
            $caso = $problema->casos_de_prueba()->orderBy('created_at','desc')->first();
            return view("problemas.casos_pruebas.assign_sql", compact('problema', 'caso'));
        }
        
    }

    public function eliminar_caso(Request $request){
        try{
            DB::beginTransaction();
            $caso = Casos_Pruebas::find($request->id);
            $problema = Problemas::find($caso->id_problema);
            $problema->puntaje_total = $problema->puntaje_total - $caso->puntos;
            $caso->delete();
            $problema->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id"=>$caso->id_problema])->with('success', 'El caso de prueba '.$caso->id.' ha sido eliminado');

    }
    public function caso_sql(Request $request){
        $validated = $request->validate(Casos_Pruebas::$rules);
        try{
            DB::beginTransaction();
            $caso = Casos_Pruebas::where('id_problema', '=', $request->id)->first();
            $problema = Problemas::find( $request->id );
            if(!isset($caso)){
                $caso = new Casos_Pruebas;
            }
            $caso->ejemplo = true;
            $caso->salidas = $request->salidas;
            $caso->puntos = $request->puntos;
            $problema->casos_de_prueba()->save($caso);
            $problema->puntaje_total = $problema->puntaje_total + $caso->puntos;
            $problema->refresh();
            DB::commit();
        }catch( \PDOException $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id"=>$caso->id_problema])->with('success', 'El caso de prueba ha sido modificado');
    }
    public function add_caso(Request $request){

        $validated = $request->validate(Casos_Pruebas::$rules);
        try{
            DB::beginTransaction();
            $problema = Problemas::find( $request->id );

            $caso = new Casos_Pruebas;
            if(isset($request->puntos)){
                $caso->puntos = $request->input("puntos");
            }
            $caso->entradas = $request->input("entradas");
            $caso->salidas = $request->input("salidas");
            $caso->ejemplo = $request->has('ejemplo') ? true : false;
            $problema->casos_de_prueba()->save($caso);
            $problema->puntaje_total = $problema->puntaje_total + $caso->puntos;
            $problema->refresh();
            DB::commit();
        }catch( \PDOException $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id"=>$request->id])->with('success', 'El caso de prueba '.$caso->id.' ha sido añadido');

    }

    public function update_caso(Request $request){
        $validated = $request->validate([
            'id_caso' => ['required', 'exists:casos__pruebas,id'],
            'entradas' => ['nullable', 'string'],
            'salidas' => ['required', 'string'],
            'puntos' => ['nullable', 'numeric'],
        ]);

        try{
            DB::beginTransaction();
            $caso = Casos_Pruebas::findOrFail($request->id_caso);
            $problema = Problemas::findOrFail($caso->id_problema);

            $caso->entradas = $request->input("entradas");
            $caso->salidas = $request->input("salidas");
            $caso->puntos = isset($request->puntos) ? (float)$request->puntos : 0;
            $caso->ejemplo = $request->has('ejemplo') ? true : false;
            $caso->save();

            // Recalculate total score for problem
            $problema->puntaje_total = $problema->casos_de_prueba()->sum('puntos');
            $problema->save();

            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->back()->with('error', 'Error al modificar el caso de prueba: ' . $e->getMessage());
        }

        return redirect()->route('casos_pruebas.assign', ["id" => $caso->id_problema])->with('success', 'El caso de prueba #' . $caso->id . ' ha sido modificado exitosamente.');
    }
}
