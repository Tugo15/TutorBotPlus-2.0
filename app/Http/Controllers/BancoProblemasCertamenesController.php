<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\banco_problemas_certamenes;
use App\Models\Certamenes;
use App\Models\Problemas;
use App\Models\Categoria_Problema;
use Illuminate\Support\Facades\DB;

class BancoProblemasCertamenesController extends Controller
{
    public function index(Request $request)
    {
        $certamen = Certamenes::find($request->id_certamen);
        $categorias_id = $certamen->categorias()->pluck('categoria__problemas.id')->toArray();
        $problemas_usados = DB::table('problemas')->join('pertenece', 'pertenece.id_problema', '=', 'problemas.id')->whereIn('id_categoria', $categorias_id)->join('disponible', 'disponible.id_problema', '=', 'problemas.id')->where('disponible.id_curso', '=', $certamen->id_curso)->pluck('problemas.id')->toArray();
        $categorias = Categoria_Problema::has('problemas')->whereNotIn('categoria__problemas.id', $categorias_id)->get()->map(function ($categoria) use ($problemas_usados, $certamen) {
            $problemas = $categoria->problemas()->join('disponible', 'disponible.id_problema', '=', 'problemas.id')->where('disponible.id_curso', '=', $certamen->id_curso)->get();
            $problemas = $problemas->except($problemas_usados);
            if (sizeof(value: $problemas) > 0) {
                return $categoria;
            }
        });
        $categorias = $categorias->filter();
        $banco_categorias = DB::table('banco_problemas_certamenes')
            ->join('categoria__problemas', 'banco_problemas_certamenes.id_categoria', '=', 'categoria__problemas.id')
            ->select('categoria__problemas.nombre', 'banco_problemas_certamenes.id', 'categoria__problemas.id as id_categoria')
            ->where('banco_problemas_certamenes.id_certamen', '=', $request->id_certamen)
            ->get();
        return view('certamen.banco_de_problemas.add', compact('banco_categorias', 'certamen', 'categorias'));
    }
    public function add(Request $request)
    {
        $request->validate([
            "categoria" => "required",
        ]);
        try {
            $certamen = Certamenes::find($request->id_certamen);
            if (!$certamen->categorias()->where('categoria__problemas.id', '=', $request->input('categoria'))->exists()) {
                DB::beginTransaction();
                $categoria = Categoria_Problema::find($request->input('categoria'));
                $certamen->categorias()->attach($request->input('categoria'));
                DB::commit();
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('certamen.banco_problemas', ['id_certamen' => $request->id_certamen])->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.banco_problemas', $request->id_certamen)->with("success", "La categoría '" . $categoria->nombre . "' ha sido añadido.");
    }
    public function delete(Request $request)
    {
        try {
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id_certamen);
            if ($certamen->categorias()->where('categoria__problemas.id', '=', $request->input('id_categoria'))->exists()) {
                $categoria = $certamen->categorias()->find($request->id_categoria);
                $certamen->categorias()->detach($request->id_categoria);
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('certamen.banco_problemas')->with("error", $e->getMessage());
        }
        $nombre_categoria = isset($categoria) ? $categoria->nombre : "";
        return redirect()->route('certamen.banco_problemas', $request->id_certamen)->with("success", "La categoría '" . $nombre_categoria . "' ha sido removido.");
    }
}
