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
        Schema::create('lenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('lense_type');
            $table->boolean('is_custom')->default(false);
            $table->decimal('left_sph', 4, 2)->nullable();
            $table->decimal('left_cyl', 4, 2)->nullable();
            $table->decimal('left_axis', 5, 2)->nullable();
            $table->decimal('right_sph', 4, 2)->nullable();
            $table->decimal('right_cyl', 4, 2)->nullable();
            $table->decimal('right_axis', 5, 2)->nullable();
            $table->decimal('pd', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lenses');
    }
};
