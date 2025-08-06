<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowFieldsTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade'); // Foreign key to workflows table
            $table->string('field_name');
            $table->enum('field_type', ['text', 'dropdown', 'date', 'checkbox'])->default('text');
            $table->enum('is_primary', ['1', '0'])->default('0');
            $table->unsignedInteger('order')->default(1)->comment('Field ordering index');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_fields');
    }
}
