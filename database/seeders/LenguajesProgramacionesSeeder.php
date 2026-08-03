<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class LenguajesProgramacionesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lenguajes = [
            "C (GCC 7.4.0)"=> ["48", ".c", "C"],
            "C++ (GCC 7.4.0)" => ["52", ".cpp", "C++"],
            "C (GCC 8.3.0)" => ["49", ".c", "C"],
            "C++ (GCC 8.3.0)" => ["53", ".cpp", "C++"],
            "C (GCC 9.2.0)" => ["50", ".c", "C"],
            "C++ (GCC 9.2.0)" => ["54", ".cpp", "C++"],
            "C# (Mono 6.6.0.161)" => ["51", ".cs", "C#"],
            "Java (OpenJDK 13.0.1)" => ["62", ".java", "Java"],
            "Python (2.7.17)" => ["70", ".py", "py"],
            "Python (3.8.1)" => ["71", ".py", "py"],
            "SQL (SQLite 3.27.2)" => ["82", ".sql", "sql"],
        ];
        foreach($lenguajes as $key => $value){
            DB::table('lenguajes_programaciones')->insert([
                'nombre' => $key,
                'codigo' => $value[0],
                'extension' => $value[1],
                'abreviatura' => $value[2],
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
    }
}
