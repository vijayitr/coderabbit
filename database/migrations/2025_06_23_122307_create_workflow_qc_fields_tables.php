<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflow_qc_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('workflow_process_names')->onDelete('cascade');
            $table->string('field_name');
            $table->string('field_type');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('workflow_qc_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_qc_field_id')->constrained('workflow_qc_fields')->onDelete('cascade');
            $table->string('option_text')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_qc_field_options');
        Schema::dropIfExists('workflow_qc_fields');
    }
};
