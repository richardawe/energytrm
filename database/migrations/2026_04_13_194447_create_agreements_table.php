<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->unsignedBigInteger('internal_party_id')->nullable();
            $table->unsignedBigInteger('counterparty_id')->nullable();
            $table->unsignedBigInteger('payment_terms_id')->nullable();
            $table->date('effective_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['Auth Pending', 'Authorized', 'Do Not Use'])->default('Authorized');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();

            $table->foreign('internal_party_id')->references('id')->on('parties')->nullOnDelete();
            $table->foreign('counterparty_id')->references('id')->on('parties')->nullOnDelete();
            $table->foreign('payment_terms_id')->references('id')->on('payment_terms')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('agreements'); }
};
