<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('imported_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imported_by');
            $table->json('data');
            $table->boolean('status')->default(0);
            $table->timestamps();
            // Foreign Keys (if needed)
            $table->foreign('imported_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('imported_tasks');
    }
};
