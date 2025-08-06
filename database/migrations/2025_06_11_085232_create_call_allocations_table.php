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
        Schema::create('call_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_id'); // References task_call_logs.id
            $table->unsignedBigInteger('qc_agent_id'); // References users.id
            $table->unsignedBigInteger('assigned_by');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->boolean('released')->default(false);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('call_id')->references('id')->on('task_call_logs')->onDelete('cascade');
            $table->foreign('qc_agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('set null');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('call_allocations');
    }

};
