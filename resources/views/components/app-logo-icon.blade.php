@php
    $name = activeBusiness()?->name ?? 'A';
    $words = explode(' ', trim($name));
    $initials = count($words) >= 2
        ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
        : strtoupper(substr($name, 0, 2));
@endphp
<svg {{ $attributes->merge(['viewBox' => '0 0 40 40', 'xmlns' => 'http://www.w3.org/2000/svg', 'class' => 'size-5']) }}>
    <rect width="40" height="40" rx="10" ry="10" fill="currentColor" />
    <text x="20" y="20" text-anchor="middle" dominant-baseline="central" font-family="system-ui,-apple-system,sans-serif" font-weight="700" font-size="18" letter-spacing="1" fill="rgb(0,0,0)" class="dark:fill-white">{{ $initials }}</text>
</svg>
