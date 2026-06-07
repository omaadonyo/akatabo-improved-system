@props([
    'sidebar' => false,
])

@php
    $brandName = auth()->user()?->business?->name ?? __('Laravel Starter Kit');
    $words = explode(' ', $brandName);
    if (count($words) > 2) {
        $firstLine = implode(' ', array_slice($words, 0, 2));
        $secondLine = implode(' ', array_slice($words, 2));
        $brandNewName = $firstLine . "\n" . $secondLine;
    }
@endphp

@if($sidebar)
    <flux:sidebar.brand :name="$brandNewName" {{ $attributes }}  style="width:210px;">
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$brandNewName" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
