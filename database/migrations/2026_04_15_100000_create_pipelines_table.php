<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 100);
            $table->string('commodity_type', 50)->nullable()->comment('Oil, Gas, LNG, Power');
            $table->string('operator', 100)->nullable();
            $table->string('country', 50)->nullable();
            $table->enum('status', ['Authorized', 'Do Not Use'])->default('Authorized');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });

        Schema::create('pipeline_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();
            $table->string('zone_code', 20);
            $table->string('zone_name', 100);
            $table->enum('status', ['Authorized', 'Do Not Use'])->default('Authorized');
            $table->timestamps();
            $table->unique(['pipeline_id', 'zone_code']);
        });

        Schema::create('pipeline_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zone_id')->constrained('pipeline_zones')->cascadeOnDelete();
            $table->string('location_code', 30);
            $table->string('location_name', 100);
            $table->enum('location_type', ['Receipt', 'Delivery', 'Both'])->default('Both');
            $table->enum('status', ['Authorized', 'Do Not Use'])->default('Authorized');
            $table->timestamps();
            $table->unique(['zone_id', 'location_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_locations');
        Schema::dropIfExists('pipeline_zones');
        Schema::dropIfExists('pipelines');
    }
};
