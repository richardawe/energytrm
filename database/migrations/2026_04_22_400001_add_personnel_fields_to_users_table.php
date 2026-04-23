<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('personnel_id', 50)->nullable()->unique()->after('id');
            $table->unsignedInteger('version')->default(0)->after('role');
            $table->enum('user_type', ['Internal', 'External', 'Licensed'])->default('Internal')->after('version');
            $table->enum('license_type', ['Full Access', 'Server', 'Read Only'])->nullable()->after('user_type');
            $table->string('short_ref_name', 32)->nullable()->after('license_type');
            $table->string('short_alias_name', 50)->nullable()->after('short_ref_name');
            $table->string('employee_id', 50)->nullable()->after('short_alias_name');
            $table->string('title', 100)->nullable()->after('employee_id');
            $table->string('phone', 50)->nullable()->after('title');
            $table->string('address', 255)->nullable()->after('phone');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('country', 100)->nullable()->after('state');
            $table->boolean('password_never_expires')->default(false)->after('country');
            $table->enum('status', ['Authorized', 'Auth Pending', 'Do Not Use'])->default('Auth Pending')->after('password_never_expires');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'personnel_id',
                'version',
                'user_type',
                'license_type',
                'short_ref_name',
                'short_alias_name',
                'employee_id',
                'title',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'password_never_expires',
                'status',
            ]);
        });
    }
};
