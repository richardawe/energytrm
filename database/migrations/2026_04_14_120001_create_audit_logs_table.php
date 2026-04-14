<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 64);   // e.g. App\Models\Trade
            $table->unsignedBigInteger('auditable_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 32);            // created, updated, validated, reverted, deleted
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('audit_logs'); }
};
