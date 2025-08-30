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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', [
                'minus',        // kacamata minus (myopia)
                'plus',         // kacamata plus (hypermetropia)
                'silinder',     // astigmatisme
                'bifokal',      // gabungan (biasanya plus + minus)
                'progressive',  // lensa bertahap, modern
                'photocromic',  // lensa yang berubah gelap kalau kena cahaya
                'fashion'       // kacamata gaya / tanpa minus
            ]);
            $table->unsignedBigInteger('price');
            $table->integer('stock');
            $table->text('description')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
