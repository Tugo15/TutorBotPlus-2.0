<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cursos;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $administrador = User::firstOrCreate(
            ['email' => 'admin@tutorbot.com'],
            [
                'username' => 'admin',
                'rut' => '11111111-1',
                'fecha_nacimiento' => Carbon::parse('07-09-2000')->toDate(),
                'firstname' => 'Admin',
                'lastname' => 'Admin',
                'password' => 'admin'
            ]
        );
        if (!$administrador->hasRole('administrador')) {
            $administrador->assignRole('administrador');
        }

        $cursos = Cursos::all();
        if ($cursos->count() > 0) {
            $administrador->cursos()->syncWithoutDetaching($cursos->pluck('id')->toArray());
        }

        $estudiante = User::firstOrCreate(
            ['email' => 'estudiante@tutorbot.com'],
            [
                'username' => 'estudiante',
                'rut' => '22222222-2',
                'fecha_nacimiento' => Carbon::parse('07-09-2001')->toDate(),
                'firstname' => 'Estudiante',
                'lastname' => 'Estudiante',
                'password' => 'estudiante'
            ]
        );
        if (!$estudiante->hasRole('estudiante')) {
            $estudiante->assignRole('estudiante');
        }

        $profesor = User::firstOrCreate(
            ['email' => 'profesor@tutorbot.com'],
            [
                'username' => 'profesor',
                'rut' => '33333333-3',
                'fecha_nacimiento' => Carbon::parse('07-09-2002')->toDate(),
                'firstname' => 'Profesor',
                'lastname' => 'Profesor',
                'password' => 'profesor'
            ]
        );
        if (!$profesor->hasRole('profesor')) {
            $profesor->assignRole('profesor');
        }

        if ($cursos->count() > 0) {
            $profesor->cursos()->syncWithoutDetaching($cursos->pluck('id')->toArray());
            $estudiante->cursos()->syncWithoutDetaching($cursos->pluck('id')->toArray());
        }
    }
}
