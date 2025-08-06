<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('user_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('item_id');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assignment_id')->references('id')->on('assign_activities')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('process_name_checklists')->onDelete('cascade');
        });
    }

    public function down() {
        Schema::dropIfExists('user_checklist_items');
    }
};