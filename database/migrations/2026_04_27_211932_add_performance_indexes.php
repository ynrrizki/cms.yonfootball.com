<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // transactions: search by customer + filter/sort by status & date
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('customer_email');
            $table->index('customer_phone');
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        // orders: filter/sort by status & date
        Schema::table('orders', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['status', 'created_at']);
        });

        // products: active products per category, and active popular products for homepage
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'category_id']);
            $table->index(['is_active', 'is_popular']);
        });

        // product_variants: active variants per product (replaces scanning all variants)
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_active']);
        });

        // vouchers: validity check at checkout (active + date range)
        Schema::table('vouchers', function (Blueprint $table) {
            $table->index(['is_active', 'effective_date', 'ended_date']);
        });

        // audits: lookup by resource
        Schema::table('audits', function (Blueprint $table) {
            $table->index(['resource_type', 'resource_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['customer_email']);
            $table->dropIndex(['customer_phone']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'category_id']);
            $table->dropIndex(['is_active', 'is_popular']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'is_active']);
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'effective_date', 'ended_date']);
        });

        Schema::table('audits', function (Blueprint $table) {
            $table->dropIndex(['resource_type', 'resource_id']);
        });
    }
};
