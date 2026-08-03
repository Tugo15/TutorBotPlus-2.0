<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
class JuecesVirtualesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(env('JUDGE0_API_KEY_RAPID_API')!=null && env('JUDGE0_API_KEY_RAPID_API')!=''){
            DB::table('jueces_virtuales')->insert([
                'nombre' => 'Juez RapidApi',
                'direccion' => 'https://judge0-ce.p.rapidapi.com',
                'host' => 'judge0-ce.p.rapidapi.com',
                'api_token' => env('JUDGE0_API_KEY_RAPID_API'),
                'autenticacion' => 'x-rapid-key',
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
        if(env('JUDGE0_API_KEY_PROD')!=null && env('JUDGE0_API_KEY_PROD')!=''  && env('JUDGE0_AUTHORIZE_KEY_PROD')!=null && env('JUDGE0_AUTHORIZE_KEY_PROD')!=''){
            DB::table('jueces_virtuales')->insert([
                'nombre' => 'Juez Principal',
                'direccion' => env('JUDGE0_URL_PROD'),
                'host' => env('JUDGE0_URL_PROD'),
                'api_token' => env('JUDGE0_API_KEY_PROD'),
                'authorize' => env('JUDGE0_AUTHORIZE_KEY_PROD'),
                'autenticacion' => env('JUDGE0_AUTHENTICATION_KEY_PROD'),
                'created_at' => Carbon::now()->toDateString(),
            ]);
        }
        
    }
}
