@php
    $name = activeBusiness()?->name ?? 'A';
    $words = explode(' ', trim($name));
    $initials = count($words) >= 2
        ? strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1))
        : strtoupper(substr($name, 0, 2));
@endphp
<span {{ $attributes->merge(['class' => 'flex items-center justify-center text-[10px] font-bold leading-none']) }}>{{ $initials }}</span>
