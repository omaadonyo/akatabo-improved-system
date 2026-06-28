<x-mail-layout title="Rental Reminder" :business="$rental->room->business ?? null">
    <h2>{{ __('Friendly Reminder') }}</h2>
    <p>{{ __('Dear :name,', ['name' => $rental->customer->name ?? __('Valued Customer')]) }}</p>
    <div style="margin: 16px 0; padding: 16px 20px; background: #f8f9fc; border-radius: 8px; font-size: 15px; line-height: 1.7; color: #4a4a6a;">
        {!! nl2br(e($customMessage)) !!}
    </div>
</x-mail-layout>
