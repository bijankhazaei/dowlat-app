<?php

use App\Contracts\Enums\EPaymentStates;
use App\Models\MealReservation;
use App\Models\Order;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(MealReservation::class,'meal_reservation_id')
                ->constrained('meal_reservations');
            $table->enum('status', EPaymentStates::values())->default(EPaymentStates::Unpaid->value);
            $table->unsignedBigInteger('amount');
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
