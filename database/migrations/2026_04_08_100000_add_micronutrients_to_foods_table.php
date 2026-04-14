<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->decimal('sodium_mg', 7, 1)->nullable()->after('fiber_per_100g');
            $table->decimal('potassium_mg', 7, 1)->nullable()->after('sodium_mg');
            $table->decimal('calcium_mg', 7, 1)->nullable()->after('potassium_mg');
            $table->decimal('iron_mg', 5, 1)->nullable()->after('calcium_mg');
            $table->decimal('vitamin_d_mcg', 5, 1)->nullable()->after('iron_mg');
            $table->decimal('vitamin_b12_mcg', 5, 1)->nullable()->after('vitamin_d_mcg');
            $table->unsignedSmallInteger('glycemic_index')->nullable()->after('vitamin_b12_mcg');
        });
    }

    public function down(): void
    {
        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn([
                'sodium_mg', 'potassium_mg', 'calcium_mg', 'iron_mg',
                'vitamin_d_mcg', 'vitamin_b12_mcg', 'glycemic_index',
            ]);
        });
    }
};
