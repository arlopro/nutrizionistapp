<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_meal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_meal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('food_id')->nullable()->constrained('foods')->nullOnDelete();
            $table->foreignId('recipe_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantity_grams', 6, 1)->nullable();
            $table->foreignId('alternative_of')->nullable()->constrained('plan_meal_items')->nullOnDelete();
            $table->string('notes')->nullable();
            $table->tinyInteger('sort_order')->unsigned()->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_meal_items');
    }
};
