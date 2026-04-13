<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('commodity_type', 50)->nullable()->comment('Oil, Gas, LNG, Power');
            $table->unsignedBigInteger('default_uom_id')->nullable();
            $table->enum('status', ['Auth Pending', 'Authorized', 'Do Not Use'])->default('Authorized');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('products'); }
};
