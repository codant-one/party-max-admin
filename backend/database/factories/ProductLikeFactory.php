<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductLike;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductLike>
 */
class ProductLikeFactory extends Factory
{

    protected $model = ProductLike::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        return [
            'user_id'=>rand(1,20),
            'product_id'=>rand(1,10),
            'date' => $this->randomDate(),

        ];
    }

    public function randomDate()
    {
        $start = strtotime('2023-01-01');
        $end = strtotime('2023-10-19');
        return date('Y-m-d', mt_rand($start, $end));
    }
}
