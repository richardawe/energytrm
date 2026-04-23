<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stress_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stress_scenario_shocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stress_scenario_id');
            $table->unsignedBigInteger('index_id');
            $table->decimal('price_shock_pct', 8, 4);
            $table->foreign('stress_scenario_id')->references('id')->on('stress_scenarios')->cascadeOnDelete();
            $table->foreign('index_id')->references('id')->on('index_definitions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stress_scenario_shocks');
        Schema::dropIfExists('stress_scenarios');
    }
};
