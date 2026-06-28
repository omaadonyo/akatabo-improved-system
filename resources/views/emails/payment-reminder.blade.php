<x-mail-layout title="Payment Reminder" :business="$invoice->business ?? null">
    <h2>{{ __('Payment Reminder') }}</h2>
    <p>{{ __('Dear :name,', ['name' => $invoice->customer?->name ?? __('Valued Customer')]) }}</p>
    <p>{{ __('This is a reminder that invoice :number is due soon.', ['number' => strtoupper($invoice->invoice_number)]) }}</p>

    <div class="details">
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
        <div class="row">
            <span class="label">{{ __('Amount') }}</span>
            <span class="value">UGX {{ number_format($invoice->total, 2) }}</span>
        </div>
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('Balance') }}</span>
            <span class="value">UGX {{ number_format(max(0, $invoice->total - $invoice->paid_amount), 2) }}</span>
        </div>
    </div>
</x-mail-layout>
