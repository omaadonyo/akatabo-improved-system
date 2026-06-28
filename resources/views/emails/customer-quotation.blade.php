<x-mail-layout title="New Fabric Request" :business="$quotation->fabric->business ?? null">
    <h2>{{ __('New Fabric Quotation Request') }}</h2>

    <div class="details">
        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #1a1a2e;">{{ __('Customer Details') }}</h3>
        <div class="row">
            <span class="label">{{ __('Name') }}</span>
            <span class="value">{{ $quotation->customer_name }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Email') }}</span>
            <span class="value">{{ $quotation->customer_email }}</span>
        </div>
        @if ($quotation->customer_phone)
        <div class="row">
            <span class="label">{{ __('Phone') }}</span>
            <span class="value">{{ $quotation->customer_phone }}</span>
        </div>
        @endif
    </div>

    <div class="details">
        <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #1a1a2e;">{{ __('Request Details') }}</h3>
        <div class="row">
            <span class="label">{{ __('Fabric') }}</span>
            <span class="value">{{ $quotation->fabric->name ?? '—' }}</span>
        </div>
        <div class="row">
            <span class="label">{{ __('Meters') }}</span>
            <span class="value">{{ number_format($quotation->meters, 2) }}</span>
        </div>
        <div class="row" style="border-bottom: none;">
            <span class="label">{{ __('Total Price') }}</span>
            <span class="value">UGX {{ number_format($quotation->total_price, 2) }}</span>
        </div>
        @if ($quotation->customer_message)
        <div class="row" style="border-bottom: none; flex-direction: column; gap: 4px;">
            <span class="label">{{ __('Message') }}</span>
            <span style="color: #4a4a6a; font-size: 14px;">{{ $quotation->customer_message }}</span>
        </div>
        @endif
    </div>
</x-mail-layout>
