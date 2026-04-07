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
        Schema::table('nutritional_plans', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('status');
            $table->string('template_name')->nullable()->after('is_template');
            $table->unsignedBigInteger('client_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutritional_plans', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'template_name']);
            $table->unsignedBigInteger('client_id')->nullable(false)->change();
        });
    }
};
