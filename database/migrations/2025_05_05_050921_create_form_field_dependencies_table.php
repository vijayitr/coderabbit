<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormFieldDependenciesTable extends Migration
{
    public function up(): void
    {
        Schema::create('form_field_dependencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('form_fields')->onDelete('cascade');
            $table->foreignId('depends_on_option_id')->constrained('form_field_options')->onDelete('cascade');
            $table->string('expected_value'); // value that triggers visibility
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_field_dependencies');
    }
}
