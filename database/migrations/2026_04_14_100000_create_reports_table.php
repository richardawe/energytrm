<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 64);          // portfolio_analysis, pnl, counterparty_exposure, var
            $table->date('reporting_date');
            $table->string('parameters')->nullable();   // JSON-encoded filter criteria
            $table->string('file_format', 10)->default('csv');
            $table->unsignedBigInteger('generated_by');
            $table->timestamps();

            $table->foreign('generated_by')->references('id')->on('users');
        });
    }
    public function down(): void { Schema::dropIfExists('reports'); }
};
