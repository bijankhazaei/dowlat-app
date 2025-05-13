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
        Schema::table('meal_reservations', function (Blueprint $table) {
            $table->dropForeign('meal_reservations_meal_id_foreign');
            $table->dropColumn('meal_id');
            $table->dropColumn('reservation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meal_reservations', function (Blueprint $table) {
            $table->unsignedBigInteger('meal_id')->nullable();
            $table->date('reservation_date')->nullable();
        });
    }
};
