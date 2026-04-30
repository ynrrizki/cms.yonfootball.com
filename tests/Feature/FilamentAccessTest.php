<?php

use App\Models\User;
use App\Filament\Resources\ProductCategoryResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\VoucherResource;
use App\Filament\Resources\UserResource;
use function Pest\Laravel\{actingAs, get};

it('grants full access to super admin', function () {
    $user = User::factory()->create(['role' => 'SUPER_ADMIN']);

    actingAs($user)
        ->get(ProductCategoryResource::getUrl('index'))
        ->assertSuccessful();
});

it('restricts owner to read-only for catalog', function () {
    $user = User::factory()->create(['role' => 'OWNER']);

    actingAs($user)
        ->get(ProductCategoryResource::getUrl('index'))
        ->assertSuccessful();

    actingAs($user)
        ->get(ProductCategoryResource::getUrl('create'))
        ->assertForbidden();
});

it('denies admin access to catalog', function () {
    $user = User::factory()->create(['role' => 'ADMIN']);

    actingAs($user)
        ->get(ProductCategoryResource::getUrl('index'))
        ->assertForbidden();
});

it('allows all roles to access operational', function () {
    $roles = ['SUPER_ADMIN', 'OWNER', 'ADMIN'];

    foreach ($roles as $role) {
        $user = User::factory()->create(['role' => $role]);

        actingAs($user)
            ->get(OrderResource::getUrl('index'))
            ->assertSuccessful();
    }
});
