<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        
        $this->call([
            CursosSeeder::class,
            RolesAndPermission::class,
            UserSeeder::class,
            LenguajesProgramacionesSeeder::class,
            CategoriaProblemaSeeder::class,
            JuecesVirtualesSeeder::class,
        ]);
        if (App::environment('local')) {
            $this->call([
                ProblemasSeeder::class,
            ]);
        }
    }
}
