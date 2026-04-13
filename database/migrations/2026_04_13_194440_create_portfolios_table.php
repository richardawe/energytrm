<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->unsignedBigInteger('business_unit_id')->nullable()->comment('FK to parties (BU type)');
            $table->boolean('is_restricted')->default(false)->comment('Restricted = specific instruments/indices allowed only');
            $table->enum('status', ['Auth Pending', 'Authorized', 'Do Not Use'])->default('Authorized');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('business_unit_id')->references('id')->on('parties')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('portfolios'); }
};
