<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\RolePermission;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('name', '!=', 'administrador')->get()->map(function($role){
            $role->fecha = carbon::parse($role->created_at)->locale('es_ES')->isoFormat('lll');
            return $role;
        });
        return view('roles.index', compact('roles'));
    }

    public function crear(){
        $permisos = Permission::all();
        return view('roles.crear', compact('permisos'));
    }

    public function editar(Request $request){
        $rol = Role::find($request->id);
        $permisos = Permission::all();
        return view('roles.editar', compact('rol', 'permisos'));
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permisos'=> 'required|array|min:1',
        ]);
        
        db::beginTransaction();
        try{
            $rol = new Role;
            $rol->name = $request->input('name');
            $rol->save();
            $rol->syncPermissions($request->input('permisos'));
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol "'.$rol->name.'" ha sido creado');
    }

    public function update(Request $request){
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        try{
            db::beginTransaction();
            $rol = Role::find($request->id);
            $rol->name = $request->input('name');
            $rol->save();
            $rol->syncPermissions($request->input('permisos'));
            db::commit();
        }catch(\Exception $e){
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $rol = Role::find($request->id);
            $rol->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('roles.index')->with('success', 'El rol '.$rol->name.' ha sido eliminado');
    }
}
