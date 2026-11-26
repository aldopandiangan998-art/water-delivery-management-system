<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find superadmin user by email or ID 1
        $superadminUser = DB::table('users')
            ->where('email', 'superadmin@aquaflow.com')
            ->orWhere('email', 'owner@aquaflow.com')
            ->orWhere('id', 1)
            ->first();

        if ($superadminUser) {
            // Delete any transactions/movements created by superadmin
            DB::table('stock_movements')->where('created_by', $superadminUser->id)->delete();
            DB::table('customer_profiles')->where('user_id', $superadminUser->id)->delete();
            DB::table('customer_addresses')->where('user_id', $superadminUser->id)->delete();
            DB::table('orders')->where('user_id', $superadminUser->id)->delete();
            DB::table('reviews')->where('user_id', $superadminUser->id)->delete();
            DB::table('sessions')->where('user_id', $superadminUser->id)->delete();

            // Delete the superadmin user record
            DB::table('users')->where('id', $superadminUser->id)->delete();
        }

        // 2. Clean up any other users that might have role super_admin or superadmin
        $otherSuperadmins = DB::table('users')
            ->whereIn('role', ['super_admin', 'superadmin'])
            ->get();

        foreach ($otherSuperadmins as $u) {
            DB::table('stock_movements')->where('created_by', $u->id)->delete();
            DB::table('customer_profiles')->where('user_id', $u->id)->delete();
            DB::table('customer_addresses')->where('user_id', $u->id)->delete();
            DB::table('orders')->where('user_id', $u->id)->delete();
            DB::table('reviews')->where('user_id', $u->id)->delete();
            DB::table('sessions')->where('user_id', $u->id)->delete();
            DB::table('users')->where('id', $u->id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data cleanup is permanent
    }
};
