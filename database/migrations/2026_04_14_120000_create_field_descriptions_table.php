<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('field_descriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tab', 64);
            $table->string('subtab', 64)->nullable();
            $table->string('field_name', 128);
            $table->string('source_type', 32)->nullable();
            $table->text('short_description')->nullable();
            $table->timestamps();
            $table->unique(['tab', 'field_name']);
        });
    }
    public function down(): void { Schema::dropIfExists('field_descriptions'); }
};
