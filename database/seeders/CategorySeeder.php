<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Elektronik', 'description' => 'Perangkat elektronik seperti laptop, ponsel, dll'],
            ['name' => 'Pakaian', 'description' => 'Pakaian dan aksesori'],
            ['name' => 'Furniture', 'description' => 'Perabot rumah tangga'],
            ['name' => 'Buku', 'description' => 'Buku pelajaran, novel, komik'],
            ['name' => 'Otomotif', 'description' => 'Aksesori kendaraan'],
            ['name' => 'Olahraga', 'description' => 'Perlengkapan olahraga'],
            ['name' => 'Kesehatan', 'description' => 'Perlengkapan kesehatan'],
            ['name' => 'Mainan', 'description' => 'Mainan anak dan dewasa'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
