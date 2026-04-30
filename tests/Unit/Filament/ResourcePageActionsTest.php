<?php

use App\Filament\Resources\Audits\Pages\ListAudits;
use App\Filament\Resources\Audits\Pages\ViewAudit;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;

function invokeProtectedMethod(object $object, string $method): mixed
{
    $reflectionClass = new ReflectionClass($object);
    $reflectionMethod = $reflectionClass->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invoke($object);
}

function actionNames(object $object, string $method): array
{
    return array_map(
        fn ($action): string => $action->getName(),
        invokeProtectedMethod($object, $method),
    );
}

it('keeps order pages free of create or edit header actions', function (): void {
    expect(actionNames(new ListOrders(), 'getHeaderActions'))->toBeEmpty();
    expect(actionNames(new ViewOrder(), 'getHeaderActions'))
        ->toContain('process', 'success', 'addNote')
        ->not->toContain('fail', 'create', 'edit', 'delete');
});

it('keeps audit pages free of create or edit header actions', function (): void {
    expect(actionNames(new ListAudits(), 'getHeaderActions'))->toBeEmpty();
    expect(actionNames(new ViewAudit(), 'getHeaderActions'))->toBeEmpty();
});
