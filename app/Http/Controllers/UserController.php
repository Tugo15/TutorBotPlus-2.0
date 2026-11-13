<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use App\Notifications\UsuarioCreado;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all()->map(function ($user) {
            $user->fecha = Carbon::parse($user->created_at)->locale('es_ES')->isoFormat('lll');
            return $user;
        });
        return view('usuarios.index', compact('users'));
    }

    public function ver_mi_perfil(Request $request){
        $info = auth()->user();
        return view('plataforma.mi_perfil', compact('info'));
    }
    public function crear()
    {
        $roles = Role::all();
        $cursos = Cursos::all();
        return view('usuarios.crear', compact('roles', 'cursos'))->with('accion', "crear");
        ;
    }

    public function bulk_insertion_form(Request $request)
    {
        return view('usuarios.bulk_insert');
    }
    public function bulk_insertion_example(Request $request)
    {
        if (!Storage::exists('public/examples/ejemplo_bulk_usuarios.csv')) {
            return back()->with('error', 'El archivo de ejemplo no se encuentra disponible.');
        }
        return Storage::download("public/examples/ejemplo_bulk_usuarios.csv");
    }
    
    public function actualizar_informacion(Request $request){
        $validated = $request->validate([
            'username' => ['required','string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', 'regex:/^[1-9]\d*\-(\d|k|K)$/', Rule::unique('users', 'rut')->ignore(auth()->user()->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore(auth()->user()->id)],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'password_actual' => ['nullable','required_with:password,password_confirmation', 'string'],
            'password' => ['nullable','min:8', 'required_with:password_confirmation,password_actual', 'same:password_confirmation'],
            'password_confirmation' => ['nullable','min:8'],
        ]);
        try{
            $user = User::find(auth()->user()->id);
            if ($request->filled('password') || $request->filled('password_actual')) {
                if (!Hash::check($request->password_actual ?? '', $user->password)) {
                    return back()->withInput()->with("error", "Contraseña actual incorrecta");
                }
                if ($request->filled('password')) {
                    $user->password = $request->password;
                }
            }
            $user->username = $request->input("username");
            $user->firstname = $request->input("firstname");
            $user->lastname = $request->input("lastname");
            $user->email = $request->input("email");
            $user->rut = $request->input("rut");
            $user->save();
        }catch(\PDOException $e){
            return back()->withInput()->with("error", $e->getMessage());
        }
        return redirect()->route("ver.perfil")->with("success", "Se ha modificado tu perfil de manera correcta.");
    }

    public function bulk_insertion(Request $request)
    {
        $validated = $request->validate([
            'csvFile' => 'required|mimes:csv,txt',
        ]);
        $contenido = file_get_contents($request->file('csvFile')->getRealPath());
        //remover caracteres especiales y nulos
        $contenido = str_replace(["\u{FEFF}", "\r", "\0", "\x00"], "", $contenido);
        //dividir el contenido por el separador de break space. Lo que transforma en un array de strings, donde cada elemento es un usuario.
        $string_arrays = explode("\n", $contenido);
        $keys_array = array('username', 'firstname', 'lastname', 'email', 'rut', 'cursos', 'roles');
        $users = [];
        try {
            DB::beginTransaction();
            foreach ($string_arrays as $key => $string_info) {
                $linea = trim($string_info);
                if ($linea === "") {
                    continue;
                }
                if (str_contains($linea, ';')) {
                    $usuario_data = array_map('trim', str_getcsv($linea, ';'));
                } else {
                    // Soporta delimitador coma respetando comillas y corchetes [INF-101,INF-102]
                    $protegido = preg_replace_callback('/\[[^\]]*\]/', function ($m) {
                        return '"' . trim($m[0]) . '"';
                    }, $linea);
                    $usuario_data = array_map('trim', str_getcsv($protegido, ','));
                }

                // Saltar fila de encabezado si viene en el CSV
                if (isset($usuario_data[0]) && in_array(strtolower($usuario_data[0]), ['username', 'usuario', 'nombre de usuario'])) {
                    continue;
                }

                if (sizeof($usuario_data) < 7) {
                    throw new \Exception("Faltan columnas en la fila " . ($key + 1) . ": [" . $string_info . "]. Se requieren 7 columnas (username;firstname;lastname;email;rut;[cursos];[roles]).");
                }
                $usuario_data = array_combine($keys_array, array_slice($usuario_data, 0, 7));
                $usuario_data["cursos"] = array_values(array_filter(array_map('trim', explode(",", str_replace(["[", "]"], "", $usuario_data["cursos"])))));
                $usuario_data["roles"] = array_values(array_filter(array_map('trim', explode(",", str_replace(["[", "]"], "", strtolower($usuario_data["roles"]))))));
                $validator = Validator::make($usuario_data, [
                    'username' => ['required', 'string', 'max:255'],
                    'rut' => ['required', 'string', 'unique:users,rut','regex:/^[1-9]\d*\-(\d|k|K)$/'],
                    'email' => ['required', 'email', 'unique:users,email'],
                    'firstname' => ['required', 'string', 'max:100'],
                    'lastname' => ['required', 'string', 'max:100'],
                    'cursos'=> ['required', 'array', 'min:1'],
                    'roles' =>  ['required', 'array', 'min:1'],
                ]);
                if ($validator->fails()) {
                    throw new \Exception("Error en la validación del usuario de la fila " . ($key + 1) . ":\n[" . $string_info . "].\n " . implode(", ", $validator->messages()->all()));
                }
                $usuario_nuevo = new User;
                $usuario_nuevo->username = $usuario_data["username"];
                $usuario_nuevo->firstname = $usuario_data["firstname"];
                $usuario_nuevo->lastname = $usuario_data["lastname"];
                $usuario_nuevo->email = $usuario_data["email"];
                $usuario_nuevo->rut = str_replace(' ', '', $usuario_data["rut"]);
                $usuario_nuevo->password = substr(str_replace(' ', '', $usuario_data["rut"]), 0, -2);
                $usuario_nuevo->save();
                $cursos_modelos = Cursos::whereIn("codigo", $usuario_data["cursos"])->get();
                $roles_modelos = Role::whereIn("name", $usuario_data["roles"])->get();
                if(sizeof($cursos_modelos)==0){
                    throw new \Exception("Error: Los cursos del usuario de la fila " . ($key + 1) . " no existen:\n[" . $string_info . "].\n Asegúrese de que los códigos de curso existan en la plataforma.");
                }
                if(sizeof($roles_modelos)==0){
                    throw new \Exception("Error: Los roles del usuario de la fila " . ($key + 1) . " no existen:\n[" . $string_info . "].\n Asegúrese de que los roles existan en la plataforma.");
                }
                $usuario_nuevo->cursos()->sync($cursos_modelos);
                $usuario_nuevo->roles()->sync($roles_modelos);
                $usuario_nuevo->save();
                array_push($users, $usuario_nuevo);
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }catch(Exception $e){
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }catch(\ValueError $e){
            return back()->with("error", "Error: Asegúrese de que los valores estén separados por punto y coma (;).");
        }
        /*try{
            foreach($users as $user){
                $user->notify(new UsuarioCreado());
            }
        }catch(\Exception $e){
            return redirect()->route('usuarios.index')->with("success", "Advertencia: Los usuarios han sido creados pero ha ocurrido un error al enviar el correo de confirmación a los usuarios creados.");
        }*/
        return redirect()->route('usuarios.index')->with("success", "Los usuarios han sido creados de manera correcta.");
    }
    public function editar(Request $request)
    {
        $id = $request->input('id') ?? $request->id;
        $user = $id ? User::find($id) : null;
        if (!$user) {
            return redirect()->route('usuarios.index')->with('error', 'El usuario solicitado no fue encontrado.');
        }
        $cursos = Cursos::all();
        $roles = Role::all();
        return view('usuarios.editar', compact('user', 'roles', 'cursos'))->with('accion', "editar");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'rut' => ['required', 'string', 'max:12', 'regex:/^[1-9]\d*\-(\d|k|K)$/', 'unique:users,rut'],
            'email' => 'required|email|max:255|unique:users,email',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'fecha_nacimiento' => 'nullable|date',
            'cursos'=>'required|array|min:1',
            'roles'=>'required|array|min:1',
        ]);
        DB::beginTransaction();
        try {
            $usuario = new User;
            $usuario->username = $request->input('username');
            $usuario->rut = $request->input('rut');
            $usuario->email = $request->input('email');
            $usuario->firstname = $request->input('firstname');
            $usuario->lastname = $request->input('lastname');
            $usuario->fecha_nacimiento = $request->input('fecha_nacimiento');
            $usuario->password = substr($request->input('rut'), 0, -2);
            $usuario->save();
            $usuario->cursos()->sync($request->input('cursos'));
            $usuario->syncRoles($request->roles);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage())->withInput();
        }
        try {
            $usuario->notify(new UsuarioCreado());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error al enviar correo de notificación para el usuario ' . $usuario->id . ': ' . $e->getMessage());
            return redirect()->route('usuarios.index')->with('success', 'El usuario "'.$usuario->username.'" ha sido creado, pero ocurrió un problema al enviar el correo de notificación.');
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario "'.$usuario->username.'" ha sido creado');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:users,id'],
            'username' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', 'regex:/^[1-9]\d*\-(\d|k|K)$/', Rule::unique('users', 'rut')->ignore($request->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($request->id)],
            'firstname' => ['required', 'string', 'max:100'],
            'lastname' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
        ]);
        $roles = isset($request->roles)? $request->roles : [];
        try {
            DB::beginTransaction();
            $usuario = User::with('roles')->find($request->id);
            $usuario->username = $request->input('username');
            $usuario->rut = $request->input('rut');
            $usuario->email = $request->input('email');
            $usuario->firstname = $request->input('firstname');
            $usuario->lastname = $request->input('lastname');
            $usuario->fecha_nacimiento = $request->input('fecha_nacimiento');
            $usuario->save();
            $usuario->cursos()->sync($request->input('cursos', []));
            $user = auth()->user();
            if($user->id == $request->id && $user->getRoleNames()->contains("administrador") && !in_array("administrador", $roles)){
                array_push($roles, "administrador");
            }
            $usuario->syncRoles($roles);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario "' . $usuario->username . '" ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:users,id',
        ]);
        if ((int) $request->id === (int) auth()->id()) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propia cuenta activa.');
        }
        try {
            DB::beginTransaction();
            $user = User::find($request->id);
            $user->delete();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario ' . $user->username . ' ha sido eliminado');
    }

    /**
     * Forcibly invalidate all active sessions for a target user (Admin action).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forzar_invalidacion_sesion(Request $request, $id)
    {
        $usuario = User::find($id);

        if (!$usuario) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $sessionService = app(\App\Services\SingleSessionService::class);
        $sessionService->forceInvalidateSession($usuario);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'La sesión del usuario "' . $usuario->username . '" ha sido finalizada de manera remota.'
            ]);
        }

        return back()->with('success', 'La sesión del usuario "' . $usuario->username . '" ha sido finalizada de manera remota.');
    }

    /**
     * Terminate all other active sessions for the current authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cerrar_otras_sesiones(Request $request)
    {
        $user = auth()->user();
        $currentSessionId = $request->session()->getId();

        $sessionService = app(\App\Services\SingleSessionService::class);
        $sessionService->forceInvalidateOtherSessions($user, $currentSessionId);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Todas tus otras sesiones activas han sido cerradas correctamente.'
            ]);
        }

        return back()->with('success', 'Todas tus otras sesiones activas en otros navegadores o dispositivos han sido cerradas.');
    }
}
