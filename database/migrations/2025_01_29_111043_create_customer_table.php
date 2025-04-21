<?php

use App\Contracts\Enums\EGenders;
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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('mobile')->unique();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('email')->nullable()->unique();
            $table->enum('gender', EGenders::values())->nullable();
            $table->date('birthdate')->nullable();
            $table->string('national_code')->nullable()->unique();
            $table->boolean('is_registered')->default(false);
            $table->string('password')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer');
    }
};
