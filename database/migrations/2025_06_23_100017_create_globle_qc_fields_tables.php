<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('globle_qc_fields', function (Blueprint $table) {
            $table->id();
            $table->string('field_name');
            $table->string('field_type');
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('globle_qc_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('globle_qc_field_id')->constrained('globle_qc_fields')->onDelete('cascade');
            $table->string('option_text')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('globle_qc_field_options');
        Schema::dropIfExists('globle_qc_fields');
    }
};
