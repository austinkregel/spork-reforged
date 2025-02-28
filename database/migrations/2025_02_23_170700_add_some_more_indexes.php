<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->index('last_modified');
        });
        // select * from `threads` where exists (select * from `people` inner join `thread_participants` on `people`.`id` = `thread_participants`.`person_id` where `threads`.`id` = `thread_participants`.`thread_id` and `person_id` = 2) and exists (select * from `messages` where `threads`.`id` = `messages`.`thread_id`) order by `updated_at` desc limit 15 offset 0
        // select count(*) as aggregate from `threads` where exists (select * from `people` inner join `thread_participants` on `people`.`id` = `thread_participants`.`person_id` where `threads`.`id` = `thread_participants`.`thread_id` and `person_id` = 2) and exists (select * from `messages` where `threads`.`id` = `messages`.`thread_id`)
        Schema::table('thread_participants', function (Blueprint $table) {
            $table->index('thread_id');
            $table->index('person_id');
        });
        Schema::table('people', function (Blueprint $table) {
            $table->index('primary_email');
            $table->index('primary_address');
            $table->index('emails');
        });
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->index('failed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
