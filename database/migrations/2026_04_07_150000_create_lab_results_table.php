<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('client_profiles')->cascadeOnDelete();
            $table->date('date');
            $table->string('lab_name')->nullable();
            $table->text('notes')->nullable();

            // Blood markers
            $table->decimal('glucose', 6, 2)->nullable();          // mg/dL
            $table->decimal('hba1c', 4, 2)->nullable();            // %
            $table->decimal('total_cholesterol', 6, 2)->nullable(); // mg/dL
            $table->decimal('hdl_cholesterol', 6, 2)->nullable();   // mg/dL
            $table->decimal('ldl_cholesterol', 6, 2)->nullable();   // mg/dL
            $table->decimal('triglycerides', 6, 2)->nullable();     // mg/dL
            $table->decimal('creatinine', 5, 2)->nullable();        // mg/dL
            $table->decimal('tsh', 5, 2)->nullable();               // mUI/L
            $table->decimal('crp', 5, 2)->nullable();               // mg/L (PCR)
            $table->decimal('zonulin', 6, 2)->nullable();           // ng/mL
            $table->decimal('calprotectin', 7, 2)->nullable();      // µg/g

            $table->timestamps();

            $table->index(['client_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
    }
};
