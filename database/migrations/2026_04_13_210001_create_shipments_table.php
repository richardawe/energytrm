<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number', 20)->unique(); // SHP-2026-0001
            $table->foreignId('trade_id')->constrained('trades')->cascadeOnDelete();

            // Vessel & carrier
            $table->string('vessel_name', 100)->nullable();
            $table->foreignId('carrier_id')->nullable()->constrained('parties');

            // Logistics (auto-populated from trade, overridable)
            $table->string('incoterm_code', 10)->nullable();
            $table->string('load_port', 100)->nullable();
            $table->string('discharge_port', 100)->nullable();

            // Dates
            $table->date('bl_date')->nullable();           // Bill of Lading date
            $table->date('eta_load')->nullable();
            $table->date('eta_discharge')->nullable();
            $table->date('actual_load')->nullable();
            $table->date('actual_discharge')->nullable();

            // Quantities
            $table->decimal('qty_nominated', 15, 4)->nullable();
            $table->decimal('qty_loaded', 15, 4)->nullable();
            $table->decimal('qty_discharged', 15, 4)->nullable();

            $table->enum('delivery_status', ['Scheduled', 'In Transit', 'Delivered', 'Completed', 'Cancelled'])
                  ->default('Scheduled');

            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
