<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->text('address');
            $table->dateTime('order_date');
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending', 'processing', 'sending', 'completed', 'cancelled'])->default('pending');
            $table->string('photo')->nullable();
            $table->enum('payment_method', [
                'bca', 'mandiri',
                // 'gopay', 'ovo', 'dana', 'shopeepay', 'qris',
            ]);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
