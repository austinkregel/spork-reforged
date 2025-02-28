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
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('name')->nullable()->index();
            $table->double('amount', 13, 2)->nullable();
            $table->string('account_id')->nullable()->index();

            $table->date('date')->nullable();

            $table->boolean('pending')->default(false);
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('personal_finance_category')->nullable();
            $table->string('personal_finance_category_detailed')->nullable();
            $table->string('personal_finance_icon', 2048)->nullable();
            $table->string('seller_icon', 2048)->nullable();

            $table->json('data')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable()->index();
            $table->double('amount', 13, 2)->nullable();
            $table->string('account_id')->nullable()->index();
            $table->date('date')->nullable();
            $table->boolean('pending')->default(false);
            $table->integer('category_id')->unsigned()->nullable();
            $table->string('transaction_id')->nullable()->index();
            $table->string('transaction_type')->nullable();
            $table->string('pending_transaction_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

    }
};
