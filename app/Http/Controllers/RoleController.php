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
        $id = $request->input('id') ?? $request->id;
        $rol = $id ? Role::find($id) : null;
        if (!$rol || strtolower($rol->name) === 'administrador') {
            return redirect()->route('roles.index')->with('error', 'El rol solicitado no fue encontrado.');
        }
        $permisos = Permission::all();
        return view('roles.editar', compact('rol', 'permisos'));
    }

    public function store(Request $request){
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permisos'=> 'required|array|min:1',
        ]);
        
        DB::beginTransaction();
        try{
            $rol = new Role;
            $rol->name = $request->input('name');
            $rol->save();
            $rol->syncPermissions($request->input('permisos'));
            DB::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol "'.$rol->name.'" ha sido creado');
    }

    public function update(Request $request){
        $id = $request->input('id') ?? $request->id;
        $rol = $id ? Role::find($id) : null;
        if (!$rol || strtolower($rol->name) === 'administrador') {
            return redirect()->route('roles.index')->with('error', 'El rol solicitado no fue encontrado.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($rol->id)],
            'permisos' => ['required', 'array', 'min:1'],
        ]);
        try{
            DB::beginTransaction();
            $rol->name = $request->input('name');
            $rol->save();
            $rol->syncPermissions($request->input('permisos', []));
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        $id = $request->input('id') ?? $request->id;
        $rol = $id ? Role::find($id) : null;
        if (!$rol || strtolower($rol->name) === 'administrador') {
            return redirect()->route('roles.index')->with('error', 'El rol solicitado no fue encontrado o no puede ser eliminado.');
        }
        try{
            DB::beginTransaction();
            $rol->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('roles.index')->with('success', 'El rol '.$rol->name.' ha sido eliminado');
    }
}
