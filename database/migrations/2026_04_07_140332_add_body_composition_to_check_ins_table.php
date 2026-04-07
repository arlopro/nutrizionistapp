<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->decimal('body_fat_percentage', 4, 1)->nullable()->after('weight_kg');
            $table->decimal('lean_mass_kg', 5, 1)->nullable()->after('body_fat_percentage');
            $table->decimal('body_water_percentage', 4, 1)->nullable()->after('lean_mass_kg');
            $table->decimal('skinfold_triceps', 4, 1)->nullable()->after('body_water_percentage');
            $table->decimal('skinfold_biceps', 4, 1)->nullable()->after('skinfold_triceps');
            $table->decimal('skinfold_subscapular', 4, 1)->nullable()->after('skinfold_biceps');
            $table->decimal('skinfold_suprailiac', 4, 1)->nullable()->after('skinfold_subscapular');
        });
    }

    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn([
                'body_fat_percentage',
                'lean_mass_kg',
                'body_water_percentage',
                'skinfold_triceps',
                'skinfold_biceps',
                'skinfold_subscapular',
                'skinfold_suprailiac',
            ]);
        });
    }
};
