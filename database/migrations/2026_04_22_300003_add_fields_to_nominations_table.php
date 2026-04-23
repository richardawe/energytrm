<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('nominations', function (Blueprint $table) {
            $table->enum('scheduling_window', ['Day-Ahead','Intraday','Real-Time','Within-Day'])->nullable()->after('gas_day');
            $table->decimal('counterpart_nominated_volume', 18, 4)->nullable()->after('nominated_volume');
            $table->decimal('imbalance_quantity', 18, 4)->nullable()->after('counterpart_nominated_volume');
            $table->datetime('submission_timestamp')->nullable()->after('nomination_status');
        });
    }
    public function down(): void {
        Schema::table('nominations', function (Blueprint $table) {
            $table->dropColumn(['scheduling_window','counterpart_nominated_volume','imbalance_quantity','submission_timestamp']);
        });
    }
};
