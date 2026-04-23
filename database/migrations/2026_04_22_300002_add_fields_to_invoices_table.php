<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['Commodity', 'Demurrage', 'Freight', 'Commission', 'Tax', 'Other'])
                  ->default('Commodity')->after('invoice_number');
            $table->string('invoice_reference_external', 100)->nullable()->after('invoice_type');
            $table->decimal('tax_amount', 18, 2)->nullable()->after('invoice_amount');
            $table->string('tax_code', 20)->nullable()->after('tax_amount');
            $table->enum('dispute_status', ['Undisputed', 'Disputed', 'Under Review'])
                  ->default('Undisputed')->after('invoice_status');
            $table->text('dispute_reason')->nullable()->after('dispute_status');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_type', 'invoice_reference_external',
                'tax_amount', 'tax_code',
                'dispute_status', 'dispute_reason',
            ]);
        });
    }
};
