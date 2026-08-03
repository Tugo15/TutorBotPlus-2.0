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
        dd(Storage::exists('public/examples/ejemplo_bulk_usuarios.csv'));
        return Storage::download("public/examples/ejemplo_bulk_usuarios.csv");
    }
    
    public function actualizar_informacion(Request $request){
        $validated = $request->validate([
            'username' => ['required','string', 'max:255'],
            'rut' => ['required', 'string', Rule::unique('users')->ignore($request->rut, "rut")],
            'email' => ['required', 'email', Rule::unique('users')->ignore($request->email, "email")],
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'password_actual' => ['nullable','required_with:password,password_confirmation', 'min:8'],
            'password' => ['nullable','min:8', 'required_with:password_confirmation,password_actual', 'same:password_confirmation'],
            'password_confirmation' => ['nullable','min:8'],
        ]);
        try{
        $user = User::find(auth()->user()->id);
        if(isset($request->password_actual,$request->password) && !Hash::check($request->password_actual, $user->password)){
            return back()->withInput()->with("error", "Contraseña actual incorrecta");
        }else if(isset($request->password_actual,$request->password)){
            $user->password = $request->password;
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
        return redirect()->route("ver.perfil")->with("succes", "Se ha modificado tu perfil de manera correcta.");
    }

    public function bulk_insertion(Request $request)
    {
        $validated = $request->validate([
            'csvFile' => 'required|mimes:csv,txt',
        ]);
        $contenido = file_get_contents($request->file('csvFile')->getRealPath());
        //remover caracteres especiales
        $contenido = str_replace(["\u{FEFF}", "\r"], "", $contenido);
        //dividir el contenido por el separador de break space. Lo que transforma en un array de strings, donde cada elemento es un usuario.
        $string_arrays = explode("\n", $contenido);
        $keys_array = array('username', 'firstname', 'lastname', 'email', 'rut', 'cursos', 'roles');
        $users = [];
        try {
            DB::beginTransaction();
            foreach ($string_arrays as $key=>$string_info) {
                if($string_info == ""){
                    continue;
                }
                if(str_contains($string_info,';')){
                    $usuario_data = array_filter(explode(";", $string_info));
                }else{
                    $usuario_data = array_filter(explode(",", $string_info));
                }
                if(sizeof($usuario_data)<7){
                    throw new \Exception("Faltan datos en el usuario de la columna ".($key+1).": [".$string_info."]. Ingrese nuevamente el archivo corregido y asegúrese de que todos los datos requeridos estén presentes.");
                }
                $usuario_data = array_combine($keys_array, $usuario_data);
                $usuario_data["cursos"] = array_filter(explode(",",str_replace(["[","]"], "", $usuario_data["cursos"])));
                $usuario_data["roles"] = array_filter(explode(",",str_replace(["[","]"], "", strtolower($usuario_data["roles"]))));
                $validator = Validator::make($usuario_data, [
                    'username' => ['required', 'string', 'max:255'],
                    'rut' => ['required', 'string', 'unique:App\Models\User,rut','regex:/^[1-9]\d*\-(\d|k|K)$/'],
                    'email' => ['required', 'email', 'unique:App\Models\User,email'],
                    'firstname' => ['required', 'string'],
                    'lastname' => ['required', 'string'],
                    'cursos'=> ['array', 'min:1'],
                    'roles' =>  ['array', 'min:1'],
                ]);
                if ($validator->fails()) {
                    throw new \Exception("Error en la validación del usuario de la columna ".($key+1).":\n[".$string_info."].\n ".implode(",",$validator->messages()->all()));
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
                    throw new \Exception("Error: Los cursos del usuario de la columna ".($key+1)." no existen:\n[".$string_info."].\n Asegúrese de que los cursos que se asignan existan en la plataforma.");
                }
                if(sizeof($roles_modelos)==0){
                    throw new \Exception("Error: Los roles del usuario de la columna ".($key+1)." no existen:\n[".$string_info."].\n Asegúrese de que los roles que se asigna existan en la plataforma.");
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
        $user = User::find($request->id);
        $cursos = Cursos::all();
        $roles = Role::all();
        return view('usuarios.editar', compact('user', 'roles', 'cursos'))->with('accion', "editar");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'rut' => 'required|string|unique:App\Models\User,rut',
            'email' => 'required|email|unique:App\Models\User,email',
            'firstname' => 'string',
            'lastname' => 'string',
            'fecha_nacimiento' => 'date',
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
        $usuario->notify(new UsuarioCreado());
        return redirect()->route('usuarios.index')->with('success', 'El usuario "'.$usuario->username.'" ha sido creado');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', Rule::unique('users', 'rut')->ignore($request->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->id)],
            'firstname' => ['string'],
            'lastname' => ['string'],
            'fecha_nacimiento' => ['date'],
        ]);
        $roles = isset($request->roles)? $request->roles : [];
        try {
            db::beginTransaction();
            $usuario = User::with('roles')->find($request->id);
            $usuario->username = $request->input('username');
            $usuario->rut = $request->input('rut');
            $usuario->email = $request->input('email');
            $usuario->firstname = $request->input('firstname');
            $usuario->lastname = $request->input('lastname');
            $usuario->fecha_nacimiento = $request->input('fecha_nacimiento');
            $usuario->save();
            $usuario->cursos()->sync($request->input('cursos'));
            $user = auth()->user();
            if($user->id == $request->id && $user->getRoleNames()->contains("administrador")){
                array_push($roles, "administrador");
            }
            $usuario->syncRoles($roles);
            db::commit();
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario "'.$usuario->username.'"ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = User::find($request->id);
            $user->delete();
            DB::commit();
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario ' . $user->username . ' ha sido eliminado');
    }
}
