<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            $table->dropColumn('default_session_minutes');
            // Durate per tipo appuntamento: { "first_visit": 60, "follow_up": 30, ... }
            $table->json('session_durations')->nullable()->after('client_tone');
        });
    }

    public function down(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            $table->dropColumn('session_durations');
            $table->unsignedSmallInteger('default_session_minutes')->nullable()->after('client_tone');
        });
    }
};
