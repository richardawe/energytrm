<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 50)->unique();
            $table->string('account_name', 150);
            $table->enum('account_type', ['Nostro', 'Internal Nostro', 'Vostro', 'Internal Vostro', 'Margin', 'Other'])->default('Nostro');
            $table->foreignId('holding_party_id')->nullable()->constrained('parties')->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->nullOnDelete();
            $table->enum('status', ['Authorized', 'Auth Pending', 'Do Not Use', 'Amendment Pending'])->default('Auth Pending');
            $table->string('class', 100)->nullable();
            $table->text('description')->nullable();
            $table->boolean('on_balance_sheet')->default(true);
            $table->boolean('allow_multiple_units')->default(false);
            $table->string('account_legal_name', 200)->nullable();
            $table->string('country', 100)->nullable();
            $table->date('date_opened')->nullable();
            $table->date('date_closed')->nullable();
            $table->string('general_ledger_account', 100)->nullable();
            $table->boolean('sweep_enabled')->default(false);
            $table->unsignedInteger('version')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('accounts'); }
};
