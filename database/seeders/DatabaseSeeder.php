<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'name' => 'Test User',
            'nomor_induk' => '12345678',
            'password' => Hash::make('password123'),
            'role' => 'mahasiswa',
            'fakultas' => 'Fakultas Ilmu Sosial & Bisnis',
            'program_studi' => 'Hubungan Internasional',
        ]);

        User::create([
            'name' => 'Admin User',
            'nomor_induk' => '87654321',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Dosen User',
            'nomor_induk' => '11223344',
            'password' => Hash::make('password123'),
            'role' => 'dosen',
        ]);
    }
}
