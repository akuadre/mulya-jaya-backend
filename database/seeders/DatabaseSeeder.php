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

        User::create([
            'name' => 'Adrenalin',
            'email' => 'adrenalin@gmail.com',
            'password' => Hash::make('adrenalin'),
            'phone_number' => '088222134661',
            'address' => 'Jl. Maleber Barat',
        ]);

        Order::create([
            'user_id' => 1,
            'product_id' => 1,
            'address' => 'Jl. Maleber Barat',
            'order_date' => Carbon::now(),
            'total_price' => 200000,
            'status' => 'pending',
        ]);
    }
}
