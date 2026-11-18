<?php

namespace Database\Factories;

use App\Models\Courier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Courier>
 */
class CourierFactory extends Factory
{
    protected $model = Courier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'KUR-'.strtoupper($this->faker->unique()->bothify('???###')),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->unique()->numerify('08##########'),
            'level' => $this->faker->numberBetween(Courier::MIN_LEVEL, Courier::MAX_LEVEL),
            'vehicle_type' => $this->faker->randomElement(Courier::VEHICLE_TYPES),
            'vehicle_plate' => strtoupper($this->faker->bothify('B #### ???')),
            'service_area' => $this->faker->city(),
            'status' => 'active',
            'registered_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
