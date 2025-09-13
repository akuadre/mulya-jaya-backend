<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Palmira',
                'type' => 'fashion',
                'price' => 250000,
                'stock' => 10,
                'description' => 'Kacamata Palmira – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
            [
                'name' => 'Republik',
                'type' => 'fashion',
                'price' => 300000,
                'stock' => 10,
                'description' => 'Kacamata Republik – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
            [
                'name' => 'Rebook',
                'type' => 'fashion',
                'price' => 500000,
                'stock' => 10,
                'description' => 'Kacamata Rebook – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
            [
                'name' => 'Peach',
                'type' => 'fashion',
                'price' => 350000,
                'stock' => 10,
                'description' => 'Kacamata Peach – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
            [
                'name' => 'Tagheuer',
                'type' => 'fashion',
                'price' => 350000,
                'stock' => 10,
                'description' => 'Kacamata Tagheuer – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
            [
                'name' => 'Asian Eyewear',
                'type' => 'fashion',
                'price' => 250000,
                'stock' => 10,
                'description' => 'Kacamata Asian Eyewear – bisa untuk gaya dan kacamata ukuran',
                'image_url' => null,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
