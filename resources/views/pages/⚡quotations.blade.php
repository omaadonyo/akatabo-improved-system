<?php

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Quotations')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public $viewingQuotation = null;

    public bool $showViewQuotationModal = false;

    public function viewQuotation(Quotation $quotation): void
    {
        $this->viewingQuotation = $quotation->load('items', 'customer');
        $this->showViewQuotationModal = true;
    }

    public function delete(Quotation $quotation): void
    {
        ActivityLog::log('deleted', 'Quotation ' . $quotation->quotation_number . ' deleted', [
            'quotation_id' => $quotation->id,
            'quotation_number' => $quotation->quotation_number,
        ]);
        $quotation->items()->delete();
        $quotation->delete();
        Flux::toast(variant: 'success', text: __('Quotation deleted.'));
    }

    public function convertToInvoice(int $id): void
    {
        $quotation = Quotation::with('items')->where('business_id', activeBusinessId())->findOrFail($id);

        if ($quotation->status === 'converted') {
            Flux::toast(variant: 'warning', text: __('Already converted.'));
            return;
        }

        $last = Invoice::where('business_id', activeBusinessId())->orderBy('id', 'desc')->first();
        $next = $last ? ((int) substr($last->invoice_number, -4)) + 1 : 1;
        $invoiceNumber = 'INV-' . str_pad($next, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'business_id' => $quotation->business_id,
            'customer_id' => $quotation->customer_id,
            'quotation_id' => $quotation->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'subtotal' => $quotation->subtotal,
            'discount_type' => $quotation->discount_type,
            'discount_value' => $quotation->discount_value,
            'discount_amount' => $quotation->discount_amount,
            'tax_name' => $quotation->tax_name,
            'tax_rate' => $quotation->tax_rate,
            'tax_amount' => $quotation->tax_amount,
            'total' => $quotation->total,
            'notes' => $quotation->notes,
            'status' => 'sent',
            'paid_amount' => 0,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($quotation->items as $item) {
            $invoice->items()->create([
                'type' => $item->type,
                'item_id' => $item->item_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);
        }

        $quotation->update(['status' => 'converted', 'updated_by' => auth()->id()]);

        ActivityLog::log('converted', 'Quotation ' . $quotation->quotation_number . ' converted to invoice ' . $invoiceNumber);
        Flux::toast(variant: 'success', text: __('Quotation converted to invoice.'));
    }

    public function exportPdf(Quotation $quotation)
    {
        $quotation->load('items', 'customer', 'business');

        $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $quotation]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $quotation->quotation_number . '.pdf'
        );
    }

    public function exportAllPdf()
    {
        $businessId = activeBusinessId();
        $quotations = Quotation::where('business_id', $businessId)
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('quotation_number', 'like', '%'.$this->search.'%')
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->with('customer')
            ->orderBy('id', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.quotations-list', [
            'quotations' => $quotations,
            'business' => activeBusiness(),
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'quotations-' . now()->format('Y-m-d') . '.pdf'
        );
    }

    public function exportExcel()
    {
        $businessId = activeBusinessId();
        $quotations = Quotation::where('business_id', $businessId)
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('quotation_number', 'like', '%'.$this->search.'%')
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->with('customer')
            ->orderBy('id', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="quotations-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($quotations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Number', 'Customer', 'Issue Date', 'Valid Until', 'Subtotal', 'Discount', 'Tax', 'Total', 'Status']);

            foreach ($quotations as $q) {
                fputcsv($handle, [
                    $q->quotation_number,
                    $q->customer?->name ?? 'Walk-in',
                    $q->issue_date->format('Y-m-d'),
                    $q->valid_until?->format('Y-m-d') ?? '',
                    number_format($q->subtotal, 2),
                    number_format($q->discount_amount, 2),
                    number_format($q->tax_amount, 2),
                    number_format($q->total, 2),
                    $q->status,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}; ?>

<div class="mx-auto" style="width: 80%;">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ __('Quotations') }}</flux:heading>
            <flux:subheading class="mt-1">{{ __('Create and manage quotations for your customers.') }}</flux:subheading>
        </div>
        <flux:button variant="primary" :href="route('quotations.create')" wire:navigate>
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
            {{ __('New Quotation') }}
        </flux:button>
    </div>

    <div class="mt-6 flex items-center justify-between">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search quotations...')" clearable class="w-72" />
        <div class="flex items-center gap-2">
            <flux:button wire:click="exportAllPdf" variant="ghost" icon="arrow-down-tray" class="text-violet-600! hover:text-violet-800! dark:text-violet-400! dark:hover:text-violet-300! cursor-pointer">
                {{ __('PDF') }}
            </flux:button>
            <flux:button wire:click="exportExcel" variant="ghost" icon="document-arrow-down" class="text-emerald-600! hover:text-emerald-800! dark:text-emerald-400! dark:hover:text-emerald-300! cursor-pointer">
                {{ __('Excel') }}
            </flux:button>
        </div>
    </div>

    <div class="mt-4">
        <flux:table :paginate="Quotation::where('business_id', activeBusinessId())
            ->when($this->search, fn($q) => $q->where(function($q) {
                $q->where('quotation_number', 'like', '%'.$this->search.'%')
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%'.$this->search.'%'));
            }))
            ->orderBy('id', 'desc')
            ->paginate(10)">
            <flux:table.columns>
                <flux:table.column>{{ __('Number') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Issue Date') }}</flux:table.column>
                <flux:table.column>{{ __('Valid Until') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Total') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse (Quotation::where('business_id', activeBusinessId())
                    ->when($this->search, fn($q) => $q->where(function($q) {
                        $q->where('quotation_number', 'like', '%'.$this->search.'%')
                          ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', '%'.$this->search.'%'));
                    }))
                    ->orderBy('id', 'desc')
                    ->paginate(10) as $quotation)
                    <flux:table.row :key="$quotation->id">
                        <flux:table.cell class="font-mono text-xs font-medium">{{ $quotation->quotation_number }}</flux:table.cell>
                        <flux:table.cell>{{ $quotation->customer?->name ?? __('Walk-in') }}</flux:table.cell>
                        <flux:table.cell>{{ $quotation->issue_date->format('d M Y') }}</flux:table.cell>
                        <flux:table.cell>{{ $quotation->valid_until?->format('d M Y') ?? '—' }}</flux:table.cell>
                        <flux:table.cell align="end" class="font-medium">UGX {{ number_format($quotation->total, 2) }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $qStyle = match($quotation->status) {
                                    'draft' => ['color' => 'neutral', 'icon' => 'clock', 'variant' => 'solid'],
                                    'sent' => ['color' => 'blue', 'icon' => 'paper-airplane', 'variant' => 'primary'],
                                    'accepted' => ['color' => 'emerald', 'icon' => 'check-badge', 'variant' => 'success'],
                                    'converted' => ['color' => 'indigo', 'icon' => 'arrow-path', 'variant' => 'ghost'],
                                    'rejected' => ['color' => 'red', 'icon' => 'x-circle', 'variant' => 'danger'],
                                    default => ['color' => 'neutral', 'icon' => 'clock', 'variant' => 'ghost'],
                                };
                            @endphp
                            <flux:badge :color="$qStyle['color']" :icon="$qStyle['icon']" :variant="$qStyle['variant']" size="sm">
                                {{ ucfirst($quotation->status) }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell align="end">
                            <div class="flex items-center justify-end gap-1">
                                <flux:button wire:click="viewQuotation({{ $quotation->id }})" variant="ghost" size="sm" icon="eye" class="cursor-pointer text-indigo-600! hover:text-indigo-800! dark:text-indigo-400! dark:hover:text-indigo-300!" title="{{ __('View') }}" />
                                <flux:button wire:click="exportPdf({{ $quotation->id }})" variant="ghost" size="sm" icon="arrow-down-tray" class="cursor-pointer text-violet-600! hover:text-violet-800! dark:text-violet-400! dark:hover:text-violet-300!" title="{{ __('Download PDF') }}" />
                                <flux:button :href="route('quotations.edit', $quotation->id)" variant="ghost" size="sm" icon="pencil-square" wire:navigate class="text-sky-600! hover:text-sky-800! dark:text-sky-400! dark:hover:text-sky-300!" />
                                @if ($quotation->status !== 'converted')
                                    <flux:button wire:click="convertToInvoice({{ $quotation->id }})" variant="ghost" size="sm" icon="file-invoice" class="cursor-pointer text-amber-600! hover:text-amber-800! dark:text-amber-400! dark:hover:text-amber-300!" title="{{ __('Convert to Invoice') }}" />
                                @endif
                                <flux:button wire:click="delete({{ $quotation->id }})" variant="ghost" size="sm" icon="trash" class="text-red-500! hover:text-red-700!" />
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7">
                            <div class="flex flex-col items-center justify-center py-12 text-center">
                                <flux:heading class="text-zinc-500 dark:text-zinc-400">{{ __('No quotations yet') }}</flux:heading>
                                <flux:subheading class="mt-1">{{ __('Create your first quotation to send to a customer.') }}</flux:subheading>
                                <flux:button variant="primary" :href="route('quotations.create')" wire:navigate class="mt-4">{{ __('New Quotation') }}</flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>

    {{-- View Quotation Modal --}}
    <flux:modal wire:model="showViewQuotationModal" class="max-w-2xl">
        <div class="space-y-6">
            <div class="flex items-start justify-between">
                <div>
                    <flux:heading size="lg">{{ $viewingQuotation?->quotation_number }}</flux:heading>
                    <flux:subheading>{{ __('Quotation summary') }}</flux:subheading>
                </div>
                @php
                    $qvStatus = $viewingQuotation?->status ?? '';
                    $qvStyle = match($qvStatus) {
                        'draft' => ['color' => 'neutral', 'icon' => 'clock', 'variant' => 'solid'],
                        'sent' => ['color' => 'blue', 'icon' => 'paper-airplane', 'variant' => 'primary'],
                        'accepted' => ['color' => 'emerald', 'icon' => 'check-badge', 'variant' => 'success'],
                        'converted' => ['color' => 'indigo', 'icon' => 'arrow-path', 'variant' => 'ghost'],
                        'rejected' => ['color' => 'red', 'icon' => 'x-circle', 'variant' => 'danger'],
                        default => ['color' => 'neutral', 'icon' => 'clock', 'variant' => 'ghost'],
                    };
                @endphp
                <flux:badge :color="$qvStyle['color']" :icon="$qvStyle['icon']" :variant="$qvStyle['variant']">{{ ucfirst($qvStatus) }}</flux:badge>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div><flux:label>{{ __('Customer') }}</flux:label><p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ $viewingQuotation?->customer?->name ?? __('Walk-in') }}</p></div>
                <div><flux:label>{{ __('Issue Date') }}</flux:label><p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ $viewingQuotation?->issue_date?->format('d M Y') ?? '—' }}</p></div>
                <div><flux:label>{{ __('Valid Until') }}</flux:label><p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">{{ $viewingQuotation?->valid_until?->format('d M Y') ?? '—' }}</p></div>
                <div><flux:label>{{ __('Total Amount') }}</flux:label><p class="mt-1 text-sm font-medium text-neutral-900 dark:text-white">UGX {{ number_format($viewingQuotation?->total ?? 0, 2) }}</p></div>
            </div>
            @if ($viewingQuotation?->items?->isNotEmpty())
                <div>
                    <flux:label>{{ __('Items') }}</flux:label>
                    <div class="mt-2 divide-y divide-neutral-100 dark:divide-neutral-800">
                        @foreach ($viewingQuotation->items as $item)
                            <div class="flex items-center justify-between py-1.5 text-sm">
                                <span class="text-neutral-900 dark:text-white">{{ $item->description }}</span>
                                <span class="font-medium text-neutral-900 dark:text-white">UGX {{ number_format($item->total, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="flex justify-end"><flux:modal.close><flux:button variant="filled">{{ __('Close') }}</flux:button></flux:modal.close></div>
        </div>
    </flux:modal>
</div>
