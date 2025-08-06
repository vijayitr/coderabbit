<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkflowsTable extends Migration
{
    public function up()
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('status')->default(1)->comment('1 = Enabled, 0 = Disabled');
            $table->timestamps();
            $table->softDeletes(); // For soft deletes
        });
    }

    public function down()
    {
        Schema::dropIfExists('workflows');
    }
}
