<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManageDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('manage_departments', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->enum('field_type', ['text', 'dropdown']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manage_departments');
    }
}
