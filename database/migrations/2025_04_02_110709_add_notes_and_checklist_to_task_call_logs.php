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
            $table->text('notes')->nullable()->after('call_details'); // Change 'existing_column' to the column after which you want to add 'notes'
            $table->text('checklist')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_call_logs', function (Blueprint $table) {
            $table->dropColumn(['notes', 'checklist']);

        });
    }
};
