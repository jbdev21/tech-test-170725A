<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'Yoga Class',
                'Pilates Class',
                'Meditation Class',
                'Reiki Healing',
                'Swimming Lesson',
                'Cooking Class',
                'Dance Class',
                'Guitar Lesson',
                'Piano Lesson',
                'Haircut',
                'Massage',
                'Facial',
                'Manicure',
                'Pedicure',
                'Spa Day',
                'Yoga Retreat',
            ]),
        ];
    }
}
