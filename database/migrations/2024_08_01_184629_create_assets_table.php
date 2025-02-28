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
        Schema::create('assets', function (Blueprint $table) {
            $table->id()->startingValue(mt_rand(10000000, 99999999));
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('location')->nullable();
            $table->string('location_asset_id')->nullable();
            $table->string('description')->nullable();
            $table->dateTime('acquired_at')->nullable();

            $table->string('order_id', 255)->nullable();
            $table->dateTime('shipped_at')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->string('return_tracking_number', 255)->nullable();
            $table->string('tracking_number', 255)->nullable();

            $table->string('status', 255)->nullable();
            $table->string('condition')->nullable();

            $table->morphs('owner');
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
