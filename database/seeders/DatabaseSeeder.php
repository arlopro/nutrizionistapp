<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\NutritionistProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(FoodSeeder::class);

        $nutrizionista = User::create([
            'name' => 'Dr. Marco Rossi',
            'email' => 'nutrizionista@demo.it',
            'password' => Hash::make('password'),
            'phone' => '+39 333 1234567',
        ]);
        $nutrizionista->assignRole('nutritionist');
        NutritionistProfile::create([
            'user_id' => $nutrizionista->id,
            'business_name' => 'Studio Nutrizione Rossi',
            'specialization' => 'Nutrizione sportiva',
            'license_number' => 'NUT-2024-001',
            'city' => 'Roma',
            'province' => 'RM',
        ]);
    }
}
