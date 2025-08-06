<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskLogsTable extends Migration
{
    public function up()
    {
        Schema::create('task_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('time_entry_id')->constrained('time_entries')->onDelete('cascade');
            $table->enum('action', ['start_task', 'end_task', 'start_break', 'end_break']);
            $table->enum('status', ['active', 'on_hold'])->default('active'); // Status for task hold
            $table->text('comments')->nullable();
            $table->text('breakOption')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_logs');
    }
}

