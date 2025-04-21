<?php

use App\Contracts\Enums\EOrderStates;
use App\Models\Cart;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnUpdate();
            $table->enum('status', EOrderStates::values())->default(EOrderStates::Pending->value);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->foreignIdFor(Cart::class, 'cart_id')
                ->nullable()
                ->constrained('carts')
                ->cascadeOnUpdate();
            $table->text('address');
            $table->string('postal_code');
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
