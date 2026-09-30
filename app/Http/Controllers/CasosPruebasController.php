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

    public function bulk_add_casos(Request $request)
    {
        $id_problema = $request->input('id_problema', $request->input('id'));

        $request->validate([
            'contenido_masivo' => ['required', 'string'],
            'puntos_defecto' => ['nullable', 'numeric'],
        ]);

        $problema = Problemas::findOrFail($id_problema);
        $rawContent = trim($request->contenido_masivo);
        $puntosDefecto = $request->filled('puntos_defecto') ? (float)$request->puntos_defecto : 10;
        $ejemploDefecto = $request->has('ejemplo_defecto') ? true : false;

        $casosParaInsertar = [];

        // 1. Probar formato JSON
        if (\Illuminate\Support\Str::startsWith($rawContent, '[') || \Illuminate\Support\Str::startsWith($rawContent, '{')) {
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                $items = (isset($decoded['entradas']) || isset($decoded['salidas'])) ? [$decoded] : $decoded;
                foreach ($items as $item) {
                    if (is_array($item) && (isset($item['salidas']) || isset($item['output']))) {
                        $casosParaInsertar[] = [
                            'entradas' => (string)($item['entradas'] ?? $item['input'] ?? ''),
                            'salidas' => (string)($item['salidas'] ?? $item['output'] ?? ''),
                            'puntos' => isset($item['puntos']) ? (float)$item['puntos'] : (isset($item['points']) ? (float)$item['points'] : $puntosDefecto),
                            'ejemplo' => isset($item['ejemplo']) ? (bool)$item['ejemplo'] : (isset($item['example']) ? (bool)$item['example'] : $ejemploDefecto),
                        ];
                    }
                }
            }
        }

        // 2. Probar formato por bloques con delimitadores (=== o --- o etiquetas INPUT/OUTPUT)
        if (empty($casosParaInsertar)) {
            if (\Illuminate\Support\Str::contains($rawContent, 'INPUT:') || \Illuminate\Support\Str::contains($rawContent, 'ENTRADA:') || \Illuminate\Support\Str::contains($rawContent, '===') || \Illuminate\Support\Str::contains($rawContent, '---')) {
                $blocks = preg_split('/(={3,}|-{3,})/', $rawContent);
                foreach ($blocks as $block) {
                    $block = trim($block);
                    if (empty($block)) continue;

                    $entradas = '';
                    $salidas = '';
                    $puntos = $puntosDefecto;
                    $ejemplo = $ejemploDefecto;

                    if (preg_match('/(?:INPUT|ENTRADA)\s*:\s*(.*?)(?=(?:OUTPUT|SALIDA|PUNTOS|POINTS|EJEMPLO|EXAMPLE)\s*:|$)/s', $block, $mIn)) {
                        $entradas = trim($mIn[1]);
                    }
                    if (preg_match('/(?:OUTPUT|SALIDA)\s*:\s*(.*?)(?=(?:INPUT|ENTRADA|PUNTOS|POINTS|EJEMPLO|EXAMPLE)\s*:|$)/s', $block, $mOut)) {
                        $salidas = trim($mOut[1]);
                    }
                    if (preg_match('/(?:PUNTOS|POINTS)\s*:\s*(\d+(?:\.\d+)?)/i', $block, $mPts)) {
                        $puntos = (float)$mPts[1];
                    }
                    if (preg_match('/(?:EJEMPLO|EXAMPLE)\s*:\s*(1|true|si|yes)/i', $block)) {
                        $ejemplo = true;
                    }

                    if ($salidas !== '' || $entradas !== '') {
                        $casosParaInsertar[] = [
                            'entradas' => $entradas,
                            'salidas' => $salidas,
                            'puntos' => $puntos,
                            'ejemplo' => $ejemplo,
                        ];
                    }
                }
            }
        }

        // 3. Probar formato de líneas separadas por Pipe (|) o Flecha (=>)
        if (empty($casosParaInsertar)) {
            $lines = explode("\n", str_replace("\r", "", $rawContent));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                if (\Illuminate\Support\Str::contains($line, '|')) {
                    $parts = explode('|', $line, 2);
                    $casosParaInsertar[] = [
                        'entradas' => trim($parts[0]),
                        'salidas' => trim($parts[1]),
                        'puntos' => $puntosDefecto,
                        'ejemplo' => $ejemploDefecto,
                    ];
                } elseif (\Illuminate\Support\Str::contains($line, '=>')) {
                    $parts = explode('=>', $line, 2);
                    $casosParaInsertar[] = [
                        'entradas' => trim($parts[0]),
                        'salidas' => trim($parts[1]),
                        'puntos' => $puntosDefecto,
                        'ejemplo' => $ejemploDefecto,
                    ];
                }
            }
        }

        if (empty($casosParaInsertar)) {
            return redirect()->back()->with('error', 'No se pudieron reconocer casos de prueba válidos. Verifique el formato e intente nuevamente.');
        }

        try {
            DB::beginTransaction();
            $insertados = 0;
            foreach ($casosParaInsertar as $data) {
                $caso = new Casos_Pruebas();
                $caso->id_problema = $problema->id;
                $caso->entradas = $data['entradas'];
                $caso->salidas = $data['salidas'];
                $caso->puntos = $data['puntos'];
                $caso->ejemplo = $data['ejemplo'];
                $caso->save();
                $insertados++;
            }

            $problema->puntaje_total = $problema->casos_de_prueba()->sum('puntos');
            $problema->save();
            DB::commit();

            return redirect()->route('casos_pruebas.assign', ['id' => $problema->id])
                ->with('success', "¡Inyección masiva exitosa! Se han insertado {$insertados} casos de prueba en el problema.");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al inyectar masivamente los casos de prueba: ' . $e->getMessage());
        }
    }
}
