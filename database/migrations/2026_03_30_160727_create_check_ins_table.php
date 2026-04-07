<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->text('notes')->nullable();
            $table->tinyInteger('mood')->unsigned()->nullable();
            $table->tinyInteger('energy_level')->unsigned()->nullable();
            $table->tinyInteger('sleep_quality')->unsigned()->nullable();
            $table->decimal('water_liters', 3, 1)->nullable();
            $table->text('nutritionist_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
