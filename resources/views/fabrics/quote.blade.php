<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Akatabo') }} — Request Quote</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, .3); border-radius: 3px; }

        input, textarea {
            -moz-appearance: textfield;
        }
        input::-webkit-inner-spin-button,
        input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-zinc-950 font-sans text-white antialiased">

    {{-- Background --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-48 -top-48 size-[500px] rounded-full bg-indigo-500/8 blur-[140px]"></div>
        <div class="absolute -bottom-48 right-1/4 size-[400px] rounded-full bg-amber-500/6 blur-[120px]"></div>
    </div>

    {{-- Nav --}}
    <nav class="border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <div class="flex size-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-indigo-600">
                    <svg class="size-4 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                </div>
                <span class="text-base font-bold tracking-tight">{{ config('app.name', 'Akatabo') }}</span>
            </a>
            <a href="{{ route('fabrics.index') }}" class="text-sm text-zinc-400 transition hover:text-white">&larr; Back to fabrics</a>
        </div>
    </nav>

    <div class="mx-auto max-w-5xl px-6 py-12 lg:px-10">

        {{-- Success message --}}
        @if (session('success'))
            <div class="mb-8 rounded-2xl border border-emerald-800/40 bg-emerald-900/20 p-6 text-center backdrop-blur-sm">
                <div class="mx-auto mb-3 flex size-14 items-center justify-center rounded-full bg-emerald-500/20">
                    <svg class="size-7 text-emerald-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h2 class="text-lg font-semibold text-emerald-300">Quotation Request Submitted!</h2>
                <p class="mt-1 text-sm text-zinc-400">{{ session('success') }}</p>
                <a href="{{ route('fabrics.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-400 transition hover:text-indigo-300">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><polyline points="12 19 5 12 12 5"/></svg>
                    Browse more fabrics
                </a>
            </div>
        @endif

        <div class="grid gap-10 lg:grid-cols-5 lg:gap-12">

            {{-- Left: Fabric Preview --}}
            <div class="lg:col-span-2">
                <div class="sticky top-24">
                    <div class="overflow-hidden rounded-2xl border border-zinc-800/60 bg-zinc-900/40 backdrop-blur-sm">
                        <div class="aspect-[4/3] overflow-hidden bg-zinc-800">
                            @if ($fabric->image && Storage::disk('public')->exists($fabric->image))
                                <img src="{{ Storage::url($fabric->image) }}" alt="{{ $fabric->name }}" class="size-full object-cover" />
                            @else
                                <div class="flex size-full items-center justify-center bg-gradient-to-br from-zinc-800 to-zinc-900">
                                    <svg class="size-16 text-zinc-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h2 class="text-xl font-bold text-white">{{ $fabric->name }}</h2>
                            @if ($fabric->color || $fabric->roll_code)
                                <div class="mt-2 flex flex-wrap gap-3">
                                    @if ($fabric->color)
                                        <span class="flex items-center gap-1.5 text-xs text-zinc-400">
                                            <span class="size-3 rounded-full" style="background-color: {{ $fabric->color }};"></span>
                                            {{ $fabric->color }}
                                        </span>
                                    @endif
                                    @if ($fabric->roll_code)
                                        <span class="font-mono text-[11px] text-zinc-600">{{ $fabric->roll_code }}</span>
                                    @endif
                                </div>
                            @endif
                            <div class="mt-4 flex items-baseline gap-1.5">
                                <span class="text-2xl font-bold text-white">UGX {{ number_format($fabric->selling_price_per_meter, 0) }}</span>
                                <span class="text-sm text-zinc-500">/ meter</span>
                            </div>
                            @if ($fabric->width)
                                <p class="mt-1 text-xs text-zinc-600">Width: {{ $fabric->width }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right: Quote Form --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border border-zinc-800/60 bg-zinc-900/40 p-6 backdrop-blur-sm sm:p-8">
                    <h2 class="text-lg font-semibold text-white">Request a Quotation</h2>
                    <p class="mt-1 text-sm text-zinc-500">Fill in your details and measurements below. The total updates automatically.</p>

                    <form method="POST" action="{{ route('fabrics.submit') }}" class="mt-6 space-y-5" x-data="{
                        pricePerMeter: {{ $fabric->selling_price_per_meter }},
                        length: '',
                        width: '',
                        get total() {
                            return (parseFloat(this.length) || 0) * this.pricePerMeter;
                        },
                        get isValid() {
                            return parseFloat(this.length) > 0;
                        }
                    }">
                        @csrf
                        <input type="hidden" name="fabric_id" value="{{ $fabric->id }}">

                        {{-- Customer info --}}
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Your Name <span class="text-red-400">*</span></label>
                                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                                    class="w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="e.g. Jane Doe" />
                                @error('customer_name') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Email <span class="text-red-400">*</span></label>
                                <input type="email" name="customer_email" value="{{ old('customer_email') }}" required
                                    class="w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="jane@example.com" />
                                @error('customer_email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-zinc-300">Phone</label>
                                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                                    class="w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                    placeholder="+256 700 000 000" />
                                @error('customer_phone') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        {{-- Measurements --}}
                        <div class="border-t border-zinc-800/50 pt-5">
                            <h3 class="text-sm font-semibold text-zinc-300">Measurements</h3>
                            <p class="text-xs text-zinc-600">Enter the amount of fabric you need.</p>

                            <div class="mt-4 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Length (meters) <span class="text-red-400">*</span></label>
                                    <input type="number" name="length_meters" x-model="length" step="0.01" min="0.01" required
                                        class="price-input w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                        placeholder="e.g. 5" />
                                    @error('length_meters') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-zinc-300">Width (meters)</label>
                                    <input type="number" name="width_meters" x-model="width" step="0.01" min="0.01"
                                        class="price-input w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                        placeholder="e.g. 1.5" />
                                    @error('width_meters') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            {{-- Price summary --}}
                            <div class="mt-5 rounded-xl border border-zinc-700/40 bg-zinc-800/30 p-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-zinc-400">UGX {{ number_format($fabric->selling_price_per_meter, 0) }} × <span x-text="parseFloat(length) || 0" class="font-mono"></span>m</span>
                                    <span class="text-zinc-500">=</span>
                                    <span class="text-lg font-bold text-indigo-400" x-text="'UGX ' + total.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})">UGX 0</span>
                                </div>
                                <template x-if="!isValid">
                                    <p class="mt-2 text-xs text-zinc-600">Enter a length to see the estimated total.</p>
                                </template>
                            </div>
                        </div>

                        {{-- Message --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-zinc-300">Additional Notes</label>
                            <textarea name="customer_message" rows="3"
                                class="w-full rounded-xl border border-zinc-700/60 bg-zinc-800/40 px-4 py-2.5 text-sm text-white placeholder-zinc-500 outline-none transition focus:border-indigo-500/50 focus:bg-zinc-800/80 focus:ring-2 focus:ring-indigo-500/20"
                                placeholder="Any specific requirements...">{{ old('customer_message') }}</textarea>
                            @error('customer_message') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit"
                            class="w-full rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition hover:from-indigo-500 hover:to-indigo-400 hover:shadow-indigo-500/30">
                            Submit Quotation Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <footer class="border-t border-zinc-800/50 px-6 py-6 lg:px-10">
        <div class="mx-auto flex max-w-5xl items-center justify-between">
            <p class="text-xs text-zinc-600">&copy; {{ date('Y') }} {{ config('app.name', 'Akatabo') }}. All rights reserved.</p>
            <a href="{{ route('fabrics.index') }}" class="text-xs text-zinc-600 transition hover:text-zinc-400">All fabrics</a>
        </div>
    </footer>

</body>
</html>
