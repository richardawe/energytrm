<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('governing_bodies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('jurisdiction', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('governing_bodies'); }
};
