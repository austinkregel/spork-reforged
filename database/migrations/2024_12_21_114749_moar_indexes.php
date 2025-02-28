<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index([
                'last_modified',
                'author_id',
                'author_type',
            ]);
            $table->index('headline');
        });
        Schema::table('domains', function (Blueprint $table) {
            $table->index([
                'credential_id',
                'expires_at',
            ]);
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->index([
                'credential_id',
                'type',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->index([
                'account_id',
                'date',
            ]);
        });

        Schema::table('people', function (Blueprint $table) {
            $table->index(['user_id', 'name']);
            $table->index('identifiers');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->index('name');
        });
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->index(['role_id', 'permission_id']);
        });
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->index(['model_id', 'model_type', 'permission_id']);
        });
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->index(['model_id', 'model_type', 'role_id']);
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->index(['credential_id', 'sent_at']);
        });
    }

    public function down(): void
    {

        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex([
                'last_modified',
                'author_id',
                'author_type',
            ]);
            $table->dropIndex('headline');
        });
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndex([
                'credential_id',
                'expires_at',
            ]);
        });
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropIndex([
                'credential_id',
                'type',
            ]);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex([
                'account_id',
                'date',
            ]);
        });

        Schema::table('thread_participants', function (Blueprint $table) {
            $table->dropIndex([
                'thread_id',
                'person_id',
            ]);
        });
        Schema::table('threads', function (Blueprint $table) {
            $table->dropIndex('thread_id');
        });
        Schema::table('people', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'name']);
            $table->dropIndex('identities');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex('name');
        });
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex('name');
        });
        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['role_id', 'permission_id']);
        });
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['model_id', 'model_type', 'permission_id']);
        });
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropIndex(['model_id', 'model_type', 'role_id']);
        });
        Schema::table('emails', function (Blueprint $table) {
            $table->dropIndex(['credential_id', 'sent_at']);
        });
    }
};
