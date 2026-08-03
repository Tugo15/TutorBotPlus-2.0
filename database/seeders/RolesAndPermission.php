<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrador = Role::create(["name"=> "administrador"]);
        $profesor = Role::create(["name"=> "profesor"]);
        $estudiante = Role::create(["name"=> "estudiante"]);

        //Crud general
        $crudBase = ['ver', 'crear', 'editar', 'eliminar'];
        $permisos = ['rol', 'usuario', 'problemas', 'certamen', 'curso', 'lenguaje de programación', 'categoría de problema'];
        foreach ($permisos as $permiso) {
            $setPermisos = [];
            $array_permisos = [];
            foreach ($crudBase as $item){
                array_push($setPermisos, ['name'=> $item.' '.$permiso,'guard_name'=> 'web']);
                array_push($array_permisos, $item.' '.$permiso);
            }
            Permission::insert($setPermisos);
            $administrador->givePermissionTo($array_permisos);
            if(in_array($permiso, ['problemas', 'certamen', 'categoría de problema'])){
                $profesor->givePermissionTo($array_permisos);
            }
        }   

        //Permisos adicionales
        
        //Permisos de Configuración de Large Language Model
        $permisosLLM = ['configurar llm', 'limitar llm', 'activación de LLM', 'acceso al panel de administración'];
        //Permisos de Problemas
        $permisosProblemas = ['resolver problemas', 'ver listado de problemas', 'ver envios'];
        //Permisos de Certamenes
        $permisosCertamenes = ['resolver certamen', 'ver listado de certamenes'];
        //Permisos de Informes
        $permisosInformes = ['ver informe del curso', 'ver informe del problema', 'ver informe del certamen', 'ver resultados del certamen', 'ver todos los envios'];

        $crear_permisos = array_merge($permisosLLM, $permisosProblemas, $permisosCertamenes, $permisosInformes);

        $permissions = collect($crear_permisos)->map(function ($permiso) {
            return ['name' => $permiso, 'guard_name' => 'web'];
        });
        Permission::insert($permissions->toArray());

        //Añadir permisos a los roles
        $profesor->givePermissionTo($crear_permisos);
        $administrador->givePermissionTo($crear_permisos);
        $estudiante->givePermissionTo(array_merge($permisosProblemas, $permisosCertamenes));
    }
}
