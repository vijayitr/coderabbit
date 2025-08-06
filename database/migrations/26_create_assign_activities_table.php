<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assign_activities', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to users table (who received the assignment)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Foreign key to workflow_process_names table
            $table->foreignId('activity_id')
                ->constrained('workflow_process_names')
                ->onDelete('cascade');

            // Foreign key to users table (who assigned the task)
            $table->foreignId('assigned_by')
                ->constrained('users')
                ->onDelete('cascade');
            $table->enum('status', ['pending', 'in_progress', 'on_hold', 'completed'])->nullable();
            $table->boolean('auto_assign')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_activities');
    }
};

