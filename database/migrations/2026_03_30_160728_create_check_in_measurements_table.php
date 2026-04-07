<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_in_id')->constrained()->cascadeOnDelete();
            $table->string('measurement_type', 15);
            $table->decimal('value_cm', 5, 1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_measurements');
    }
};
