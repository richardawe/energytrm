<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eob_checklists', function (Blueprint $table) {
            $table->id();
            $table->date('checklist_date');
            $table->foreignId('business_unit_id')->constrained('parties');

            // Checklist items (derived from module state)
            $table->boolean('all_trades_validated')->default(false);
            $table->boolean('all_invoices_issued')->default(false);
            $table->boolean('all_settlements_confirmed')->default(false);
            $table->boolean('all_nominations_matched')->default(false);

            // Overall sign-off
            $table->boolean('signed_off')->default(false);
            $table->foreignId('signed_off_by')->nullable()->constrained('users');
            $table->timestamp('signed_off_at')->nullable();

            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['checklist_date', 'business_unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eob_checklists');
    }
};
