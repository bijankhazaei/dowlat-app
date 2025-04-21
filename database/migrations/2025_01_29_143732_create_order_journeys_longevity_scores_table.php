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
        Schema::create('order_journeys_longevity_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(OrderJourney::class, 'order_journey_id')
                ->constrained('order_journeys')
                ->cascadeOnDelete();
            $table->foreignIdFor(OrderJourney::class, 'related_life_style_order_journey_id')
                ->nullable()
                ->constrained('order_journeys', 'id', 'fk_longevity_life_style_order_journeys');
            $table->json('metadata')->nullable();
            /**
             * {file_name:string;}|null
             */
            $table->json('requirements')->nullable();
            $table->json('parsed_data')->nullable();
            $table->json('result')->nullable();
            $table->string('error')->nullable();
            $table->timestamp('sampled_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejected_due')->nullable();
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
        Schema::dropIfExists('order_journeys_longevity_scores');
    }
};
