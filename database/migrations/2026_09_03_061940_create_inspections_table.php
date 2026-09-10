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
        Schema::create('inspections', function (Blueprint $table) {

            $table->id();
            $table->foreignId('rental_detail_id')->constrained('rental_details')->onDelete('cascade');
            $table->string('stage');
            $table->string('condition_category');
            $table->text('notes')->nullable();
            $table->timestamps();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
