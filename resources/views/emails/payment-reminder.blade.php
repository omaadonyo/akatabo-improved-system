<x-mail::message>
# {{ __('Payment Reminder') }}

{{ __('Dear :name,', ['name' => $invoice->customer?->name ?? 'Valued Customer']) }}

{{ __('This is a reminder that invoice **:number** is due on **:date**.', ['number' => $invoice->invoice_number, 'date' => $invoice->due_date?->format('d M Y') ?? 'N/A']) }}

**{{ __('Amount Due:') }}** UGX {{ number_format(max(0, (float) $invoice->total - (float) $invoice->paid_amount), 2) }}

<x-mail::button :url="route('invoices')">
{{ __('View Invoice') }}
</x-mail::button>

{{ __('If you have already made the payment, please disregard this message.') }}

{{ __('Thank you for your business!') }}

{{ $invoice->business->name ?? '' }}
</x-mail::message>
