<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('settlement_instructions', function (Blueprint $table) {
            $table->id();
            $table->string('si_number', 30)->unique();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->string('si_name', 150);
            $table->string('settler', 100)->nullable();
            $table->enum('status', ['Auth Pending', 'Authorized', 'Amendment Pending', 'Do Not Use'])->default('Auth Pending');
            $table->string('advice', 100)->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('account_name', 150)->nullable();
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_dvp')->default(false);
            $table->foreignId('link_settle_id')->nullable()->constrained('settlement_instructions')->nullOnDelete();
            $table->unsignedInteger('version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('settlement_instructions'); }
};
