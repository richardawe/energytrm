<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('var_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedInteger('lookback_period_days')->default(250);
            $table->enum('holding_period_days', ['1', '10'])->default('1');
            $table->enum('var_method', ['Historical Simulation', 'Parametric', 'Monte Carlo'])->default('Historical Simulation');
            $table->decimal('confidence_level', 5, 4)->default(0.9900);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('var_configurations');
    }
};
