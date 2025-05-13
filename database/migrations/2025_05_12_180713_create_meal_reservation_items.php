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
        Schema::create('meal_reservation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meal_reservation_id');
            $table->unsignedBigInteger('meal_id');
            $table->date('reservation_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_reservation_items');
    }
};
