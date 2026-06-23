<x-mail::message>
# {{ __('Friendly Reminder') }}

{{ __('Hi :name,', ['name' => $rental->customer->name]) }}

{!! nl2br(e($customMessage)) !!}

<x-mail::button :url="route('invoices')">
{{ __('View Invoices') }}
</x-mail::button>

{{ __('Thank you!') }}

{{ activeBusiness()->name }}
</x-mail::message>
