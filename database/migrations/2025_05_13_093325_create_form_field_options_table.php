<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormFieldOptionsTable extends Migration
{
    public function up()
    {
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('form_field_id');
            $table->string('option');
            $table->integer('order')->nullable();
            $table->timestamps();

            $table->foreign('form_field_id')->references('id')->on('form_fields')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('form_field_options');
    }
}
