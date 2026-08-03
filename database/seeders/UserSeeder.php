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
        $administrador = User::create([
            'username' => 'admin',
            'rut' => '11111111-1',
            'fecha_nacimiento' => Carbon::parse('07-09-2000')->toDate(),
            'firstname' => 'Admin',
            'lastname' => 'Admin',
            'email' => 'admin@tutorbot.com',
            'password' => 'admin'
        ]);
        $administrador->assignRole('administrador');

        $cursos = Cursos::all();
        foreach($cursos as $curso){
            $administrador->cursos()->save($curso);
        }
        if(App::environment()=="local"){
            $estudiante = User::create([
                'username' => 'estudiante',
                'rut' => '22222222-2',
                'fecha_nacimiento' => Carbon::parse('07-09-2001')->toDate(),
                'firstname' => 'Estudiante',
                'lastname' => 'Estudiante',
                'email' => 'estudiante@tutorbot.com',
                'password' => 'estudiante'
            ]);
            $profesor = User::create([
                'username' => 'profesor',
                'rut' => '33333333-3',
                'fecha_nacimiento' => Carbon::parse('07-09-2002')->toDate(),
                'firstname' => 'Profesor',
                'lastname' => 'Profesor',
                'email' => 'profesor@tutorbot.com',
                'password' => 'profesor'
            ]);
            $profesor->assignRole('profesor');
            $estudiante->assignRole('estudiante');
            $curso_1 = Cursos::first();
            $profesor->cursos()->save($curso_1);
            $estudiante->cursos()->save($curso_1);
        }
    }
}
