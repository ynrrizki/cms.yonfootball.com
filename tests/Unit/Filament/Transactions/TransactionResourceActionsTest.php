<?php

use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Pages\ViewTransaction;

function invokeProtectedTransactionMethod(object $object, string $method): mixed
{
    $reflectionClass = new ReflectionClass($object);
    $reflectionMethod = $reflectionClass->getMethod($method);
    $reflectionMethod->setAccessible(true);

    return $reflectionMethod->invoke($object);
}

it('does not expose create or edit header actions for transactions', function (): void {
    $listActions = invokeProtectedTransactionMethod(new ListTransactions(), 'getHeaderActions');
    $viewActions = invokeProtectedTransactionMethod(new ViewTransaction(), 'getHeaderActions');

    expect($listActions)->toBeEmpty();
    expect($viewActions)->toBeEmpty();
});
