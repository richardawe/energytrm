<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_warning_thresholds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('party_id');
            $table->decimal('warning_threshold_pct', 5, 2)->default(80.00);
            $table->decimal('breach_threshold_pct', 5, 2)->default(100.00);
            $table->boolean('is_active')->default(true);
            $table->foreign('party_id')->references('id')->on('parties')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_warning_thresholds');
    }
};
