<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManageDepartmentTasksTable extends Migration
{
    public function up()
    {
        Schema::create('manage_department_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('manage_departments')->onDelete('cascade');
            $table->string('option_value');
            $table->json('extra_fields')->nullable()->after('option_value');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manage_department_tasks');
    }
}
