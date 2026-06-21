<?php

use App\Models\Customer;
use App\Models\Fabric;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductService;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reports')] class extends Component {
    public string $period = 'all';

    public function mount(): void
    {
        if (! activeBusiness()) {
            $this->redirect(route('onboarding', absolute: false), navigate: true);
        }
    }

    public function with(): array
    {
        $businessId = activeBusinessId();

        $dateRange = match ($this->period) {
            '30d' => [now()->subDays(30), now()],
            '90d' => [now()->subDays(90), now()],
            '12m' => [now()->subYear(), now()],
            default => null,
        };

        $applyDateFilter = function ($query) use ($dateRange) {
            if ($dateRange) {
                $query->whereBetween('created_at', $dateRange);
            }
            return $query;
        };

        $invoices = Invoice::where('business_id', $businessId)->when($dateRange, fn($q) => $q->whereBetween('issue_date', $dateRange));
        $payments = Payment::whereHas('invoice', fn($q) => $q->where('business_id', $businessId))
            ->when($dateRange, fn($q) => $q->whereBetween('payment_date', $dateRange));

        return [
            'totalRevenue' => (clone $payments)->sum('amount'),
            'paymentCount' => (clone $payments)->count(),
            'totalInvoiced' => (clone $invoices)->sum('total'),
            'invoiceCount' => (clone $invoices)->count(),
            'paidInvoices' => (clone $invoices)->where('status', 'paid')->count(),
            'pendingInvoices' => (clone $invoices)->whereIn('status', ['draft', 'sent', 'overdue'])->count(),
            'customerCount' => Customer::where('business_id', $businessId)->when($dateRange, fn($q) => $applyDateFilter($q))->count(),
            'productCount' => ProductService::where('business_id', $businessId)->when($dateRange, fn($q) => $applyDateFilter($q))->count(),
            'fabricCount' => Fabric::where('business_id', $businessId)->when($dateRange, fn($q) => $applyDateFilter($q))->count(),
            'quotationCount' => Quotation::where('business_id', $businessId)->when($dateRange, fn($q) => $applyDateFilter($q))->count(),
            'recentPayments' => (clone $payments)->with('invoice', 'creator')->latest()->take(10)->get(),
            'topInvoices' => (clone $invoices)->with('customer')->orderByDesc('total')->take(5)->get(),
            'paymentMethodBreakdown' => (clone $payments)
                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('payment_method')
                ->get(),
            'period' => $this->period,
        ];
    }

    public function exportPdf()
    {
        $business = activeBusiness();
        $data = $this->with();
        $data['business'] = $business;
        $data['periodLabel'] = match ($this->period) {
            '30d' => __('Last 30 Days'),
            '90d' => __('Last 90 Days'),
            '12m' => __('Last 12 Months'),
            default => __('All Time'),
        };

        $pdf = Pdf::loadView('pdf.reports', $data);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            __('reports') . '-' . now()->format('Y-m-d') . '.pdf'
        );
    }
}; ?>

<div style="width: 80%; margin: 0 auto;">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
        <div class="flex items-center gap-3">
            <flux:select wire:model.live="period" class="w-44">
                <option value="all">{{ __('All Time') }}</option>
                <option value="30d">{{ __('Last 30 Days') }}</option>
                <option value="90d">{{ __('Last 90 Days') }}</option>
                <option value="12m">{{ __('Last 12 Months') }}</option>
            </flux:select>
            <flux:button wire:click="exportPdf" variant="ghost" icon="arrow-down-tray">
                {{ __('PDF') }}
            </flux:button>
        </div>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-emerald-50 to-green-50 dark:from-emerald-950/30 dark:to-green-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-emerald-200/30 dark:bg-emerald-500/10 blur-2xl"></div>
            <div class="absolute right-3 top-3 flex size-10 items-center justify-center rounded-xl bg-emerald-100 dark:bg-emerald-900/40 shadow-sm shadow-emerald-200/50 dark:shadow-emerald-900/30">
                <flux:icon name="banknotes" variant="solid" class="size-5 text-emerald-600 dark:text-emerald-400" />
            </div>
            <div class="text-sm font-medium text-emerald-700 dark:text-emerald-300">{{ __('Total Revenue') }}</div>
            <div class="mt-1 text-2xl font-bold tracking-tight text-emerald-900 dark:text-emerald-100">UGX {{ number_format($totalRevenue, 0) }}</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-emerald-600/70 dark:text-emerald-400/70">
                <span>{{ $paymentCount }} {{ __('payments') }}</span>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-blue-50 to-sky-50 dark:from-blue-950/30 dark:to-sky-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-blue-200/30 dark:bg-blue-500/10 blur-2xl"></div>
            <div class="absolute right-3 top-3 flex size-10 items-center justify-center rounded-xl bg-blue-100 dark:bg-blue-900/40 shadow-sm shadow-blue-200/50 dark:shadow-blue-900/30">
                <flux:icon name="document-text" variant="solid" class="size-5 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="text-sm font-medium text-blue-700 dark:text-blue-300">{{ __('Total Invoiced') }}</div>
            <div class="mt-1 text-2xl font-bold tracking-tight text-blue-900 dark:text-blue-100">UGX {{ number_format($totalInvoiced, 0) }}</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-blue-600/70 dark:text-blue-400/70">
                <span>{{ $invoiceCount }} {{ __('invoices') }}</span>
                <span>&middot;</span>
                <span class="text-emerald-500 font-medium">{{ $paidInvoices }} {{ __('paid') }}</span>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-amber-200/30 dark:bg-amber-500/10 blur-2xl"></div>
            <div class="absolute right-3 top-3 flex size-10 items-center justify-center rounded-xl bg-amber-100 dark:bg-amber-900/40 shadow-sm shadow-amber-200/50 dark:shadow-amber-900/30">
                <flux:icon name="exclamation-triangle" variant="solid" class="size-5 text-amber-600 dark:text-amber-400" />
            </div>
            <div class="text-sm font-medium text-amber-700 dark:text-amber-300">{{ __('Outstanding') }}</div>
            <div class="mt-1 text-2xl font-bold tracking-tight text-amber-900 dark:text-amber-100">UGX {{ number_format(max($totalInvoiced - $totalRevenue, 0), 0) }}</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-amber-600/70 dark:text-amber-400/70">
                <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $pendingInvoices }} {{ __('unpaid invoices') }}</span>
            </div>
        </flux:card>

        <flux:card class="relative overflow-hidden p-5 bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-950/30 dark:to-purple-950/20">
            <div class="absolute -bottom-4 -right-4 size-24 rounded-full bg-violet-200/30 dark:bg-violet-500/10 blur-2xl"></div>
            <div class="absolute right-3 top-3 flex size-10 items-center justify-center rounded-xl bg-violet-100 dark:bg-violet-900/40 shadow-sm shadow-violet-200/50 dark:shadow-violet-900/30">
                <flux:icon name="users" variant="solid" class="size-5 text-violet-600 dark:text-violet-400" />
            </div>
            <div class="text-sm font-medium text-violet-700 dark:text-violet-300">{{ __('Customers') }}</div>
            <div class="mt-1 text-2xl font-bold tracking-tight text-violet-900 dark:text-violet-100">{{ $customerCount }}</div>
            <div class="mt-1 flex items-center gap-1 text-xs text-violet-600/70 dark:text-violet-400/70">
                <span>{{ $quotationCount }} {{ __('quotes') }}</span>
                <span>&middot;</span>
                <span>{{ $invoiceCount }} {{ __('invoices') }}</span>
            </div>
        </flux:card>
    </div>

    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <flux:card class="p-5 bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-950">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Payment Methods') }}</flux:heading>
                <flux:icon name="credit-card" class="size-4 text-neutral-400" />
            </div>
            <div class="space-y-3">
                @forelse ($paymentMethodBreakdown as $method)
                    @php
                        $methodColors = [
                            'cash' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500', 'bar' => 'bg-emerald-400 dark:bg-emerald-600'],
                            'bank_transfer' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-700 dark:text-blue-300', 'dot' => 'bg-blue-500', 'bar' => 'bg-blue-400 dark:bg-blue-600'],
                            'mobile_money' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-700 dark:text-violet-300', 'dot' => 'bg-violet-500', 'bar' => 'bg-violet-400 dark:bg-violet-600'],
                            'credit_card' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-700 dark:text-amber-300', 'dot' => 'bg-amber-500', 'bar' => 'bg-amber-400 dark:bg-amber-600'],
                            'cheque' => ['bg' => 'bg-sky-50 dark:bg-sky-900/20', 'text' => 'text-sky-700 dark:text-sky-300', 'dot' => 'bg-sky-500', 'bar' => 'bg-sky-400 dark:bg-sky-600'],
                        ];
                        $colors = $methodColors[$method->payment_method] ?? ['bg' => 'bg-neutral-50 dark:bg-neutral-800', 'text' => 'text-neutral-700 dark:text-neutral-300', 'dot' => 'bg-neutral-400', 'bar' => 'bg-neutral-300 dark:bg-neutral-600'];
                        $maxTotal = $paymentMethodBreakdown->max('total');
                        $barWidth = $maxTotal > 0 ? round(($method->total / $maxTotal) * 100) : 0;
                    @endphp
                    <div class="rounded-lg p-3 {{ $colors['bg'] }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="flex size-2.5 rounded-full {{ $colors['dot'] }}"></span>
                                <span class="text-sm font-medium capitalize {{ $colors['text'] }}">{{ str_replace('_', ' ', $method->payment_method) }}</span>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-neutral-900 dark:text-white">UGX {{ number_format($method->total, 0) }}</div>
                                <div class="text-xs text-neutral-400">{{ $method->count }} {{ __('payments') }}</div>
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-neutral-200 dark:bg-neutral-700">
                            <div class="h-full rounded-full {{ $colors['bar'] }} transition-all" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-neutral-400">{{ __('No payments recorded.') }}</p>
                @endforelse
            </div>
        </flux:card>

        <flux:card class="p-5 bg-gradient-to-br from-white to-neutral-50 dark:from-neutral-900 dark:to-neutral-950">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">{{ __('Highest Invoices') }}</flux:heading>
                <flux:icon name="arrow-trending-up" class="size-4 text-neutral-400" />
            </div>
            <div class="space-y-3">
                @forelse ($topInvoices as $i => $invoice)
                    @php
                        $rankColors = ['text-amber-500', 'text-neutral-400', 'text-orange-600'];
                        $rankIcons = ['trophy', 'star', 'arrow-trending-up'];
                        $rankColor = $rankColors[$i] ?? 'text-neutral-300';
                        $rankIcon = $rankIcons[$i] ?? 'arrow-trending-up';
                    @endphp
                    <div class="flex items-center justify-between rounded-lg p-2 transition-colors hover:bg-neutral-50 dark:hover:bg-neutral-800/50">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="shrink-0 flex size-7 items-center justify-center rounded-full bg-neutral-100 dark:bg-neutral-800 {{ $rankColor }}">
                                <flux:icon :name="$rankIcon" variant="solid" class="size-3.5" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-neutral-900 dark:text-white truncate">{{ $invoice->invoice_number }}</span>
                                    @php
                                        $invRankStyle = match($invoice->status) {
                                            'paid' => ['color' => 'emerald', 'icon' => 'check-circle'],
                                            'overdue' => ['color' => 'red', 'icon' => 'exclamation-triangle'],
                                            'sent' => ['color' => 'blue', 'icon' => 'paper-airplane'],
                                            'partial' => ['color' => 'teal', 'icon' => 'adjustments-horizontal'],
                                            'draft' => ['color' => 'neutral', 'icon' => 'clock'],
                                            default => ['color' => 'neutral', 'icon' => 'clock'],
                                        };
                                    @endphp
                                    <flux:badge variant="pill" size="sm" :color="$invRankStyle['color']" :icon="$invRankStyle['icon']">
                                        {{ ucfirst($invoice->status) }}
                                    </flux:badge>
                                </div>
                                <div class="text-xs text-neutral-400 truncate">{{ $invoice->customer?->name ?? __('Walk-in') }}</div>
                            </div>
                        </div>
                        <span class="ml-3 shrink-0 text-sm font-semibold text-neutral-900 dark:text-white">UGX {{ number_format($invoice->total, 0) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-neutral-400">{{ __('No invoices yet.') }}</p>
                @endforelse
            </div>
        </flux:card>
    </div>

    <flux:card class="p-5">
        <div class="mb-4 flex items-center justify-between">
            <flux:heading size="sm">{{ __('Recent Payments') }}</flux:heading>
            <flux:icon name="clock" class="size-4 text-neutral-400" />
        </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column>{{ __('Receipt') }}</flux:table.column>
                <flux:table.column>{{ __('Invoice') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Method') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded By') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($recentPayments as $payment)
                    @php
                        $methodBadgeColors = [
                            'cash' => 'lime',
                            'bank_transfer' => 'blue',
                            'mobile_money' => 'violet',
                            'credit_card' => 'amber',
                            'cheque' => 'sky',
                        ];
                        $methodIcons = [
                            'cash' => 'banknotes',
                            'bank_transfer' => 'building-bank',
                            'mobile_money' => 'device-phone-mobile',
                            'credit_card' => 'credit-card',
                            'cheque' => 'document-text',
                        ];
                        $badgeColor = $methodBadgeColors[$payment->payment_method] ?? 'neutral';
                        $badgeIcon = $methodIcons[$payment->payment_method] ?? 'clock';
                    @endphp
                    <flux:table.row>
                        <flux:table.cell class="font-mono text-xs font-medium text-indigo-500">{{ $payment->receipt_number ?? '—' }}</flux:table.cell>
                        <flux:table.cell class="font-medium">{{ $payment->invoice?->invoice_number ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end" class="font-medium">UGX {{ number_format($payment->amount, 0) }}</flux:table.cell>
                        <flux:table.cell class="text-neutral-500">{{ $payment->payment_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="pill" size="sm" :color="$badgeColor" :icon="$badgeIcon">
                                {{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-neutral-500">{{ $payment->creator?->name ?? '—' }}</flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6">
                            <div class="flex flex-col items-center py-8 text-center">
                                <flux:heading class="text-neutral-400">{{ __('No payments yet') }}</flux:heading>
                                <flux:icon name="credit-card" class="mt-2 size-6 text-neutral-300" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:card>
</div>
