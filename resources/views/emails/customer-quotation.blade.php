<x-mail::message>
# New Fabric Quotation Request

**{{ $quotation->customer_name }}** has requested a quotation for **{{ $quotation->fabric->name }}**.

---

### Customer Details
- **Name:** {{ $quotation->customer_name }}
- **Email:** {{ $quotation->customer_email }}
@if($quotation->customer_phone)
- **Phone:** {{ $quotation->customer_phone }}
@endif

### Quotation Details
- **Fabric:** {{ $quotation->fabric->name }} ({{ $quotation->fabric->color ?? 'N/A' }})
- **Length:** {{ number_format($quotation->length_meters, 2) }}m
@if($quotation->width_meters)
- **Width:** {{ number_format($quotation->width_meters, 2) }}m
@endif
- **Price per meter:** UGX {{ number_format($quotation->fabric->selling_price_per_meter, 2) }}
- **Estimated Total:** **UGX {{ number_format($quotation->total_price, 2) }}**

@if($quotation->customer_message)
### Message
{{ $quotation->customer_message }}
@endif

<x-mail::button :url="route('login')">
View in Dashboard
</x-mail::button>
</x-mail::message>
