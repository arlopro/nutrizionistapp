<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutritional_plan_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->unsigned()->nullable();
            $table->string('meal_type', 20);
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_meals');
    }
};
