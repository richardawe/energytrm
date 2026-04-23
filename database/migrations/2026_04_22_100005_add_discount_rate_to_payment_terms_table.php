<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->decimal('discount_rate', 5, 4)->nullable()->after('days_net');
        });
    }
    public function down(): void {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->dropColumn('discount_rate');
        });
    }
};
