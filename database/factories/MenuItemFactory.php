<?php

namespace Database\Factories;

use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition()
    {
        return [
            'kode_menu' => $this->faker->unique()->numerify('BRG##########'), // Unique kode_menu
            'category_id' => \App\Models\Categories::inRandomOrder()->first()->id, // Random existing category_id
            'name' => $this->faker->word,
            'price' => $this->faker->randomFloat(2, 10, 100),
            'diskon_persen' => $this->faker->optional()->randomFloat(2, 0, 100),
            'diskon_rupiah' => $this->faker->optional()->randomFloat(2, 0, 1000),
            'description' => $this->faker->sentence,
            'image' => $this->faker->imageUrl,
        ];
    }
}
