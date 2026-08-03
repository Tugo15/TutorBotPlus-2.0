<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class CursosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = [
            "Taller de Programación II" => "IN1071C",
            "Taller de Programación" => "IN1045C",
            "Estructura de Datos" => "IN1069C",
            "Introducción a la Ingeniería Informática" => "IN1039C",
            "Base de Datos" => "IN1075C",
            "Taller de Base de Datos" => "IN1078C",
        ];

        foreach($cursos as $key => $value){
            DB::table('cursos')->insert([
                'nombre' => $key,
                'codigo' => $value,
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
    }
}
