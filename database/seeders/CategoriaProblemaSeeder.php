<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CategoriaProblemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            "Estructuras de Control",
            "Arreglos y Vectores",
            "Funciones y Recursión",
            "Búsqueda y Ordenamiento",
            "Consultas SQL",
            "Estructuras de Datos Avanzadas",
            "Programación Dinámica",
        ];
        foreach ($categorias as $categoria) {
            DB::table('categoria__problemas')->insert([
                'nombre' => $categoria,
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
    }
}
