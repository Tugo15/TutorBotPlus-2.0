<?php

namespace Database\Factories;

use App\Models\Problemas;
use App\Models\Categoria_Problema;
use App\Models\Cursos;
use App\Models\LenguajesProgramaciones;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Problemas>
 */
class ProblemasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nombre = fake()->sentence(5);
        return [
            'nombre' => $nombre,
            'codigo' => str_replace(' ', '-', $nombre),
            'body_problema' => fake()->text(500),
            'memoria_limite'=>fake()->numberBetween(5500, 8000),
            'tiempo_limite'=>fake()->numberBetween(1,5),
            'visible'=>fake()->boolean(),
            'habilitar_llm'=>fake()->boolean(),
            'limite_llm'=>fake()->numberBetween(0,5),
            'body_editorial'=>fake()->text(500)
        ];
    }

     /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Problemas $problema) {
            $categorias = Categoria_Problema::inRandomOrder()->limit(fake()->numberBetween(1,3))->get();
            $cursos = Cursos::inRandomOrder()->limit(fake()->numberBetween(1,3))->get();
            $lenguajes = LenguajesProgramaciones::where('nombre','LIKE', '%python%')->get();
            $problema->categorias()->sync($categorias);
            $problema->cursos()->sync($cursos);
            $problema->lenguajes()->sync($lenguajes);
            $problema->casos_de_prueba()->createMany([
                ['entradas' => "5\n2", "salidas" => "7", "puntos" => 5],
                ["entradas" => "25\n4", "salidas" => "29", "puntos" => 10],
                ["entradas" => "3500\n932", "salidas" => "4432", "puntos" => 25],
            ]);
        });
    }
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Problemas::class;
}
