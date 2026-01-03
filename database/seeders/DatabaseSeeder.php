<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
        ]);

        // Buat user default jika belum ada
        if (User::count() == 0) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'phone' => '081234567890',
                'address' => 'Jl. Contoh No. 123, Kota'
            ]);
        }

        // Setelah user dibuat, baru jalankan ProductSeeder
        $this->call([
            ProductSeeder::class,
        ]);
    }
}
