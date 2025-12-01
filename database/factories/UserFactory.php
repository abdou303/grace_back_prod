<?php

namespace Database\Factories;

use App\Models\Groupe;
use App\Models\Partenaire;
use App\Models\Role;
use App\Models\Tribunal;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $groupeIds = Groupe::pluck('id');
        $roleIds = Role::pluck('id');
        $partenaireIds = Partenaire::pluck('id');
        $tribunalIds = Tribunal::pluck('id');

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'username' => $this->faker->unique()->userName, // Generate a unique username
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => fake()->randomElement($roleIds),
            //'partenaire_id' => fake()->randomElement($partenaireIds),
           //'tribunal_id' => fake()->randomElement($tribunalIds),
            'groupe_id' => fake()->randomElement($groupeIds)

        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
