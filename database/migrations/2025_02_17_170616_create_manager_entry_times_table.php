<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManagerEntryTimesTable extends Migration
{
    public function up()
    {
        Schema::create('manager_entry_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('manage_field_id')->constrained('manage_departments')->onDelete('cascade');
            $table->foreignId('manage_field_option_id')->nullable()->constrained('manage_department_tasks')->onDelete('set null');
            $table->text('details')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manager_entry_times');
    }
}
