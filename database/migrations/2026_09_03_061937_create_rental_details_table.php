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
        Schema::create('rental_details', function (Blueprint $table) {

            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->foreignId('item_unit_id')->constrained('item_units');
            $table->bigInteger('price_per_day');
            $table->string('return_status')->nullable();
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_details');
    }
};
