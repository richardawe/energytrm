<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('short_name', 20)->nullable();
            $table->enum('broker_type', ['Voice', 'Electronic', 'Hybrid'])->default('Voice');
            $table->enum('status', ['Active', 'Suspended', 'Do Not Use'])->default('Active');
            $table->string('lei', 20)->nullable();
            $table->boolean('is_regulated')->default(false);
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('brokers'); }
};
