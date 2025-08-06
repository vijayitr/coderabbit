<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('task_call_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assignment_id')->constrained('assign_activities')->onDelete('cascade');
            $table->text('call_details');
            $table->text('notes')->nullable();
            $table->text('checklist')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('task_call_logs');
    }
};
