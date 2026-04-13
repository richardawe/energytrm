<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominations', function (Blueprint $table) {
            $table->id();
            $table->string('nomination_number', 20)->unique(); // NOM-2026-0001
            $table->foreignId('trade_id')->constrained('trades');

            $table->date('gas_day');
            $table->string('pipeline_operator', 100)->nullable();
            $table->string('delivery_point', 100)->nullable();

            $table->decimal('nominated_volume', 15, 4);
            $table->decimal('confirmed_volume', 15, 4)->nullable();
            $table->foreignId('uom_id')->constrained('uoms');

            $table->enum('nomination_status', ['Pending', 'Confirmed', 'Matched', 'Unmatched'])
                  ->default('Pending');

            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominations');
    }
};
