<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user dan kategori terlebih dahulu
        if (User::count() == 0 || Category::count() == 0) {
            $this->command->error('Harap buat user dan kategori terlebih dahulu sebelum menjalankan seeder produk');
            return;
        }

        $products = [
            // Tidak ada produk dummy saat ini - kosongkan array ini
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
