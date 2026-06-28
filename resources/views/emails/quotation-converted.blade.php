<x-mail-layout title="Quotation Converted to Invoice" :business="$invoice->business ?? null">
    <h2>{{ __('Your Quotation Has Been Converted') }}</h2>
    <p>{{ __('Dear :name,', ['name' => $invoice->customer?->name ?? __('Valued Customer')]) }}</p>
    <p>{{ __('Your quotation has been converted into an invoice. Below are the details:') }}</p>

    <div class="details">
        <div class="row">
            <span class="label">{{ __('Quotation') }}</span>
            <span class="value">{{ strtoupper($quotation->quotation_number) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Invoice') }}</span>
            <span class="value">{{ strtoupper($invoice->invoice_number) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Issue Date') }}</span>
            <span class="value">{{ $invoice->issue_date?->format('d M Y') }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Due Date') }}</span>
            <span class="value">{{ $invoice->due_date?->format('d M Y') }}</span>
        </div>
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('Total') }}</span>
            <span class="value">UGX {{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    @if ($invoice->items->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('Qty') }}</th>
                    <th>{{ __('Amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description ?: '—' }}</td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>UGX {{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-mail-layout>
