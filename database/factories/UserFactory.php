<?php

declare(strict_types=1);
namespace Database\Factories;
use App\Domains\Accounts\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
/** @extends Factory<User> */
class UserFactory extends Factory { protected static ?string $password=null; public function definition(): array { return ['mobile'=>'09'.$this->faker->unique()->numerify('#########'),'email'=>$this->faker->unique()->safeEmail(),'first_name'=>$this->faker->firstName(),'last_name'=>$this->faker->lastName(),'password'=>static::$password ??= Hash::make('password'),'role'=>UserRole::User,'is_active'=>true,'is_staff'=>false]; } public function unverified(): static { return $this->state(fn()=>['email_verified_at'=>null]); } }
