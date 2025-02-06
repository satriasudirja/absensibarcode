<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'nip' => '123456789',
            'name' => 'Nadya Putri',
            'email' => 'nadyaputripurnama@gmail.com',
            'phone' => '081234567890',
            'gender' => 'Male',
            'birth_date' => '1990-05-15',
            'birth_place' => 'Jakarta',
            'address' => 'Jl. Merdeka No. 10',
            'city' => 'Jakarta',
            'education_id' => 1,
            'division_id' => 2,
            'job_title_id' => 3,
            'password' => Hash::make('password12345'),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'raw_password' => 'password123',
            'group' => 'user',
            'email_verified_at' => Carbon::now(),
            'profile_photo_path' => null,
            'remember_token' => Str::random(10),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Membuat 10 user random dengan Faker
        \App\Models\User::factory(10)->create([
            'group' => 'user'
        ]);
    }
}
