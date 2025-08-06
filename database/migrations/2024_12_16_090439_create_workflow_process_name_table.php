<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowProcessNameTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_process_names', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')
                  ->constrained('workflows')
                  ->onDelete('cascade');
            $table->string('process_name');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'on_hold', 'null'])->default(null);
            $table->enum('priority', ['1', '2', '3', '4'])->default('1');
            $table->boolean('qc_enabled')->default(false);
            $table->foreignId('assigned_by')->constrained('users')->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_process_names');
    }
}
