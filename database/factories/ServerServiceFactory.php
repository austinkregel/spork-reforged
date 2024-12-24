<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ServerService;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServerServiceFactory extends Factory
{
    protected $model = ServerService::class;

    public function definition(): array
    {
        return [
            'service' => $this->faker->word,
            'version' => $this->faker->word,
            'status' => $this->faker->word,
            'settings' => [],
        ];
    }
}
