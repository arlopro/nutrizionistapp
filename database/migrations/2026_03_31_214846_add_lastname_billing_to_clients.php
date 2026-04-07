<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('last_name')->nullable()->after('name');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->string('fiscal_code', 16)->nullable()->after('notes');
            $table->string('billing_name')->nullable()->after('fiscal_code');
            $table->string('billing_address')->nullable()->after('billing_name');
            $table->string('billing_city', 100)->nullable()->after('billing_address');
            $table->string('billing_zip', 10)->nullable()->after('billing_city');
            $table->string('billing_province', 5)->nullable()->after('billing_zip');
            $table->string('vat_number', 20)->nullable()->after('billing_province');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
        });

        Schema::table('client_profiles', function (Blueprint $table) {
            $table->dropColumn(['fiscal_code', 'billing_name', 'billing_address', 'billing_city', 'billing_zip', 'billing_province', 'vat_number']);
        });
    }
};
