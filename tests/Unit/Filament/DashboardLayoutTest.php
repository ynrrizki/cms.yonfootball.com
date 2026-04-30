<?php

use App\Enums\Role;
use App\Filament\Pages\Dashboard;
use App\Models\User;
use Livewire\Livewire;

it('contains a custom dashboard hierarchy in the dashboard page content', function (): void {
    $dashboardSource = file_get_contents(app_path('Filament/Pages/Dashboard.php'));
    $heroView = file_get_contents(resource_path('views/filament/pages/dashboard-hero.blade.php'));

    expect($dashboardSource)->toContain("View::make('filament.pages.dashboard-hero')")
        ->toContain('Ringkasan arus pendapatan')
        ->toContain('Funnel transaksi harian')
        ->toContain('Antrian kerja yang harus disentuh hari ini')
        ->toContain('Aktivitas terbaru');

    expect($heroView)->toContain('Operational command center')
        ->toContain('Dashboard yang dibaca cepat, bukan dipelototin lama.')
        ->toContain('Lihat antrean order')
        ->toContain('Buka transaksi');
});

it('renders the custom dashboard hero for filaments users', function (): void {
    $user = User::factory()->create([
        'role' => Role::ADMIN,
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertSee('Operational command center')
        ->assertSee('Dashboard yang dibaca cepat, bukan dipelototin lama.')
        ->assertSee('Lihat antrean order')
        ->assertSee('Buka transaksi');
});
