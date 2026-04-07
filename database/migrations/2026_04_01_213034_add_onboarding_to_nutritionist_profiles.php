<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            // Come il nutrizionista si rivolge ai clienti (usato nelle email, comunicazioni)
            $table->string('client_tone', 20)->nullable()->after('bio');
            // Durata media seduta in minuti (pre-compila gli appuntamenti)
            $table->unsignedSmallInteger('default_session_minutes')->nullable()->after('client_tone');
            // Quando viene impostato, l'onboarding non viene più mostrato
            $table->timestamp('onboarding_completed_at')->nullable()->after('default_session_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('nutritionist_profiles', function (Blueprint $table) {
            $table->dropColumn(['client_tone', 'default_session_minutes', 'onboarding_completed_at']);
        });
    }
};
