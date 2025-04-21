<?php

use App\Models\OrderJourney;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_journeys_life_style_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderJourney::class, 'order_journey_id')
                ->constrained('order_journeys')
                ->cascadeOnDelete();
            $table->json('metadata')->nullable();
            /**
             * {form_id:string; response_id:string}|null
             */
            $table->json('requirements')->nullable();
            $table->json('parsed_data')->nullable();
            $table->json('result')->nullable();
            $table->string('error')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('parsed_at')->nullable();
            $table->timestamp('enqueued_at')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_journeys_life_style_scores');
    }
};
