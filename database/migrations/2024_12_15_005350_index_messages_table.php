<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index('thread_id');
            $table->index('event_id');
            $table->index('credential_id');
            $table->index(['thread_id', 'event_id']);
        });

        Schema::table('thread_participants', function (Blueprint $table) {
            $table->index(['thread_id', 'person_id']);
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->index('thread_id');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['thread_id']);
            $table->dropIndex(['event_id']);
            $table->dropIndex(['credential_id']);
            $table->dropIndex(['thread_id', 'event_id']);
        });

        Schema::table('thread_participants', function (Blueprint $table) {
            $table->dropIndex(['thread_id', 'person_id']);
        });

        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex(['thread_id']);
        });
    }
};
