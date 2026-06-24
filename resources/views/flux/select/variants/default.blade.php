@blaze

@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'placeholder' => null,
    'invalid' => null,
    'size' => null,
])

@php
$invalid ??= ($name && $errors->has($name));

$triggerClasses = Flux::classes()
    ->add('appearance-none')
    ->add('[:where(&)]:w-full ps-3 pe-10 block text-left')
    ->add(match ($size) {
        default => 'h-10 py-2 text-base sm:text-sm leading-[1.375rem] rounded-lg',
        'sm' => 'h-8 py-1.5 text-sm leading-[1.125rem] rounded-md',
        'xs' => 'h-6 text-xs leading-[1.125rem] rounded-md',
    })
    ->add('shadow-xs border')
    ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
    ->add('text-zinc-700 dark:text-zinc-300 disabled:text-zinc-500 dark:disabled:text-zinc-400')
    ->add('disabled:shadow-none disabled:cursor-not-allowed')
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    );

$panelClasses = 'absolute z-50 mt-1 w-full rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-600 dark:bg-zinc-800 max-h-60 overflow-auto py-1';
@endphp

<div
    x-data="{
        open: false,
        get selectedValue() {
            return this.$refs.select.value;
        },
        get selectedLabel() {
            const select = this.$refs.select;
            if (!select) return '';
            const opt = select.options[select.selectedIndex];
            if (!opt || (opt.disabled && opt.value === '')) return '';
            return opt.textContent;
        },
        get options() {
            return Array.from(this.$refs.select?.options ?? []).map((o, i) => ({
                value: o.value,
                label: o.textContent,
                disabled: o.disabled,
            }));
        },
        select(value) {
            const select = this.$refs.select;
            if (!select) return;
            select.value = value;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            this.open = false;
        },
        toggle() {
            if (this.$refs.select && !this.$refs.select.disabled) this.open = !this.open;
        },
    }"
    x-on:click.outside="open = false"
    class="relative"
>
    <select
        x-ref="select"
        {{ $attributes->except('class') }}
        style="display: none;"
        @if ($invalid) aria-invalid="true" data-invalid @endif
        @isset ($name) name="{{ $name }}" @endisset
        @if (is_numeric($size)) size="{{ $size }}" @endif
        data-flux-control
        data-flux-select-native
        data-flux-group-target
    >
        <?php if ($placeholder): ?>
            <option value="" disabled selected class="placeholder">{{ $placeholder }}</option>
        <?php endif; ?>
        {{ $slot }}
    </select>

    <button type="button"
        x-on:click="toggle"
        x-bind:disabled="$refs.select?.disabled"
        class="{{ $triggerClasses }} flex items-center justify-between w-full cursor-pointer gap-2"
    >
        <span x-text="selectedLabel || '{{ $placeholder ?? '' }}'" class="truncate"></span>
        <svg class="size-4 shrink-0 text-zinc-400 transition-transform" x-bind:class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="{{ $panelClasses }}"
    >
        <template x-for="(opt, index) in options" :key="opt.value + '-' + index">
            <div
                x-on:click="!opt.disabled && select(opt.value)"
                x-text="opt.label"
                class="px-3 py-2 text-sm cursor-pointer transition-colors"
                x-bind:class="{
                    'bg-zinc-100 dark:bg-zinc-700': selectedValue === opt.value,
                    'text-zinc-400 dark:text-zinc-500': opt.disabled && opt.value === '',
                    'text-zinc-700 dark:text-zinc-200': !opt.disabled || opt.value !== '',
                    'hover:bg-zinc-100 dark:hover:bg-zinc-700': !opt.disabled,
                    'opacity-50 cursor-default': opt.disabled && opt.value !== '',
                }"
            ></div>
        </template>
    </div>
</div>
