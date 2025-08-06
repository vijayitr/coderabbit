<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityScriptsTable extends Migration
{
    public function up(): void
    {
        Schema::create('activity_scripts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->longText('script');
            $table->timestamps();
            $table->foreign('activity_id')->references('id')->on('workflow_process_names')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_scripts');
    }
}