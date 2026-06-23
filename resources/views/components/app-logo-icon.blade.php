@php
    $name = activeBusiness()?->name ?? 'A';
    $words = explode(' ', trim($name));
    $initials = count($words) >= 2
        ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
        : strtoupper(substr($name, 0, 2));
@endphp
<svg {{ $attributes->merge(['viewBox' => '0 0 32 32', 'xmlns' => 'http://www.w3.org/2000/svg']) }}>
    <text x="16" y="16" text-anchor="middle" dominant-baseline="central" font-family="system-ui,-apple-system,sans-serif" font-weight="700" font-size="15" letter-spacing="0.5" fill="currentColor">{{ $initials }}</text>
</svg>
