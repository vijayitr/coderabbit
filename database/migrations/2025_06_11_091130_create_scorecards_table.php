<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_allocation_id');

            $table->integer('greeting')->nullable();
            $table->integer('request_formulation')->nullable();
            $table->enum('call_hold', ['not_followed', 'followed', 'not_applicable'])->nullable();
            $table->integer('speech_clarity')->nullable();
            $table->integer('rate_of_speech')->nullable();
            $table->boolean('dead_air')->nullable(); // true = Yes, false = No
            $table->integer('active_listening')->nullable();
            $table->enum('empathy', ['poor', 'acceptable', 'excellent'])->nullable();
            $table->integer('probing')->nullable();
            $table->enum('closure', ['non_existent', 'acceptable', 'excellent'])->nullable();

            $table->decimal('total_score', 5, 2)->nullable(); // Auto-calculated total
            $table->text('verbal_feedback')->nullable();
            $table->text('action_items')->nullable();
            $table->boolean('finalized')->default(false);
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('call_allocation_id')->references('id')->on('call_allocations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorecards');
    }
};
