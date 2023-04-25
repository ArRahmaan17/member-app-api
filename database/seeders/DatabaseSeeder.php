<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Mr Ardhi',
            'email' => 'test@example.com',
            'password' => bcrypt('loginmaman'),
            'address' => 'Jambangan, Pereng, Mojogedang, Karanganyar',
            'phone_number' => '089522983270',
            'referral_code' => str_shuffle('DANW'),
            'developer' => true,
            'administration' => true,
            'created_at' => now(),
        ]);
    }
}
