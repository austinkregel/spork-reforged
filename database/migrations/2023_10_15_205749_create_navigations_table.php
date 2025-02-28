<?php

declare(strict_types=1);

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
        Schema::create('navigations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Domain::class)->nullable();
            $table->foreignId('parent_id')->nullable();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->string('href', 2048);
            $table->integer('order')->default(0);
            $table->boolean('authentication_required');
            $table->json('settings')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('pretty_url', 2048)->nullable();
            $table->string('ugly_url', 2048)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigations');
    }
};
