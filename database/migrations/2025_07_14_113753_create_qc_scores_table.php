<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qc_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('call_allocation_id');
            $table->integer('score'); // total score (e.g. 62)
            $table->text('details')->nullable(); // free text
            $table->json('parameter_scores'); // e.g. {"1":2,"2":3,"3":1}
            $table->unsignedBigInteger('created_by')->nullable(); // optional
            $table->timestamps();

            // Optional foreign key constraint
            $table->foreign('call_allocation_id')
                ->references('id')
                ->on('call_allocations')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_scores');
    }
};
