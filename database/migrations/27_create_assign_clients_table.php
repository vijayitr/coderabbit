<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assign_clients', function (Blueprint $table) {
            $table->id();

            // Foreign key to users table (who is assigned the client)
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            // Foreign key to clients table
            $table->foreignId('client_id')
                ->constrained('clients')
                ->onDelete('cascade');

            // Foreign key to users table (who assigned the client)
            $table->foreignId('assigned_by')
                ->constrained('users')
                ->onDelete('cascade');

            $table->enum('status', ['pending', 'in_progress', 'on_hold', 'completed'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assign_clients');
    }
};
