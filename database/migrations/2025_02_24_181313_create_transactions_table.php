<?php

use App\Contracts\Enums\ETransactionFeeTypes;
use App\Contracts\Enums\ETransactionStates;
use App\Models\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Payment::class, 'payment_id')->constrained('payments');
            $table->enum('status', ETransactionStates::values())->default(ETransactionStates::Init->value);
            $table->string('status_message');
            $table->unsignedBigInteger('amount');
            $table->string('provider');
            $table->string('authority')->nullable();
            $table->string('gateway_url')->nullable();
            $table->string('reference')->nullable();
            $table->string('provider_status')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->enum('fee_type', ETransactionFeeTypes::values())->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
