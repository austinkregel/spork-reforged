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
        Schema::create('deployment', function (Blueprint $table) {
            $table->id();

            $table->foreignIdFor(\App\Models\Project::class, 'project_id');
            // Name spork.zone
            $table->string('name');

            // What kind of deployment?
            $table->string('type');

            // This will be the "primary" domain
            $table->foreignIdFor(\App\Models\Domain::class, 'primary_domain_id')->nullable();
            // If there are no domains, we should set a server
            $table->foreignIdFor(\App\Models\Server::class, 'primary_server_id')->nullable();

            $table->json('deployment_strategies')->nullable();

            // austinkregel/spork
            $table->string('repository_base_url');
            $table->string('repository_status');
            // root, forge, ubuntu, etc....
            $table->string('user');

            $table->boolean('maintenance_mode')->default(true);
            // Where we clone our project to /var/www/site.domain
            $table->string('full_path', 2048);

            // Could be php 8.2.22, or ruby 1.2, or rust 1.48.1, etc
            $table->string('executable_version')->nullable();
            $table->string('executable_binary')->nullable();

            $table->boolean('secured')->nullable();

            $table->dateTime('deploy_started_at')->nullable();
            $table->dateTime('deploy_ended_at')->nullable();

            $table->dateTime('last_deployed_at')->nullable();
            $table->string('last_deployed_commit')->nullable();

            $table->unsignedInteger('deployment_duration')->nullable();


            $table->timestamps();
        });

        Schema::create('deployment_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Deployment::class, 'deployment_id');
            $table->morphs('resource');
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deployment');
        Schema::dropIfExists('deployment_resources');
    }
};
