<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Problemas;
use App\Models\Cursos;
use App\Models\Categoria_Problema;
use App\Models\Casos_Pruebas;
use App\Models\LenguajesProgramaciones;
use Carbon\Carbon;
class ProblemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $problema = Problemas::create([
            'nombre' => 'Sumar A y B',
            'codigo' => 'suma-a-b',
            'body_problema' => 'Dado dos numeros A y B, debes entregar la suma entre A y B.',
            'habilitar_llm' => true,
            'limite_llm' => 3,
        ]);

        $cursos = Cursos::all()->pluck('id')->toArray();
        $problema->cursos()->sync($cursos);

        $lenguajes = LenguajesProgramaciones::where('codigo', '=', '71')->orWhere('codigo', '=', '52')->get()->pluck('id')->toArray();

        $problema->lenguajes()->sync($lenguajes);

        $categoria = Categoria_Problema::where('nombre', '=', 'Fácil')->get()->pluck('id')->toArray();
        $problema->categorias()->sync($categoria);

        $problema->casos_de_prueba()->createMany([
            ['entradas' => "5\n2", "salidas" => "7", "puntos" => 5],
            ["entradas" => "25\n4", "salidas" => "29", "puntos" => 10],
            ["entradas" => "3500\n932", "salidas" => "4432", "puntos" => 25],
        ]);

        $problema->puntaje_total = 45;
        $problema->save();

        $problemas = Problemas::factory()->count(35)->create();
    }
}
