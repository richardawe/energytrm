<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // user_business_units pivot
        Schema::create('user_business_units', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('party_id'); // references parties (BU type)
            $table->primary(['user_id', 'party_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('party_id')->references('id')->on('parties')->cascadeOnDelete();
        });

        // user_portfolios pivot
        Schema::create('user_portfolios', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('portfolio_id');
            $table->primary(['user_id', 'portfolio_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('portfolio_id')->references('id')->on('portfolios')->cascadeOnDelete();
        });

        // user_security_groups pivot
        Schema::create('user_security_groups', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('security_group_id');
            $table->primary(['user_id', 'security_group_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('security_group_id')->references('id')->on('security_groups')->cascadeOnDelete();
        });

        // user_trading_locations pivot — with default flag
        Schema::create('user_trading_locations', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trading_location_id');
            $table->boolean('is_default')->default(false);
            $table->primary(['user_id', 'trading_location_id']);
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('trading_location_id')->references('id')->on('trading_locations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_trading_locations');
        Schema::dropIfExists('user_security_groups');
        Schema::dropIfExists('user_portfolios');
        Schema::dropIfExists('user_business_units');
    }
};
