<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketDetailsTable extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('user_tickets')->onDelete('cascade');
            $table->foreignId('field_id')->constrained('form_fields')->onDelete('cascade');
            $table->enum('value_type', ['select', 'text', 'checkbox','radio','date'])->default('text');
            $table->json('value'); // can be string or array
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_details');
    }
}
