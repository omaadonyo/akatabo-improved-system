<x-mail-layout title="Payment Receipt" :business="$invoice->business ?? null">
    <h2>{{ __('Payment Received') }}</h2>
    <p>{{ __('A payment has been recorded against invoice :number.', ['number' => strtoupper($invoice->invoice_number)]) }}</p>

    <div class="details">
        <div class="row">
            <span class="label">{{ __('Invoice') }}</span>
            <span class="value">{{ strtoupper($invoice->invoice_number) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Receipt No.') }}</span>
            <span class="value">{{ strtoupper($payment->receipt_number) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Amount') }}</span>
            <span class="value">UGX {{ number_format($payment->amount, 2) }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Date') }}</span>
            <span class="value">{{ $payment->payment_date->format('d M Y') }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Method') }}</span>
            <span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
        </div>
        @if ($payment->reference)
        <div class="row">
            <span class="label">{{ __('Reference') }}</span>
            <span class="value">{{ strtoupper($payment->reference) }}</span>
        </div>
        @endif
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('Status') }}</span>
            <span class="value">{{ ucfirst($invoice->status) }}</span>
        </div>
    </div>

    @if ($isAdminCopy ?? false)
        <p style="font-size: 13px; color: #8a8aaa; text-align: center; margin-top: 16px;">
            {{ __('This is an admin notification.') }}
        </p>
    @endif
</x-mail-layout>
