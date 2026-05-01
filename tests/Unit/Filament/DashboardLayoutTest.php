<?php

use App\Enums\Role;
use App\Filament\Pages\Dashboard;
use App\Models\User;
use Livewire\Livewire;

it('keeps the dashboard page focused on filament native layout', function (): void {
    $dashboardSource = file_get_contents(app_path('Filament/Pages/Dashboard.php'));

    expect($dashboardSource)
        ->not->toContain("View::make('filament.pages.dashboard-hero')")
        ->toContain('RevenueStatsOverview::class')
        ->toContain('RevenueLineChart::class')
        ->toContain('PendingOrdersWidget::class')
        ->toContain('LatestTransactionsWidget::class');
});

it('renders the filament dashboard widgets for panel users', function (): void {
    $user = User::factory()->create([
        'role' => Role::ADMIN,
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee('Dashboard');
});
