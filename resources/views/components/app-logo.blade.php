@props([
    'sidebar' => false,
])

@php
    $brandName = activeBusiness()?->name ?? config('app.name', 'NAKUNDA BUSINESS SOLUTIONS');
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$brandName" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('logos/favicon.png') }}" alt="{{ $brandName }}" class="size- object-contain" style="width: 27px;">
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$brandName" {{ $attributes }}>
        <x-slot name="logo">
            <img src="{{ asset('logos/favicon.png') }}" alt="{{ $brandName }}" class="h- w-auto" style="width: 27px;">
        </x-slot>
    </flux:brand>
@endif
