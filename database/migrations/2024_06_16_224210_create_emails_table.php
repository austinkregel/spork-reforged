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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Credential::class);
            $table->string('email_id', 255)->nullable();

            $table->string('to', 255)->nullable();
            $table->string('to_email', 255)->nullable();
            $table->string('from', 255)->nullable();
            $table->string('from_email', 255)->nullable();

            $table->string('subject', 255);
            $table->timestamp('sent_at');

            $table->boolean('seen')->default(false);
            $table->boolean('spam')->default(false);
            $table->boolean('answered')->default(false);

            $table->longText('message');
            $table->timestamps();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('from_email');
            $table->dropColumn('to_email');
            $table->dropColumn('answered');
            $table->dropColumn('subject');
            $table->dropColumn('seen');
            $table->dropColumn('spam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');

        Schema::table('messages', function (Blueprint $table) {
            $table->string('to_email', 255)->nullable()->after('from_person');
            $table->string('from_email', 255)->nullable()->after('from_person');
            $table->string('subject', 255)->nullable();
            $table->boolean('seen')->default(false);
            $table->boolean('spam')->default(false);
            $table->boolean('answered')->default(false);
        });
    }
};
