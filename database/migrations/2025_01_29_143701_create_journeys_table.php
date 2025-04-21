<?php

use App\Contracts\Enums\EJourneyTypes;
use App\Contracts\Enums\JourneySlug;
use App\Models\Journey;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journeys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('slug', JourneySlug::values());
            $table->enum('type', EJourneyTypes::values());
            $table->unsignedBigInteger('price');
            $table->integer('interval')->nullable();
            $table->integer('re_buy_interval');
            $table->boolean('is_active')->default(true);
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->json('extra')->nullable();
            $table->foreignIdFor(Journey::class, 'dependency_id')
                ->nullable()->constrained('journeys');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journeys');
    }
};
