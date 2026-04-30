@php
    use App\Enums\OrderStatus;
    use App\Enums\TransactionStatus;
    use App\Models\Order;
    use App\Models\Transaction;
    use Illuminate\Support\Facades\DB;

    $today = today();
    $monthStart = now()->startOfMonth();

    $ordersToday = Order::whereDate('created_at', $today)->count();
    $ordersPending = Order::where('status', OrderStatus::PENDING)->count();
    $ordersProcessing = Order::where('status', OrderStatus::PROCESSING)->count();
    $ordersSuccess = Order::where('status', OrderStatus::SUCCESS)->count();
    $backlogOrders = Order::where('status', OrderStatus::PENDING)
        ->whereHas('transaction', fn ($query) => $query->where('status', TransactionStatus::PAID))
        ->count();

    $paidTransactions = Transaction::where('status', TransactionStatus::PAID)->count();
    $pendingTransactions = Transaction::where('status', TransactionStatus::PENDING)->count();
    $failedTransactions = Transaction::whereIn('status', [TransactionStatus::CANCELLED, TransactionStatus::REFUNDED])->count();

    $revenueToday = DB::table('transactions')
        ->join('orders', 'transactions.order_id', '=', 'orders.id')
        ->join('product_variants', 'orders.product_variant_id', '=', 'product_variants.id')
        ->where('transactions.status', TransactionStatus::PAID->value)
        ->whereDate('transactions.paid_at', $today)
        ->sum('product_variants.price');

    $revenueMonth = DB::table('transactions')
        ->join('orders', 'transactions.order_id', '=', 'orders.id')
        ->join('product_variants', 'orders.product_variant_id', '=', 'product_variants.id')
        ->where('transactions.status', TransactionStatus::PAID->value)
        ->where('transactions.paid_at', '>=', $monthStart)
        ->sum('product_variants.price');

    $averageOrderValue = $paidTransactions > 0 ? (int) round($revenueMonth / $paidTransactions) : 0;
    $completionRate = Order::count() > 0 ? (int) round(($ordersSuccess / Order::count()) * 100) : 0;

    $ordersUrl = '/admin/orders';
    $transactionsUrl = '/admin/transactions';
@endphp

<div class="space-y-6">
    <section class="relative overflow-hidden rounded-4xl border border-white/10 bg-slate-950/90 p-8 shadow-[0_24px_80px_rgba(0,0,0,0.35)]">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,158,11,0.10),transparent_30%),radial-gradient(circle_at_bottom_left,rgba(59,130,246,0.10),transparent_32%)]"></div>

        <div class="relative grid gap-8 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="space-y-6">
                <div class="space-y-3">
                    <span class="inline-flex items-center rounded-full border border-amber-400/20 bg-amber-400/10 px-4 py-1.5 text-[10px] font-bold uppercase tracking-[0.35em] text-amber-300">
                        Owner Overview
                    </span>
                    <h1 class="max-w-3xl text-4xl font-semibold tracking-tight text-white md:text-5xl">
                        Dashboard yang langsung menjawab: <span class="text-amber-400">uang masuk</span>, <span class="text-emerald-400">order bergerak</span>, dan <span class="text-blue-400">risiko yang harus dibereskan</span>.
                    </h1>
                </div>

                <p class="max-w-2xl text-base leading-7 text-slate-400">
                    Ini bukan dashboard teknis. Ini ringkasan eksekutif untuk owner supaya tahu apa yang sudah menghasilkan, apa yang macet, dan apa yang harus diputuskan hari ini.
                </p>

                <div class="flex flex-wrap gap-3">
                    <x-filament::button
                        tag="a"
                        href="{{ $ordersUrl }}"
                        color="warning"
                        size="lg"
                        icon="heroicon-m-shopping-bag"
                        class="shadow-lg shadow-amber-500/20"
                    >
                        Buka Order Pending ({{ $ordersPending }})
                    </x-filament::button>

                    <x-filament::button
                        tag="a"
                        href="{{ $transactionsUrl }}"
                        color="gray"
                        size="lg"
                        outlined
                        icon="heroicon-m-arrow-trending-up"
                    >
                        Review Transaksi ({{ $pendingTransactions }})
                    </x-filament::button>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-300/70">Fokus Hari Ini</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Selesaikan backlog yang sudah dibayar sebelum memikirkan ekspansi.</p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-emerald-300/70">Arah Bisnis</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Naikkan order selesai, kurangi pending, jaga cash-in tetap konsisten.</p>
                    </div>

                    <div class="rounded-3xl border border-white/10 bg-white/5 p-4">
                        <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-blue-300/70">Risiko</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Order dibayar tapi belum diproses adalah kebocoran trust yang paling mahal.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-3xl border border-amber-500/20 bg-linear-to-br from-amber-500/10 to-orange-500/5 p-6 backdrop-blur-md">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-300/70">Revenue Hari Ini</p>
                    <p class="mt-3 text-3xl font-semibold text-white">Rp {{ number_format($revenueToday, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-400">Uang yang sudah tervalidasi masuk hari ini.</p>
                </div>

                <div class="rounded-3xl border border-blue-500/20 bg-linear-to-br from-blue-500/10 to-cyan-500/5 p-6 backdrop-blur-md">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-blue-300/70">Revenue Bulan Ini</p>
                    <p class="mt-3 text-3xl font-semibold text-white">Rp {{ number_format($revenueMonth, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-400">Total kas terkonfirmasi untuk periode berjalan.</p>
                </div>

                <div class="rounded-3xl border border-emerald-500/20 bg-linear-to-br from-emerald-500/10 to-teal-500/5 p-6 backdrop-blur-md">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-emerald-300/70">Order Pending</p>
                    <p class="mt-3 text-3xl font-semibold text-white">{{ $ordersPending }}</p>
                    <p class="mt-2 text-sm text-slate-400">Butuh perhatian operasional.</p>
                </div>

                <div class="rounded-3xl border border-purple-500/20 bg-linear-to-br from-purple-500/10 to-pink-500/5 p-6 backdrop-blur-md">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-purple-300/70">Avg Order Value</p>
                    <p class="mt-3 text-3xl font-semibold text-white">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</p>
                    <p class="mt-2 text-sm text-slate-400">Rata-rata nilai transaksi yang berhasil dibayar.</p>
                </div>
            </div>
        </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="rounded-4xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.35em] text-slate-500">Yang perlu diputuskan</p>
                    <h2 class="mt-2 text-xl font-semibold text-white">3 hal yang paling penting hari ini</h2>
                </div>

                <span class="rounded-full border border-white/10 bg-black/20 px-3 py-1 text-xs text-slate-300">Owner view</span>
            </div>

            <div class="mt-6 space-y-4">
                <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-white">Backlog yang sudah dibayar</p>
                            <p class="mt-1 text-sm text-slate-400">Pesanan yang menunggu diproses dan paling cepat memengaruhi trust customer.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-amber-400">{{ $backlogOrders }}</p>
                            <a href="{{ $ordersUrl }}" class="text-sm font-semibold text-amber-300 hover:text-amber-200">Buka order</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-white">Transaksi menunggu konfirmasi</p>
                            <p class="mt-1 text-sm text-slate-400">Kalau angka ini naik, cash-in sedang melambat.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-blue-400">{{ $pendingTransactions }}</p>
                            <a href="{{ $transactionsUrl }}" class="text-sm font-semibold text-blue-300 hover:text-blue-200">Review transaksi</a>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-white">Order yang sudah selesai</p>
                            <p class="mt-1 text-sm text-slate-400">Ini bukti eksekusi yang sudah memberi hasil ke bisnis.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-semibold text-emerald-400">{{ $ordersSuccess }}</p>
                            <p class="text-sm text-slate-400">Completion rate {{ $completionRate }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-4xl border border-white/10 bg-white/5 p-6 backdrop-blur-md">
            <p class="text-xs font-bold uppercase tracking-[0.35em] text-slate-500">Kondisi bisnis</p>
            <h2 class="mt-2 text-xl font-semibold text-white">Sinyal utama untuk owner</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-amber-500/20 bg-amber-500/5 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-amber-300/70">Order masuk hari ini</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $ordersToday }}</p>
                </div>

                <div class="rounded-3xl border border-emerald-500/20 bg-emerald-500/5 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-emerald-300/70">Order sedang diproses</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $ordersProcessing }}</p>
                </div>

                <div class="rounded-3xl border border-blue-500/20 bg-blue-500/5 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-blue-300/70">Transaksi berhasil</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $paidTransactions }}</p>
                </div>

                <div class="rounded-3xl border border-red-500/20 bg-red-500/5 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-[0.35em] text-red-300/70">Transaksi bermasalah</p>
                    <p class="mt-2 text-3xl font-semibold text-white">{{ $failedTransactions }}</p>
                </div>
            </div>

            <div class="mt-6 rounded-3xl border border-white/10 bg-slate-900/60 p-4">
                <p class="text-sm font-semibold text-white">Ringkasan singkat</p>
                <p class="mt-2 text-sm leading-7 text-slate-400">
                    Revenue bulan ini sudah terkunci di Rp {{ number_format($revenueMonth, 0, ',', '.') }} dengan average order value Rp {{ number_format($averageOrderValue, 0, ',', '.') }}.
                    Kalau backlog terus turun dan completion rate naik, dashboard ini akan menunjukkan bisnis yang sehat, bukan cuma ramai aktivitas.
                </p>
            </div>
        </section>
    </div>
</div>
