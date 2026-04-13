<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('index_grid_points', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('index_id');
            $table->date('price_date');
            $table->decimal('price', 18, 6);
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->timestamps();

            $table->foreign('index_id')->references('id')->on('index_definitions')->cascadeOnDelete();
            $table->foreign('entered_by')->references('id')->on('users')->nullOnDelete();
            $table->unique(['index_id', 'price_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('index_grid_points'); }
};
