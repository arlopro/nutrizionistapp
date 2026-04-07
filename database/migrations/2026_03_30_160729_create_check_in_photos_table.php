<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_in_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('check_in_id')->constrained()->cascadeOnDelete();
            $table->string('photo_type', 10);
            $table->string('file_path');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_in_photos');
    }
};
