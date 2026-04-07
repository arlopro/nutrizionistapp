<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('city');
            $table->string('website')->nullable()->after('logo');
            $table->string('instagram')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            $table->dropColumn(['logo', 'website', 'instagram']);
        });
    }
};
