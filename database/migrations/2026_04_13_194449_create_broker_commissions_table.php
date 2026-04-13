<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('broker_commissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('broker_id');
            $table->string('name', 100);
            $table->decimal('commission_rate', 10, 6);
            $table->string('rate_unit', 50)->nullable()->comment('per MT / per BBL / % of Trade Value');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->enum('payment_frequency', ['Per Trade', 'Monthly', 'Quarterly'])->default('Per Trade');
            $table->decimal('min_fee', 18, 2)->nullable();
            $table->decimal('max_fee', 18, 2)->nullable();
            $table->string('index_group', 100)->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->foreign('broker_id')->references('id')->on('brokers')->cascadeOnDelete();
            $table->foreign('currency_id')->references('id')->on('currencies')->nullOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('broker_commissions'); }
};
