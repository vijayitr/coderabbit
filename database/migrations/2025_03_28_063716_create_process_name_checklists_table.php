<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('process_name_checklists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('process_id');
            $table->string('item');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('process_id')->references('id')->on('workflow_process_names')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('process_name_checklists');
    }
};
