<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cursos;
use App\Models\Casos_Pruebas;
use App\Models\Resolver;
use App\Models\LenguajesProgramaciones;
use App\Models\Categoria_Problema;
use App\Models\JuecesVirtuales;
use App\Models\ResolucionCertamenes;
use App\Models\EnvioSolucionProblema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use PDF;

class ProblemasController extends Controller
{
    public function index(Request $request)
    {
        $problemas = Problemas::whereHas('cursos', function (Builder $query) {
            $cursos = auth()->user()->cursos()->select('cursos.id')->pluck('id')->toArray();
            $query->whereIn('cursos.id', $cursos);
        })->get()->map(function($item){
            $item->creado = Carbon::parse($item->created_at)->locale('es_ES')->isoFormat('lll');
            if(isset($item->fecha_inicio)){
                $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            }else{
                $item->fecha_inicio = "No definido";
            }

            if(isset($item->fecha_termino)){
                $item->fecha_termino = Carbon::parse($item->fecha_termino)->locale('es_ES')->isoFormat('lll');
            }else{
                $item->fecha_termino = "No definido";
            }
            
            return $item;
        });

        return view('problemas.index', compact('problemas'));
    }

    public function crear()
    {
        $categorias = Categoria_Problema::all();
        $cursos = auth()->user()->cursos()->get();
        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.crear', compact('categorias', 'cursos', 'lenguajes'))->with('accion', "crear");;
    }

    public function editar(Request $request)
    {
        $problema = Problemas::find($request->id);
        $problema->sql = $problema->lenguajes()->where('lenguajes_programaciones.nombre', 'LIKE', '%sql%')->exists();
        if(isset($problema->fecha_inicio)){
            $problema->fecha_inicio = Carbon::parse($problema->fecha_inicio)->toDateTimeString();
        }
        if(isset($problema->fecha_termino)){
            $problema->fecha_termino = Carbon::parse($problema->fecha_termino)->toDateTimeString();
        }
        if($problema->memoria_limite==0){
            $problema->memoria_limite = null;
        }
        $categorias = Categoria_Problema::all();
        $cursos = auth()->user()->cursos()->get();
        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.editar', compact('problema', 'categorias', 'lenguajes', 'cursos'))->with('accion', "editar");
    }
    public function editar_config_llm(Request $request)
    {
        $problema = Problemas::find($request->id);
        return view('problemas.llm_config', compact('problema'));
    }

    public function configurar_llm(Request $request)
    {
        $validated = $request->validate(Problemas::$llm_config_rules);
        try {
            $problema = Problemas::find($request->id);
            if (isset($request->habilitar_llm)) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }
            $problema->limite_llm = $request->input('limite_llm');
            $problema->body_problema_resumido = $request->input('body_problema_resumido');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route("problemas.configurar_llm", ["id" => $request->id])->with("error", $e->getMessage());
        }
        return redirect()->route("problemas.index")->with("success", "Se ha configurado la Large Language Model en el problema " . $problema->codigo . " correctamente");
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Problemas::createRules(isset($request->fecha_inicio), isset($request->fecha_termino), null,$request->sql));
        try {
            db::beginTransaction();
            $problema = new Problemas;
            $this->set_datos_problemas($problema, $request);
            db::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id" => $problema->id])->with('success', 'El Problema ' . $problema->nombre . ' ha sido creado, ingrese los casos de prueba.');
    }
    private static function set_datos_problemas(Problemas $problema, Request $request){
            $problema->nombre = $request->input('nombre');
            $problema->codigo = $request->input('codigo');
            if(isset($request->set_fecha_inicio)){
                $problema->fecha_inicio = Carbon::parse( $request->input('fecha_inicio'));
            }else{
                $problema->fecha_inicio = null;
            }
            if(isset($request->set_fecha_termino)){
                $problema->fecha_termino = Carbon::parse( $request->input('fecha_termino'));
            }else{
                $problema->fecha_termino = null;
            }
            if(isset($request->memoria_limite) && $request->memoria_limite>0){
                $problema->memoria_limite = $request->input('memoria_limite');
            }else{
                $problema->memoria_limite = 0;
            }
            $problema->tiempo_limite = $request->input('tiempo_limite');
            $problema->body_problema = $request->input('body_problema');
            $problema->body_problema_resumido = $request->input('body_problema_resumido');
            if (isset($request->visible)) {
                $problema->visible = true;
            } else {
                $problema->visible = false;
            }

            if (isset($request->habilitar_llm)) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }
            $problema->limite_llm = $request->input('limite_llm')? $request->input('limite_llm') : 0;
            if(isset($request->archivos_adicionales)){
                $archivo = $request->file('archivos_adicionales');
                $nombre = $archivo->hashName();
                $request->file('archivos_adicionales')->storeAs(
                    'archivos_adicionales',$nombre,'public'
                );
                if(isset($problema->archivo_adicional)){
                    Storage::delete('public/archivos_adicionales/'.$problema->archivo_adicional);
                }
                $problema->archivo_adicional = $nombre;
            }
            $problema->save();
            $problema->cursos()->sync($request->input('cursos'));
            if($request->sql==true){
                $problema->lenguajes()->sync(LenguajesProgramaciones::where('abreviatura', '=', 'sql')->get()->pluck('id'));
            }else{
                $problema->lenguajes()->sync($request->input('lenguajes'));
                if(isset($problema->archivo_adicional)){
                    Storage::delete('public/archivos_adicionales/'.$problema->archivo_adicional);
                    $problema->archivo_adicional = null;
                }
            }
            $problema->categorias()->sync($request->input('categorias'));
    }
    public function update(Request $request)
    {

        $validated = $request->validate(Problemas::createRules(isset($request->fecha_inicio),isset($request->fecha_termino),$request->codigo, $request->sql, true));
        try {
            db::beginTransaction();
            $problema = Problemas::find($request->id);
            $this->set_datos_problemas($problema, $request);
            db::commit();
        } catch (\Exception $e) {
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El problema ha "'.$problema->nombre.'" sido modificado');
    }
    public function eliminar(Request $request)
    {
        try {
            DB::beginTransaction();
            $problema = Problemas::find($request->id);
            $problema->delete();
            Storage::delete('public/archivos_adicionales/'.$problema->archivo_adicional);
            DB::commit();
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El problema "' . $problema->nombre . '" ha sido eliminado');
    }

    public function update_editorial(Request $request)
    {
        try {
            $problema = Problemas::find($request->id);
            $problema->body_editorial = $request->input('body_editorial');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->back()->withInput($request->input())->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El editorial para el problema "' . $problema->nombre . '" ha sido modificado');
    }

    public function editar_editorial(Request $request)
    {
        try {
            $problema = Problemas::find($request->id);
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return view('problemas.editorial', compact('problema'));
    }

    public function listado_problemas(Request $request)
    {
        try {
            $curso = auth()->user()->cursos()->find($request->id);
            //verifica si el usuario está registrado al curso que se quiere acceder 
            if (!isset($curso)) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al curso al que estás tratando de acceder');
            }
            //tabla intermedia entre usuario y curso
            $curso_usuario_pivot = $curso->pivot;
            $fecha_ahora = Carbon::now();
            $problemas = $curso->problemas()->where('visible', '=', true)->get()->map(function ($problema) use($curso_usuario_pivot){
                $problema->puntaje_total = $problema->casos_de_prueba()->get()->pluck('puntos')->sum();
                $problema->categorias = implode(',', array: $problema->categorias()->get()->pluck('nombre')->toArray());
                $problema->creado = Carbon::parse($problema->created_at)->locale('es_ES')->isoFormat('lll');
                $problema->resuelto = $problema->envios()->where('id_cursa', '=', $curso_usuario_pivot->id)->where('solucionado', '=', true)->exists();
                return $problema;
            })->unique();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.index', compact('problemas', 'curso'));
    }

    public function ver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            if(!Cursos::where('cursos.id','=',$request->id_curso)->exists()){
                return redirect()->route('cursos.listado')->with('error', 'El curso que estás tratando de acceder no existe.');
            }
            $curso_usuario = auth()->user()->cursos()->find($request->id_curso);
            if ($problema->cursos()->where('cursos.id', '=', $curso_usuario)->exists() || $problema->visible == false) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }
            $problema->disponible = true;
            //verifica si el usuario ha solucionado el problema mediante la tabla intermedia de curso y usuario (pivot)
            $problema->estado = $problema->envios()->where('id_cursa', '=', $curso_usuario->pivot->id)->whereNull('id_certamen')->where('solucionado', '=', true)->exists();
            $now = Carbon::now();
            if(isset($problema->fecha_inicio)){
            $fecha_inicio = Carbon::parse($problema->fecha_inicio);
                if ($now->lt($fecha_inicio)) {
                    $problema->disponible = false;
                }
            $problema->fecha_inicio = isset($problema->fecha_inicio)? $fecha_inicio->locale('es_ES')->isoFormat('lll') : "No Definido";
            }
            if (isset($problema->fecha_termino)) {
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino)) {
                    $problema->disponible = false;
                }
            $problema->fecha_termino = isset($problema->fecha_termino)? $fecha_termino->locale('es_ES')->isoFormat('lll') : "No Definido";
        }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_problema', compact('problema'))->with('id_curso', $request->id_curso);
    }
    public function ver_editorial(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            $cursos_usuario = auth()->user()->cursos()->get()->pluck('id')->toArray();
            if (!$problema->cursos()->whereIn('cursos.id', $cursos_usuario)->exists() || $problema->visible == false) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }
            if (isset($problema->fecha_termino)) {
                $now = Carbon::now();
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino)) {
                    return redirect()->route('cursos.listado')->with('error', 'El problema ' . $problema->nombre . 'no está disponible');
                }
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_editorial', compact('problema'))->with('id_curso', $request->id_curso);
    }

    public function resolver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            $curso_usuario  = auth()->user()->cursos()->find($request->id_curso);
            $lenguajes = $problema->lenguajes()->get();
            $jueces = JuecesVirtuales::all();
            $res_certamen = null;
            if(isset($request->token_certamen)){
                $res_certamen = ResolucionCertamenes::where('token', '=', $request->token_certamen)->first();
                $last_envio = $problema->envios()->where('id_certamen', '=', $res_certamen->id)->orderBy('created_at', 'DESC')->first();
            }else{
                $last_envio = $problema->envios()->where('id_cursa', '=', $curso_usuario->pivot->id)->whereNull('id_certamen')->orderBy('created_at', 'DESC')->first();
            }
            if (isset($last_envio->termino) || !isset($last_envio)) {
                DB::beginTransaction();
                $envio = new EnvioSolucionProblema;
                $envio->token = Str::random(40);
                $envio->inicio = Carbon::now();
                if(isset($last_envio->termino)){
                    $envio->ProblemaLenguaje()->associate($lenguajes->find($last_envio->lenguaje->id)->pivot);
                }else{
                    $envio->ProblemaLenguaje()->associate($lenguajes[0]->pivot);
                }
                $envio->CursoUsuario()->associate($curso_usuario->pivot);
                if(isset($res_certamen)){
                    $envio->id_certamen = $res_certamen->id;
                }else{
                    DB::table('disponible')->where('id_curso', '=', $request->id_curso)->where('id_problema', '=', $problema->id)->increment('cantidad_intentos');
                }
                if(isset($last_envio->termino) && $last_envio->solucionado==false){
                    $codigo = $last_envio->codigo;
                    $envio->codigo = $codigo;
                    $envio->inicio = $last_envio->inicio;
                }
                $envio->save();
                $last_envio = $envio;
                DB::commit();
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.resolver_problema', compact('problema', 'lenguajes', 'jueces', 'last_envio','res_certamen'))->with('id_curso', $request->id_curso);
    }

    public function pdf_enunciado(Request $request){
        $problema = Problemas::with(['categorias', 'lenguajes'])->find($request->id_problema);
        $pdf = PDF::loadView('plataforma.problemas.pdf_enunciado', compact('problema'));
        return $pdf->download($problema->codigo.' - enunciado.pdf');
    }

    public function guardar_codigo(Request $request){
        try{
            DB::beginTransaction();
            $last_envio = EnvioSolucionProblema::where('id_resolver', '=', $request->id_resolver)->where('id_cursa', '=', $request->id_cursa)->orderBy('created_at', 'DESC')->first();
            $last_envio->codigo = $request->codigo_save;
            if(isset($request->lenguaje_save)){
                $resolver = Resolver::where('id_problema', '=', $request->id_problema)->where('id_lenguaje', '=', $request->lenguaje_save)->first();
                $last_envio->ProblemaLenguaje()->dissociate();
                $last_envio->ProblemaLenguaje()->associate($resolver);
            }
            $last_envio->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('problemas.resolver', ['codigo'=>$request->codigo_problema, 'id_curso'=>$request->id_curso])->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.ver', ['codigo'=>$request->codigo_problema, 'id_curso'=>$request->id_curso])->with('succes', 'El código desarrollado ha sido almacenado');
    }

}
