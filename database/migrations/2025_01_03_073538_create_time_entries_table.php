<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTimeEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('process_id')->constrained('workflow_process_names')->onDelete('cascade')->nullable()->default(null);
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade')->nullable()->default(null);
            $table->string('activity')->nullable();
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assign_activities')
                  ->onDelete('cascade');
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();
            $table->enum('type', ['task', 'break', 'idle'])->default('task'); // 'task' or 'break'
            $table->enum('status', ['pending', 'in_progress', 'on_hold', 'completed'])->default('pending');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('time_entries');
    }
}

