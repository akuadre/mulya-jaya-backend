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
                'name' => 'Cartier',
                'type' => 'unisex',
                'price' => 200000,
                'stock' => 1,
                'description' => 'Kacamata Cartier – elegan dan mewah, cocok untuk gaya modern.',
                'image_url' => 'Cartier.jpg',
            ],
            [
                'name' => 'Celine',
                'type' => 'unisex',
                'price' => 450000,
                'stock' => 1,
                'description' => 'Kacamata Celine – desain simple dan classy, cocok untuk segala acara.',
                'image_url' => 'celine.jpg',
            ],
            [
                'name' => 'Chanel',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Chanel – premium dan stylish dengan sentuhan elegan.',
                'image_url' => 'Chanel.jpg',
            ],
            [
                'name' => 'Dior',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Dior – modern, fashionable, dan nyaman digunakan.',
                'image_url' => 'dior.jpg',
            ],
            [
                'name' => 'Esprit',
                'type' => 'unisex',
                'price' => 600000,
                'stock' => 1,
                'description' => 'Kacamata Esprit – ringan, trendy, dan cocok untuk anak muda.',
                'image_url' => 'esprit.jpg',
            ],
            [
                'name' => 'Evolution',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata Evolution – pilihan tepat untuk gaya santai dan formal.',
                'image_url' => 'Evolution.jpg',
            ],
            [
                'name' => 'Fendi',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata Fendi – fashion premium dengan kualitas tinggi.',
                'image_url' => 'fendi.jpg',
            ],
            [
                'name' => 'FreeHouse',
                'type' => 'unisex',
                'price' => 200000,
                'stock' => 1,
                'description' => 'Kacamata FreeHouse – simple, ringan, dan cocok dipakai sehari-hari.',
                'image_url' => 'FreeHouse.jpg',
            ],
            [
                'name' => 'Glory',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Glory – desain kekinian untuk gaya anak muda.',
                'image_url' => 'Glory.jpg',
            ],
            [
                'name' => 'Goobgn',
                'type' => 'unisex',
                'price' => 200000,
                'stock' => 1,
                'description' => 'Kacamata Goobgn – trendy dan ringan, nyaman dipakai lama.',
                'image_url' => 'Goobgn.jpg',
            ],
            [
                'name' => 'Gucci',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Gucci – high class fashion eyewear.',
                'image_url' => 'Gucci.jpg',
            ],
            [
                'name' => 'Marc Jacobs',
                'type' => 'unisex',
                'price' => 200000,
                'stock' => 1,
                'description' => 'Kacamata Jacobs – simple tapi tetap stylish.',
                'image_url' => 'Jacobs.jpg',
            ],
            [
                'name' => 'Jessica',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata Jessica – ringan dengan desain kekinian.',
                'image_url' => 'Jessica.jpg',
            ],
            [
                'name' => 'Looneytoons',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Looneytoons – playful dan cocok untuk anak muda.',
                'image_url' => 'looneytoons.jpg',
            ],
            [
                'name' => 'MJ Series 01',
                'type' => 'unisex',
                'price' => 210000,
                'stock' => 1,
                'description' => 'Kacamata MJ Series 01 – desain unik dengan gaya modern.',
                'image_url' => 'MJSeries01.jpg',
            ],
            [
                'name' => 'Moskow',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata Moskow – simple dan elegan untuk semua kesempatan.',
                'image_url' => 'Moskow.jpg',
            ],
            [
                'name' => 'Police',
                'type' => 'unisex',
                'price' => 300000,
                'stock' => 1,
                'description' => 'Kacamata Police – keren dan maskulin dengan kualitas tinggi.',
                'image_url' => 'Police.jpg',
            ],
            [
                'name' => 'Ray-Ban',
                'type' => 'unisex',
                'price' => 350000,
                'stock' => 1,
                'description' => 'Kacamata Ray-Ban – klasik, populer, dan timeless.',
                'image_url' => 'ray-ban.jpg',
            ],
            [
                'name' => 'Station',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata Station – desain praktis untuk sehari-hari.',
                'image_url' => 'Station.jpg',
            ],
            [
                'name' => 'WhiteLotus',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata WhiteLotus – stylish dan elegan.',
                'image_url' => 'WhiteLotus.jpg',
            ],
            [
                'name' => 'YouthTrend',
                'type' => 'unisex',
                'price' => 250000,
                'stock' => 1,
                'description' => 'Kacamata YouthTrend – cocok untuk anak muda yang ingin tampil trendy.',
                'image_url' => 'YouthTrend.jpg',
            ],
            [
                'name' => 'Zero',
                'type' => 'unisex',
                'price' => 200000,
                'stock' => 1,
                'description' => 'Kacamata Zero – desain minimalis, simple, dan elegan.',
                'image_url' => 'Zero.jpg',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
