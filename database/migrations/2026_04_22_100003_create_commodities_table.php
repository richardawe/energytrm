<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commodities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->enum('commodity_group', ['Energy', 'Metal', 'Agricultural', 'Other']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('commodities'); }
};
