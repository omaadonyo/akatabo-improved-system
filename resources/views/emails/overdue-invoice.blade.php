<x-mail-layout title="Payment Reminder" :business="$invoice->business ?? null">
    <h2>{{ __('Payment Reminder') }}</h2>
    <p>{{ __('Dear :name,', ['name' => $invoice->customer?->name ?? __('Valued Customer')]) }}</p>
    <p>{{ __('This is a friendly reminder that invoice :number is now overdue.', ['number' => strtoupper($invoice->invoice_number)]) }}</p>

    <div class="details">
        <div class="row">
            <span class="label">{{ __('Invoice') }}</span>
            <span class="value">{{ strtoupper($invoice->invoice_number) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Due Date') }}</span>
            <span class="value">{{ $invoice->due_date?->format('d M Y') }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Days Overdue') }}</span>
            <span class="value">{{ $overdueDays }} {{ __('days') }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Total') }}</span>
            <span class="value">UGX {{ number_format($invoice->total, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Paid') }}</span>
            <span class="value">UGX {{ number_format($invoice->paid_amount, 2) }}</span>
        </div>
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('Balance Due') }}</span>
            <span class="value">UGX {{ number_format(max(0, $invoice->total - $invoice->paid_amount), 2) }}</span>
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

    <p style="font-size: 13px; color: #8a8aaa; text-align: center; margin-top: 16px;">
        {{ __('If you have already made payment, please disregard this notice.') }}
    </p>
</x-mail-layout>
