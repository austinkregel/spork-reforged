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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            // This is the full address
            $table->string('address');
            // This is the city
            $table->string('city')->nullable();
            // This is the state
            $table->string('state')->nullable();

            $table->float('latitude', 10, 6)->nullable();
            $table->float('longitude', 10, 6)->nullable();

            // Want to know when that location opened.
            $table->dateTime('opened_at')->nullable();
            // Want to know if and when that location closed.
            $table->dateTime('closed_at')->nullable();

            $table->string('category')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
