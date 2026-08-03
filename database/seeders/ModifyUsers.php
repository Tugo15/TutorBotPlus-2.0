<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class ModifyUsers extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        try{
        DB::beginTransaction();
        $usuarios = User::whereDate('created_at', Carbon::parse('2024-11-05 11:23:18'))->get();
        $rut_usado = [];
        foreach($usuarios as $usuario){
            $usuario->firstname = $faker->firstName();
            $usuario->lastname = $faker->lastName();
            $usuario->rut = strval($faker->numberBetween(30,80)).strval($faker->numberBetween(100,955)).strval($faker->numberBetween(100,955))."-".strval($faker->randomDigit());
            while(in_array($usuario->rut, $rut_usado)){
                $usuario->rut = strval($faker->numberBetween(17,23)).strval($faker->numberBetween(100,955)).strval($faker->numberBetween(100,955))."-".strval($faker->randomDigit());
            }
            array_push($rut_usado, $usuario->rut);
            $usuario->save();
            echo $usuario->firstname." ".$usuario->lastname." - RUT: ".$usuario->rut."\n";
        }
        DB::commit();
        }catch(\PDOException $e){
            DB::rollback();
            echo $e->getMessage();
        }
    }
}
