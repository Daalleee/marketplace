<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;

class DailySampleSeeder extends Seeder
{
    /**
     * Run the daily sample data seeding.
     * This file can be modified daily to add new sample data.
     *
     * @return void
     */
    public function run()
    {
        // Daily sample data for maintaining GitHub contributions
        echo "Seeding daily data: " . date('Y-m-d H:i:s') . "\n";
        
        // This content can be modified daily by adding new samples or changing values
        $today = date('Y-m-d');
        
        // Sample products added daily
        $sampleProducts = [
            [
                'name' => 'Sample Product ' . rand(1000, 9999),
                'description' => 'Daily sample product added on ' . $today,
                'price' => rand(10000, 1000000),
                'condition' => ['baru', 'bekas'][rand(0, 1)],
                'stock' => rand(1, 100),
                'location' => ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar'][rand(0, 4)]
            ],
            [
                'name' => 'Demo Item ' . rand(1000, 9999),
                'description' => 'Another sample added on ' . $today,
                'price' => rand(10000, 1000000),
                'condition' => ['baru', 'bekas'][rand(0, 1)],
                'stock' => rand(1, 100),
                'location' => ['Jakarta', 'Bandung', 'Surabaya', 'Medan', 'Makassar'][rand(0, 4)]
            ]
        ];

        foreach ($sampleProducts as $productData) {
            $user = User::first(); // Get first user to assign product
            if ($user) {
                $category = Category::first(); // Get first category
                if ($category) {
                    Product::create(array_merge($productData, [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'status' => 'active'
                    ]));
                }
            }
        }
    }
}