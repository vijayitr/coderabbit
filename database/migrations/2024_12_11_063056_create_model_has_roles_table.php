<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasRolesTable extends Migration
{
    public function up()
    {
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->bigInteger('role_id')->unsigned();
            $table->string('model_type');
            $table->bigInteger('model_id')->unsigned();

            // Foreign key constraint
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

            // Composite primary key
            $table->primary(['role_id', 'model_type', 'model_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_roles');
    }
}
