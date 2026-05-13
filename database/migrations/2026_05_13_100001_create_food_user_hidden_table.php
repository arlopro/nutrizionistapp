<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_user_hidden', function (Blueprint $table) {
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->primary(['food_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_user_hidden');
    }
};
