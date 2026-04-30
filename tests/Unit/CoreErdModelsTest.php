<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Banner;
use App\Models\Transaction;
use App\Models\Audit;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductVariant;
use App\Models\Voucher;

test('core erd models exist', function () {
    expect(class_exists(ProductCategory::class))->toBeTrue();
    expect(class_exists(Product::class))->toBeTrue();
    expect(class_exists(ProductVariant::class))->toBeTrue();
    expect(class_exists(Order::class))->toBeTrue();
    expect(class_exists(Voucher::class))->toBeTrue();
    expect(class_exists(Banner::class))->toBeTrue();
    expect(class_exists(Transaction::class))->toBeTrue();
    expect(class_exists(Audit::class))->toBeTrue();
});

test('product category has products', function () {
    $category = ProductCategory::factory()->create();

    $product = Product::factory()->create([
        'category_id' => $category->id,
    ]);

    expect($category->products)->toHaveCount(1);
    expect($category->products->first()->is($product))->toBeTrue();
});

test('product has variants and belongs to category', function () {
    $product = Product::factory()->create();

    $variant = ProductVariant::factory()->create([
        'product_id' => $product->id,
    ]);

    expect($product->category)->toBeInstanceOf(ProductCategory::class);
    expect($product->variants)->toHaveCount(1);
    expect($product->variants->first()->is($variant))->toBeTrue();
});

test('order references variant and tracks status enum', function () {
    $order = Order::factory()->create([
        'status' => OrderStatus::PENDING,
    ]);

    expect($order->variant)->toBeInstanceOf(ProductVariant::class);
    expect($order->status)->toBe(OrderStatus::PENDING);
});

test('voucher model supports active defaults', function () {
    $voucher = Voucher::factory()->create();

    expect($voucher->is_active)->toBeTrue();
    expect($voucher->usage_count)->toBe(0);
});

test('banner uses ordering defaults', function () {
    $banner = Banner::factory()->create();

    expect($banner->sort_order)->toBe(0);
});

test('transaction tracks payment snapshot and status', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->status)->not->toBeEmpty();
    expect($transaction->payment_snapshot)->toBeArray();
});

test('audit belongs to user and stores snapshot data', function () {
    $audit = Audit::factory()->create();

    expect($audit->resource_snapshot)->toBeArray();
    expect($audit->type)->not->toBeEmpty();
});
