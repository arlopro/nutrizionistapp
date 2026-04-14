<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_supplements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutritional_plan_id')->constrained('nutritional_plans')->cascadeOnDelete();
            $table->string('name');
            $table->string('dosage')->nullable();
            $table->string('dosage_unit', 30)->nullable();
            $table->string('timing')->nullable();
            $table->string('duration')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_supplements');
    }
};
