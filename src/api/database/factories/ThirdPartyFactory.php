<?php

namespace Database\Factories;

use App\Models\ThirdParty;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThirdPartyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ThirdParty::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
