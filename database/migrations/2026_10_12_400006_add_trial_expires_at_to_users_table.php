<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('trial_expires_at')->nullable()->after('status')->index();
        });

        DB::table('security_groups')->insertOrIgnore([
            'name'        => 'Trial Access',
            'description' => 'Self-service 1-hour trial accounts. Trader-level access, no downloads or exports; expires and prompts registration.',
            'is_active'   => true,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('security_groups')->where('name', 'Trial Access')->delete();

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['trial_expires_at']);
            $table->dropColumn('trial_expires_at');
        });
    }
};
