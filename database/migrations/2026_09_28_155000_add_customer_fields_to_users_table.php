<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('phone')->nullable()->after('is_admin');
            $table->string('rut')->nullable()->after('phone');
            $table->string('account_type')->default('personal')->after('rut'); // 'personal' o 'company'
            $table->string('company_name')->nullable()->after('account_type');
            $table->string('company_rut')->nullable()->after('company_name');
            $table->string('company_giro')->nullable()->after('company_rut');
            $table->string('shipping_address')->nullable()->after('company_giro');
            $table->string('shipping_city')->nullable()->after('shipping_address');
            $table->string('shipping_region')->default('Metropolitana')->after('shipping_city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_admin',
                'phone',
                'rut',
                'account_type',
                'company_name',
                'company_rut',
                'company_giro',
                'shipping_address',
                'shipping_city',
                'shipping_region'
            ]);
        });
    }
};
