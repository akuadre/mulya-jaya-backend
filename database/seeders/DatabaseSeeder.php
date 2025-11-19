<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(ProductSeeder::class);

        Admin::create([
            'name' => 'Mulya Jaya Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('mulyajaya'),
        ]);

        // User Seeder
        User::create([
            'name' => 'Adrenalin',
            'email' => 'adrenalin@gmail.com',
            'password' => Hash::make('adrenalin'),
            'phone_number' => '088222134661',
            'address' => 'Jl. Maleber Barat',
        ]);

        User::create([
            'name' => 'Juang Syahid',
            'email' => 'juang@gmail.com',
            'password' => Hash::make('juang'),
            'phone_number' => '081234567891',
            'address' => 'Gg. Suniaraja',
        ]);

        User::create([
            'name' => 'Evan',
            'email' => 'evan@gmail.com',
            'password' => Hash::make('evan'),
            'phone_number' => '081234567891',
            'address' => 'Perumahan Batujajar Regency',
        ]);

        // Order seeder
        // Order::create([
        //     'user_id' => 1,
        //     'product_id' => 1,
        //     'address' => 'Jl. Maleber Barat',
        //     'order_date' => Carbon::now(),
        //     'total_price' => 200000,
        //     'status' => 'completed',
        //     'payment_method' => 'bca',
        //     'lensa_type' => 'normal',
        // ]);

        // Order::create([
        //     'user_id' => 1,
        //     'product_id' => 2,
        //     'address' => 'Jl. Maleber Barat',
        //     'order_date' => Carbon::now(),
        //     'total_price' => 250000,
        //     'status' => 'processing',
        //     'payment_method' => 'bca',
        //     'lensa_type' => 'normal',
        // ]);

        // Order::create([
        //     'user_id' => 1,
        //     'product_id' => 3,
        //     'address' => 'Jl. Maleber Barat',
        //     'order_date' => Carbon::now(),
        //     'total_price' => 400000,
        //     'status' => 'processing',
        //     'payment_method' => 'mandiri',
        //     'lensa_type' => 'without',
        // ]);

        // Order::create([
        //     'user_id' => 1,
        //     'product_id' => 4,
        //     'address' => 'Jl. Maleber Barat',
        //     'order_date' => Carbon::now(),
        //     'total_price' => 300000,
        //     'status' => 'pending',
        //     'payment_method' => 'bca',
        //     'lensa_type' => 'custom',
        //     'photo' => 'images/doctorRecipes/sample.jpg', // jika ada foto
        // ]);

        // Order::create([
        //     'user_id' => 1,
        //     'product_id' => 5,
        //     'address' => 'Jl. Maleber Barat',
        //     'order_date' => Carbon::now(),
        //     'total_price' => 350000,
        //     'status' => 'pending',
        //     'payment_method' => 'mandiri',
        //     'lensa_type' => 'normal',
        // ]);
    }
}
