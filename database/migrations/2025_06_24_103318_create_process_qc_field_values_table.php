<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProcessQcFieldValuesTable extends Migration
{
    public function up()
    {
        Schema::create('process_qc_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                  ->nullable()
                  ->constrained('workflow_process_names')
                  ->onDelete('cascade');
            // $table->foreignId('field_id')->constrained('workflow_qc_fields')->onDelete('cascade');
            $table->foreignId('workflow_qc_field_id')
              ->nullable()
              ->constrained('workflow_qc_fields')
              ->onDelete('cascade');

            $table->foreignId('globle_qc_field_id')
              ->nullable()
              ->constrained('globle_qc_fields')
              ->onDelete('cascade');
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assign_activities')
                  ->onDelete('cascade');
            $table->string('value')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'on_hold', 'completed'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('process_qc_field_values');
    }
}
