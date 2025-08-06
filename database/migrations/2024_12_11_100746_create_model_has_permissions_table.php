<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            
            // Index for quick lookups
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            
            // Foreign key constraint
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            
            // Composite primary key
            $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
        });
    }

    public function down()
    {
        Schema::dropIfExists('model_has_permissions');
    }
}