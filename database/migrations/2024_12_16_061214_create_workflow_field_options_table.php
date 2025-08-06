<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowFieldOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('workflow_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_field_id')->constrained('workflow_fields')->onDelete('cascade');
            $table->string('option_value')->nullable();
            $table->unsignedInteger('option_order')->default(1)->comment('Ordering of field options');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflow_field_options');
    }
}
