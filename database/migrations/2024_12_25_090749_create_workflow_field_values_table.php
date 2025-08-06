<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowFieldValuesTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_field_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')
                  ->nullable()
                  ->constrained('workflow_process_names')
                  ->onDelete('cascade');
            $table->foreignId('field_id')->constrained('workflow_fields')->onDelete('cascade');
            $table->foreignId('assignment_id')
                  ->nullable()
                  ->constrained('assign_activities')
                  ->onDelete('cascade');
            $table->string('value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_field_values');
    }
}
