<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('nutritionist_id')->constrained('users')->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('initial_weight_kg', 5, 1)->nullable();
            $table->string('activity_level', 20)->nullable();
            $table->json('allergies')->nullable();
            $table->json('intolerances')->nullable();
            $table->text('pathologies')->nullable();
            $table->text('dietary_preferences')->nullable();
            $table->string('goal', 20)->nullable();
            $table->string('status', 10)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
