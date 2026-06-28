<x-mail-layout title="Low Stock Alert" :business="$business">
    <h2>{{ __('Low Stock Alert') }}</h2>
    <p>{{ __('The following items in your inventory have fallen below their minimum stock levels:') }}</p>

    @if ($products->isNotEmpty())
        <h3 style="font-size: 14px; font-weight: 600; margin: 20px 0 8px; color: #1a1a2e;">{{ __('Products') }}</h3>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Product') }}</th>
                    <th>{{ __('Stock') }}</th>
                    <th>{{ __('Min. Level') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $p)
                    <tr>
                        <td>{{ $p->name }}</td>
                        <td style="color: #dc2626;">{{ number_format($p->quantity, 2) }}</td>
                        <td>{{ number_format($p->low_stock_threshold, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if ($fabrics->isNotEmpty())
        <h3 style="font-size: 14px; font-weight: 600; margin: 20px 0 8px; color: #1a1a2e;">{{ __('Fabrics') }}</h3>
        <table>
            <thead>
                <tr>
                    <th>{{ __('Fabric') }}</th>
                    <th>{{ __('Remaining') }}</th>
                    <th>{{ __('Min. Level') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fabrics as $f)
                    <tr>
                        <td>{{ $f->name }} ({{ strtoupper($f->roll_code) }})</td>
                        <td style="color: #dc2626;">{{ number_format($f->remaining_meters, 2) }} {{ __('m') }}</td>
                        <td>{{ number_format($f->low_stock_threshold, 2) }} {{ __('m') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-mail-layout>
