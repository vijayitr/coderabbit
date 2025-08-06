<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_call_logs', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('user_id')->nullable()->after('id'); // Adjust 'after' as needed
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade'); // Adjust 'users' and 'id' as needed


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_call_logs', function (Blueprint $table) {
            //
            $table->dropForeign(['user_id']); // Drop foreign key constraint
            $table->dropColumn('user_id'); // Drop the column
            
        });
    }
};
