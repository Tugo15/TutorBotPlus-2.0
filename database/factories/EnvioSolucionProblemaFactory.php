<?php

namespace Database\Factories;

use App\Models\EnvioSolucionProblema;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Problemas;
use App\Models\Cursa;
use App\Models\User;
use App\Models\Cursos;
use App\Models\Resolver;
use App\Models\JuecesVirtuales;
use App\Models\LenguajesProgramaciones;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EnvioSolucionProblemaFactory extends Factory
{

    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = EnvioSolucionProblema::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    public function definition(): array
    {
        $date_now = fake()->dateTimeBetween('-10 week', '-1 week');
        $date_termino = Carbon::parse($date_now)->addHours(fake()->numberBetween(1,5));
        $problema = Problemas::inRandomOrder()->first();
        $casos_problemas = $problema->casos_de_prueba()->count();
        $user = User::inRandomOrder()->first();
        return [
            'token' =>  Str::random(40),
            'id_problema' => $problema->id,
            'id_usuario' => $user->id,
            'id_curso' => Cursa::where('id_usuario', '=', $user->id)->inRandomOrder()->value('id_curso'),
            'id_juez' => JuecesVirtuales::inRandomOrder()->value('id'),
            'id_lenguaje' => Resolver::where('id_problema','=',$problema->id)->inRandomOrder()->value('id_lenguaje'),
            'codigo' => fake()->paragraph(3),
            'inicio' => $date_now,
            'termino' => $date_termino,
            'cant_casos_resuelto' => fake()->numberBetween(0, $casos_problemas),
            'puntaje' => fake()->numberBetween(0, $problema->puntaje_total),
            'solucionado' => fake()->randomDigit(0, 1),
        ];
    }
    
}
