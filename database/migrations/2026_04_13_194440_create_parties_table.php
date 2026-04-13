<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['Group', 'LE', 'BU'])->comment('Group=Party Group, LE=Legal Entity, BU=Business Unit');
            $table->enum('internal_external', ['Internal', 'External'])->default('External');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('short_name', 32)->unique();
            $table->string('long_name', 255);
            $table->enum('status', ['Auth Pending', 'Authorized', 'Do Not Use'])->default('Auth Pending');
            $table->unsignedInteger('version')->default(0);
            // Regulatory / compliance (Legal Entity fields)
            $table->string('lei', 20)->nullable()->comment('ISO 17442 Legal Entity Identifier');
            $table->string('bic_swift', 11)->nullable();
            $table->decimal('credit_limit', 18, 2)->nullable();
            $table->unsignedBigInteger('credit_limit_currency_id')->nullable();
            $table->enum('kyc_status', ['Pending', 'Approved', 'Expired', 'Suspended'])->nullable();
            $table->date('kyc_review_date')->nullable();
            $table->enum('regulatory_class', ['FC', 'NFC', 'NFC+', 'Third-Country'])->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('parties')->nullOnDelete();
            $table->foreign('credit_limit_currency_id')->references('id')->on('currencies')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('parties'); }
};
