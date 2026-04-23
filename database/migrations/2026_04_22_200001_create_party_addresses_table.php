<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('party_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('party_id')->constrained('parties')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->enum('address_type', ['Main', 'Backup', 'Registered', 'Billing'])->default('Main');
            $table->string('address_line1', 255);
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('country', 100);
            $table->string('phone', 50)->nullable();
            $table->string('description', 255)->nullable();
            $table->foreignId('contact_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_date')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('party_addresses'); }
};
