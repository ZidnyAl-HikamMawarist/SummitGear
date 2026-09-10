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
        Schema::create('penalties', function (Blueprint $table) {

            $table->id();
            $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
            $table->string('reason');
            $table->bigInteger('amount');
            $table->boolean('is_settled')->default(false);
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
