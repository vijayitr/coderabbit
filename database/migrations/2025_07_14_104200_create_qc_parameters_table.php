<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qc_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('weight', 5, 2);
            $table->unsignedTinyInteger('order')->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Insert default parameters
        DB::table('qc_parameters')->insert([
            ['name' => 'Greetings / Call Opening', 'weight' => 0.10],
            ['name' => 'Request Formulation', 'weight' => 0.15],
            ['name' => 'Call Hold / Transfer Procedure', 'weight' => 0.15],
            ['name' => 'Speech Clarity & Pronunciation', 'weight' => 0.10],
            ['name' => 'Rate of Speech', 'weight' => 0.05],
            ['name' => 'Dead Air', 'weight' => 0.05],
            ['name' => 'Active Listening', 'weight' => 0.10],
            ['name' => 'Empathy', 'weight' => 0.10],
            ['name' => 'Probing / Assertiveness', 'weight' => 0.10],
            ['name' => 'Closure', 'weight' => 0.10],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_parameters');
    }
};
