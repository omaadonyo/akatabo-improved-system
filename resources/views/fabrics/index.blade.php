<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'NAKUNDA BUSINESS SOLUTIONS') }} — Premium Curtains & Fabrics</title>
    <link rel="icon" href="{{ asset('logos/favicon.png') }}" type="image/png">
    <link rel="manifest" href="{{ asset('build/manifest.webmanifest') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <meta name="theme-color" content="#09090b">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="mobile-web-app-capable" content="yes">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { scroll-behavior: smooth; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(20, 184, 166, .3); border-radius: 3px; }

        .fade-up {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .fade-up.in-view {
            opacity: 1;
            transform: translateY(0);
        }

        .shimmer {
            background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.04) 50%, transparent 100%);
            background-size: 200% 100%;
            animation: shimmer 3s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .card-hover {
            transition: transform .35s cubic-bezier(.25,.46,.45,.94), box-shadow .35s ease;
        }
        .card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 60px -12px rgba(20, 184, 166, .2);
        }

        .price-input {
            -moz-appearance: textfield;
        }
        .price-input::-webkit-inner-spin-button,
        .price-input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-zinc-950 font-sans text-white antialiased">

    {{-- Background effects --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-48 -top-48 size-[600px] rounded-full bg-teal-500/10 blur-[150px]"></div>
        <div class="absolute -right-48 top-1/3 size-[500px] rounded-full bg-amber-400/8 blur-[140px]"></div>
        <div class="absolute -bottom-48 left-1/4 size-[400px] rounded-full bg-teal-400/6 blur-[120px]"></div>
    </div>

    {{-- Nav --}}
    <nav class="sticky top-0 z-50 border-b border-zinc-800/50 bg-zinc-950/80 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-10">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                <img src="{{ asset('logos/logo-white.png') }}" alt="{{ config('app.name', 'Akatabo') }}" class="h-8 w-auto">
            </a>
            <div class="flex items-center gap-6">
                <a href="#curtains" class="hidden text-sm text-zinc-400 transition hover:text-white sm:block">Curtains</a>
                <a href="#fabrics" class="hidden text-sm text-zinc-400 transition hover:text-white sm:block">Fabrics</a>
                <a href="#how-it-works" class="hidden text-sm text-zinc-400 transition hover:text-white sm:block">How it works</a>
                <a href="#fabrics" class="rounded-lg bg-teal-600 px-4 py-1.5 text-sm font-medium transition hover:bg-teal-500">Get a quote</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="relative overflow-hidden px-6 pb-28 pt-20 lg:px-10 lg:pb-36 lg:pt-28">
        <div class="mx-auto max-w-6xl">
            <div class="flex flex-col items-center text-center">
                <div class="mb-6 inline-flex items-center gap-1.5 rounded-full border border-zinc-700/50 bg-zinc-800/30 px-4 py-1.5 text-xs font-medium text-zinc-400 backdrop-blur-sm">
                    <span class="flex size-2 rounded-full bg-teal-400"></span>
                    Premium curtains & fabrics — made to measure
                </div>
                <h1 class="max-w-4xl text-4xl font-bold leading-[1.1] tracking-tight sm:text-5xl lg:text-6xl">
                    Custom curtains &amp;<br class="hidden sm:block" />
                    <span class="bg-gradient-to-r from-teal-400 via-teal-300 to-amber-300 bg-clip-text text-transparent">premium fabrics</span>
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-zinc-500">
                    From bespoke made-to-measure curtains to high-quality fabrics by the meter — we bring your vision to life. Browse our collection, choose your style, and get a quote in minutes.
                </p>
                <div class="mt-8 flex items-center gap-4">
                    <a href="#curtains" class="rounded-xl bg-teal-600 px-7 py-3 text-sm font-semibold shadow-lg shadow-teal-600/25 transition hover:bg-teal-500 hover:shadow-teal-500/30">Explore curtains</a>
                    <a href="#fabrics" class="rounded-xl border border-zinc-700 bg-zinc-800/40 px-7 py-3 text-sm font-semibold text-zinc-300 backdrop-blur-sm transition hover:border-zinc-500 hover:text-white">Browse fabrics</a>
                </div>
            </div>

            {{-- Trust stats --}}
            <div class="mt-16 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-5">
                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-5 backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white sm:text-3xl">{{ $fabrics->count() }}+</p>
                    <p class="mt-1 text-xs text-zinc-500">Fabrics &amp; Styles</p>
                </div>
                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-5 backdrop-blur-sm">
                    <p class="text-2xl font-bold text-transparent sm:text-3xl"><span class="bg-gradient-to-r from-teal-300 to-amber-300 bg-clip-text text-transparent">Made</span></p>
                    <p class="mt-1 text-xs text-zinc-500">to Measure</p>
                </div>
                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-5 backdrop-blur-sm">
                    <p class="text-2xl font-bold text-white sm:text-3xl">24h</p>
                    <p class="mt-1 text-xs text-zinc-500">Quote Response</p>
                </div>
                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-5 backdrop-blur-sm">
                    <p class="text-2xl font-bold text-transparent sm:text-3xl"><span class="bg-gradient-to-r from-amber-300 to-teal-300 bg-clip-text text-transparent">Nationwide</span></p>
                    <p class="mt-1 text-xs text-zinc-500">Delivery Available</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Curtains Section --}}
    <section id="curtains" class="px-6 pb-28 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 text-center">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-zinc-700/50 bg-zinc-800/30 px-4 py-1.5 text-xs font-medium text-zinc-400 backdrop-blur-sm">
                    Bespoke curtains
                </div>
                <h2 class="text-3xl font-bold sm:text-4xl">Curtains <span class="bg-gradient-to-r from-teal-400 to-amber-400 bg-clip-text text-transparent">made for you</span></h2>
                <p class="mt-3 max-w-xl mx-auto text-sm text-zinc-500">Every space is unique. We design, cut, and sew curtains tailored to your exact measurements and style preferences.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-teal-500/10">
                        <svg class="size-6 text-teal-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18 8 21 11 17 14"/><circle cx="7" cy="11" cx2="3"/><rect x="7" y="9" width="4" height="4"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Made to Measure</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Custom-sized curtains crafted to fit your windows perfectly. Choose from pleated, eyelet, pencil pleat, and more.</p>
                </div>

                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-amber-400/10">
                        <svg class="size-6 text-amber-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v2"/><path d="M12 21v2"/><path d="M4.22 4.22l1.42 1.42"/><path d="M18.36 18.36l1.42 1.42"/><path d="M1 12h2"/><path d="M21 12h2"/><path d="M4.22 19.78l1.42-1.42"/><path d="M18.36 5.64l1.42-1.42"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Vast Fabric Selection</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Hundreds of premium fabrics to choose from — linens, velvets, cottons, blackout, sheer, and more.</p>
                </div>

                <div class="rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-5 flex size-12 items-center justify-center rounded-xl bg-teal-500/10">
                        <svg class="size-6 text-teal-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 12h18"/><path d="M12 3v18"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Professional Installation</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Full measuring, fitting, and installation service available. We handle everything from rail to finish.</p>
                </div>
            </div>

            <div class="mt-10 text-center">
                <a href="#fabrics" class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-7 py-3 text-sm font-semibold shadow-lg shadow-teal-600/25 transition hover:bg-teal-500 hover:shadow-teal-500/30">
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Request a curtain quote
                </a>
            </div>
        </div>
    </section>

    {{-- Fabrics Grid --}}
    <section id="fabrics" class="px-6 pb-28 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 text-center">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-zinc-700/50 bg-zinc-800/30 px-4 py-1.5 text-xs font-medium text-zinc-400 backdrop-blur-sm">
                    By the meter
                </div>
                <h2 class="text-3xl font-bold sm:text-4xl">Premium <span class="bg-gradient-to-r from-teal-400 to-amber-400 bg-clip-text text-transparent">Fabrics</span></h2>
                <p class="mt-3 max-w-xl mx-auto text-sm text-zinc-500">Browse our curated collection of high-quality fabrics. Whether for curtains, upholstery, fashion, or decor — find the perfect material for your project.</p>
            </div>

            @if ($fabrics->isEmpty())
                <div class="rounded-2xl border border-zinc-800 bg-zinc-900/40 p-16 text-center backdrop-blur-sm">
                    <p class="text-zinc-400">No fabrics available yet. Check back soon!</p>
                </div>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($fabrics as $fabric)
                        <div class="card-hover group relative overflow-hidden rounded-2xl border border-zinc-800/60 bg-zinc-900/40 backdrop-blur-sm">
                            {{-- Image --}}
                            <div class="relative aspect-[4/3] overflow-hidden bg-zinc-800">
                                @if ($fabric->image && Storage::disk('public')->exists($fabric->image))
                                    <img src="{{ Storage::url($fabric->image) }}" alt="{{ $fabric->name }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                                @else
                                    <div class="flex size-full items-center justify-center bg-gradient-to-br from-zinc-800 to-zinc-900">
                                        <svg class="size-12 text-zinc-700" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                                    </div>
                                @endif
                                @if ($fabric->color)
                                    <div class="absolute left-3 top-3 flex items-center gap-1.5 rounded-full bg-black/50 px-2.5 py-1 text-xs text-white backdrop-blur-sm">
                                        <span class="size-2.5 rounded-full" style="background-color: {{ $fabric->color }};"></span>
                                        {{ $fabric->color }}
                                    </div>
                                @endif
                                @if ($fabric->remaining_meters !== null && $fabric->remaining_meters <= $fabric->low_stock_threshold)
                                    <div class="absolute right-3 top-3 rounded-full bg-amber-500/20 px-2.5 py-1 text-xs font-medium text-amber-400 backdrop-blur-sm">
                                        Low stock
                                    </div>
                                @endif
                            </div>

                            {{-- Details --}}
                            <div class="p-5">
                                <div class="mb-1 flex items-start justify-between gap-2">
                                    <h3 class="text-base font-semibold text-white">{{ $fabric->name }}</h3>
                                    @if ($fabric->roll_code)
                                        <span class="shrink-0 font-mono text-[10px] text-zinc-600">{{ strtoupper($fabric->roll_code) }}</span>
                                    @endif
                                </div>

                                <div class="mt-4 flex items-end justify-between">
                                    <div>
                                        <p class="text-lg font-bold text-white">UGX {{ number_format($fabric->selling_price_per_meter, 0) }}</p>
                                        <p class="text-xs text-zinc-500">per meter</p>
                                    </div>
                                </div>

                                <a href="{{ route('fabrics.quote', $fabric) }}"
                                   class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-teal-600/10 px-4 py-2.5 text-sm font-medium text-teal-400 ring-1 ring-teal-600/20 transition hover:bg-teal-600 hover:text-white hover:shadow-lg hover:shadow-teal-600/20">
                                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    Request Quote
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- How it works --}}
    <section id="how-it-works" class="px-6 pb-28 lg:px-10">
        <div class="mx-auto max-w-6xl">
            <div class="mb-12 text-center">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full border border-zinc-700/50 bg-zinc-800/30 px-4 py-1.5 text-xs font-medium text-zinc-400 backdrop-blur-sm">
                    Simple process
                </div>
                <h2 class="text-3xl font-bold sm:text-4xl"><span class="bg-gradient-to-r from-teal-400 to-amber-400 bg-clip-text text-transparent">How it</span> works</h2>
                <p class="mt-3 text-sm text-zinc-500">From selection to delivery — we make it easy.</p>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="relative rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-teal-500/10">
                        <span class="text-lg font-bold text-teal-400">01</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Browse & Select</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Explore our collection of premium fabrics and curtain styles. Choose what speaks to your space.</p>
                </div>

                <div class="relative rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-amber-400/10">
                        <span class="text-lg font-bold text-amber-400">02</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Tell Us Your Needs</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Enter your measurements and preferences. Our system calculates the price instantly.</p>
                </div>

                <div class="relative rounded-2xl border border-zinc-800/50 bg-zinc-900/30 p-6 backdrop-blur-sm">
                    <div class="mb-4 flex size-12 items-center justify-center rounded-xl bg-teal-500/10">
                        <span class="text-lg font-bold text-teal-400">03</span>
                    </div>
                    <h3 class="text-lg font-semibold text-white">Get Your Quote</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-500">Submit your request and receive an official quotation via email within 24 hours.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="px-6 pb-28 lg:px-10">
        <div class="mx-auto max-w-4xl">
            <div class="relative overflow-hidden rounded-3xl border border-zinc-800/60 bg-gradient-to-br from-zinc-900 to-zinc-950 p-10 text-center sm:p-16">
                <div class="pointer-events-none absolute -right-20 -top-20 size-60 rounded-full bg-teal-500/10 blur-[80px]"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 size-60 rounded-full bg-amber-400/8 blur-[80px]"></div>
                <h2 class="text-2xl font-bold sm:text-3xl">Ready to get started?</h2>
                <p class="mt-3 text-sm text-zinc-500">Request a free quotation today. No commitment, no hassle — just quality craftsmanship.</p>
                <div class="mt-8 flex items-center justify-center gap-4">
                    <a href="#curtains" class="rounded-xl bg-teal-600 px-7 py-3 text-sm font-semibold shadow-lg shadow-teal-600/25 transition hover:bg-teal-500 hover:shadow-teal-500/30">Explore curtains</a>
                    <a href="#fabrics" class="rounded-xl border border-zinc-700 bg-zinc-800/40 px-7 py-3 text-sm font-semibold text-zinc-300 backdrop-blur-sm transition hover:border-zinc-500 hover:text-white">Browse fabrics</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-t border-zinc-800/50 px-6 py-8 lg:px-10">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 sm:flex-row">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logos/logo-white.png') }}" alt="{{ config('app.name', 'Akatabo') }}" class="h-6 w-auto opacity-60">
                <p class="text-xs text-zinc-600">&copy; {{ date('Y') }} {{ config('app.name', 'Akatabo') }}. All rights reserved.</p>
            </div>
            <div class="flex items-center gap-6 text-xs text-zinc-600">
                <a href="#curtains" class="transition hover:text-zinc-400">Curtains</a>
                <a href="#fabrics" class="transition hover:text-zinc-400">Fabrics</a>
                <a href="#how-it-works" class="transition hover:text-zinc-400">How it works</a>
                <a href="{{ route('login') }}" class="transition hover:text-zinc-400">Admin login</a>
            </div>
        </div>
    </footer>

    {{-- Intersection Observer for fade-up --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('in-view');
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/build/sw.js');
            });
        }
    </script>
</body>
</html>
