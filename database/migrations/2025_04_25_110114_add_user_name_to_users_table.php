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
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('id')->nullable();
            $table->string('first_name')->after('username')->nullable();
            $table->string('last_name')->after('first_name')->nullable();
            $table->string('national_code')->unique()->after('last_name')->nullable();
            $table->string('phone')->after('last_name')->nullable();
            $table->string('address')->after('phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
