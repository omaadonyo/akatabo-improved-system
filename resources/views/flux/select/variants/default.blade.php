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
    ->add('outline-none')
    ->add($invalid
        ? 'border-red-500'
        : 'border-zinc-200 border-b-zinc-300/80 dark:border-white/10'
    );

$panelClasses = 'absolute z-50 mt-1.5 w-full rounded-xl border border-zinc-200 bg-white shadow-lg shadow-black/5 dark:border-zinc-600 dark:bg-zinc-800 origin-top';
@endphp

<div
    x-data="{
        open: false,
        search: '',
        highlightedValue: null,

        get selectedValue() {
            return this.$refs.select.value;
        },
        get selectedLabel() {
            const select = this.$refs.select;
            if (!select) return '';
            const opt = select.options[select.selectedIndex];
            if (!opt || (opt.disabled && opt.value === '')) return '';
            return opt.textContent.trim();
        },
        get allOptions() {
            return Array.from(this.$refs.select?.options ?? []).map((o, i) => ({
                value: o.value,
                label: o.textContent.trim(),
                disabled: o.disabled,
                isPlaceholder: o.disabled && o.value === '',
            }));
        },
        get processedOptions() {
            return this.allOptions.map((opt, i) => ({
                ...opt,
                hidden: this.isHidden(opt, i),
            }));
        },
        isHidden(opt, idx) {
            if (!this.search) return false;
            if (opt.isPlaceholder) return false;
            const q = this.search.toLowerCase();
            if (opt.disabled) {
                return !this.allOptions.slice(idx + 1).some(
                    o => !o.disabled && o.label.toLowerCase().includes(q)
                );
            }
            return !opt.label.toLowerCase().includes(q);
        },
        getSelectable() {
            return this.processedOptions.filter(o => !o.hidden && !o.disabled && !o.isPlaceholder);
        },
        get hasResults() {
            return this.getSelectable().length > 0;
        },
        select(value) {
            const select = this.$refs.select;
            if (!select || select.disabled) return;
            select.value = value;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            this.search = '';
            this.highlightedValue = null;
            this.open = false;
        },
        openDropdown() {
            if (this.$refs.select && !this.$refs.select.disabled) {
                this.open = true;
                this.search = '';
                this.highlightedValue = null;
                this.$nextTick(() => {
                    const el = this.$el.querySelector('[data-cs-search]');
                    if (el) el.focus();
                });
            }
        },
        toggle() {
            if (this.open) { this.open = false; return; }
            this.openDropdown();
        },
        onKeydown(e) {
            if (!this.open) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.openDropdown();
                }
                return;
            }
            switch (e.key) {
                case 'Escape':
                    e.preventDefault();
                    this.open = false;
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.highlightNext();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.highlightPrev();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.highlightedValue) {
                        this.select(this.highlightedValue);
                    }
                    break;
                case 'Tab':
                    this.open = false;
                    break;
            }
        },
        highlightNext() {
            const items = this.getSelectable();
            if (items.length === 0) return;
            if (!this.highlightedValue) {
                this.highlightedValue = items[0].value;
            } else {
                const idx = items.findIndex(o => o.value === this.highlightedValue);
                this.highlightedValue = items[Math.min(idx + 1, items.length - 1)].value;
            }
            this.scrollToHighlight();
        },
        highlightPrev() {
            const items = this.getSelectable();
            if (items.length === 0) return;
            if (!this.highlightedValue) {
                this.highlightedValue = items[items.length - 1].value;
            } else {
                const idx = items.findIndex(o => o.value === this.highlightedValue);
                this.highlightedValue = items[Math.max(idx - 1, 0)].value;
            }
            this.scrollToHighlight();
        },
        scrollToHighlight() {
            this.$nextTick(() => {
                const el = this.$el.querySelector('[data-highlighted]');
                if (el) el.scrollIntoView({ block: 'nearest' });
            });
        },
        isHighlighted(opt) {
            return !opt.disabled && !opt.isPlaceholder && opt.value === this.highlightedValue;
        },
    }"
    x-on:click.outside="open = false"
    x-on:keydown="onKeydown"
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
        x-bind:class="{ 'ring-2 ring-accent ring-offset-2 ring-offset-accent-foreground': open }"
        class="{{ $triggerClasses }} flex items-center justify-between w-full cursor-pointer gap-2"
    >
        <span x-text="selectedLabel || '{{ $placeholder ?? '' }}'" class="truncate" x-bind:class="{ 'text-zinc-400': !selectedValue }"></span>
        <svg class="size-4 shrink-0 text-zinc-400 transition-transform duration-200" x-bind:class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        x-cloak
        class="{{ $panelClasses }}"
    >
        {{-- Search --}}
        <div class="p-2 pb-0">
            <div class="flex items-center gap-1.5 rounded-lg border border-zinc-200 bg-zinc-50 px-2.5 dark:border-zinc-600 dark:bg-zinc-700/50">
                <svg class="size-4 shrink-0 text-zinc-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input data-cs-search type="text" x-model="search"
                    placeholder="{{ __('Search...') }}"
                    class="w-full border-0 bg-transparent py-2 text-sm text-zinc-700 outline-none placeholder:text-zinc-400 dark:text-zinc-200 dark:placeholder:text-zinc-500"
                    x-on:click.stop
                    x-on:keydown.arrow-down.stop.prevent="highlightNext()"
                    x-on:keydown.arrow-up.stop.prevent="highlightPrev()"
                    x-on:keydown.enter.stop.prevent="highlightedValue && select(highlightedValue)"
                    x-on:keydown.escape.stop.prevent="open = false"
                />
                <button x-show="search" x-on:click="search = ''" class="shrink-0 rounded p-0.5 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                    <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
        </div>

        {{-- Options --}}
        <div class="max-h-56 overflow-auto py-1" x-ref="list">
            <template x-for="(opt, idx) in processedOptions" :key="opt.value + '-' + idx">
                <div x-show="!opt.hidden">
                    {{-- Group header --}}
                    <div x-show="opt.disabled && !opt.isPlaceholder"
                        class="px-3 pb-1 pt-3 text-[11px] font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500"
                        x-text="opt.label.replace(/^[─\s─]+|[─\s─]+$/g, '')"
                    ></div>

                    {{-- Selectable option --}}
                    <div x-show="!opt.disabled || opt.isPlaceholder"
                        x-on:click="(!opt.disabled || opt.isPlaceholder) && select(opt.value)"
                        x-bind:data-highlighted="isHighlighted(opt) || undefined"
                        class="mx-1 flex cursor-pointer items-center justify-between rounded-md px-2.5 py-2 text-sm transition-colors duration-75"
                        x-bind:class="{
                            'bg-accent text-accent-foreground': isHighlighted(opt),
                            'bg-zinc-100 dark:bg-zinc-700': !isHighlighted(opt) && !opt.disabled && selectedValue === opt.value,
                            'text-zinc-700 dark:text-zinc-200': !opt.disabled,
                            'text-zinc-400 dark:text-zinc-500': opt.isPlaceholder,
                            'hover:bg-zinc-50 dark:hover:bg-zinc-700/50': !isHighlighted(opt) && !opt.disabled,
                        }"
                    >
                        <span x-text="opt.isPlaceholder ? '{{ $placeholder ?? '' }}' : opt.label" class="truncate"></span>
                        <svg x-show="!opt.disabled && selectedValue === opt.value" class="size-4 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                </div>
            </template>

            {{-- No results --}}
            <div x-show="!hasResults" class="px-3 py-8 text-center text-sm text-zinc-400 dark:text-zinc-500">
                {{ __('No results found') }}
            </div>
        </div>
    </div>
</div>
