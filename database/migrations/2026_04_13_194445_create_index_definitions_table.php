<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('index_definitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('version')->default(0);
            $table->string('index_name', 100);
            $table->string('market', 100)->nullable()->comment('E.g. Crude Oil, Natural Gas, Power');
            $table->string('index_group', 100)->nullable();
            $table->enum('format', ['Daily', 'Monthly', 'Quarterly', 'Annual'])->default('Monthly');
            $table->string('class', 50)->nullable()->comment('Energy, Metal, Agricultural');
            $table->unsignedBigInteger('base_currency_id')->nullable();
            $table->unsignedBigInteger('uom_id')->nullable();
            $table->enum('status', ['Custom', 'Official', 'Template'])->default('Custom');
            $table->enum('rec_status', ['Auth Pending', 'Authorized', 'Do Not Use'])->default('Authorized');
            $table->timestamps();

            $table->foreign('base_currency_id')->references('id')->on('currencies')->nullOnDelete();
            $table->foreign('uom_id')->references('id')->on('uoms')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('index_definitions'); }
};
