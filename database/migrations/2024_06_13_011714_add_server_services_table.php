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
        Schema::create('server_services', function (Blueprint $table) {
            $table->id();
            $table->string('service');
            $table->foreignIdFor(\App\Models\Server::class);
            $table->string('version')->nullable();
            // running, stopped, paused, etc.
            $table->string('status')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_services');
    }
};
