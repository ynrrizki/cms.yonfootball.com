<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('planned next orchestration tables exist', function () {
    expect(Schema::hasTable('transaction_events'))->toBeTrue();
    expect(Schema::hasTable('order_state_histories'))->toBeTrue();
    expect(Schema::hasTable('notification_deliveries'))->toBeTrue();
});

test('transaction events contain idempotency and transition columns', function () {
    expect(Schema::hasColumns('transaction_events', [
        'transaction_id',
        'provider_event_id',
        'source',
        'status_before',
        'status_after',
        'payload',
        'processed_at',
    ]))->toBeTrue();
});

test('orders can be created without processor at pending stage', function () {
    expect(Schema::hasColumn('orders', 'processed_by'))->toBeTrue();

    $columns = collect(DB::select("PRAGMA table_info('orders')"));
    $processedBy = $columns->firstWhere('name', 'processed_by');

    expect($processedBy)->not->toBeNull();
    expect((int) $processedBy->notnull)->toBe(0);
});

test('transactions allow payment method to be unknown before payer selection', function () {
    expect(Schema::hasColumn('transactions', 'payment_method'))->toBeTrue();

    $columns = collect(DB::select("PRAGMA table_info('transactions')"));
    $paymentMethod = $columns->firstWhere('name', 'payment_method');

    expect($paymentMethod)->not->toBeNull();
    expect((int) $paymentMethod->notnull)->toBe(0);
});
