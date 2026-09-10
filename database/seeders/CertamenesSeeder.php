<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Certamenes;
use App\Models\Cursos;
use App\Models\Categoria_Problema;
use Carbon\Carbon;

class CertamenesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cursos = Cursos::all();

        if ($cursos->isEmpty()) {
            return;
        }

        $categorias = Categoria_Problema::pluck('id')->toArray();

        foreach ($cursos as $curso) {
            $c1 = Certamenes::create([
                'nombre' => 'Certamen #1 - ' . $curso->codigo,
                'descripcion' => 'Evaluación oficial sobre fundamentos de la asignatura ' . $curso->nombre . '. Resuelva los ejercicios propuestos en el tiempo asignado.',
                'fecha_inicio' => Carbon::now()->subDays(1),
                'fecha_termino' => Carbon::now()->addDays(7),
                'penalizacion_error' => 0.25,
                'cantidad_penalizacion' => 4,
                'restriccion_red' => false,
                'ips_autorizadas' => null,
                'dificultad' => 'Fácil',
                'id_curso' => $curso->id,
                'cantidad_problemas' => count($categorias) > 0 ? count($categorias) : 2,
            ]);

            if (!empty($categorias)) {
                $c1->categorias()->sync($categorias);
            }

            $c2 = Certamenes::create([
                'nombre' => 'Certamen #2 (Red Institucional) - ' . $curso->codigo,
                'descripcion' => 'Evaluación de control acumulativo para la materia ' . $curso->nombre . '. Requiere acceso exclusivo desde la red universitaria.',
                'fecha_inicio' => Carbon::now()->subHours(2),
                'fecha_termino' => Carbon::now()->addDays(5),
                'penalizacion_error' => 0.50,
                'cantidad_penalizacion' => 3,
                'restriccion_red' => true,
                'ips_autorizadas' => '127.0.0.1, 10.0.0.0/8, 192.168.1.0/24, ::1',
                'dificultad' => 'Medio',
                'id_curso' => $curso->id,
                'cantidad_problemas' => count($categorias) > 0 ? count($categorias) : 3,
            ]);

            if (!empty($categorias)) {
                $c2->categorias()->sync($categorias);
            }
        }
    }
}
