<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>
    {{ filled($title ?? null) ? $title.' - '.config('app.name', 'NAKUNDA BUSINESS SOLUTIONS') : config('app.name', 'Laravel') }}
</title>

<link rel="icon" href="{{ asset('logos/favicon.png') }}" type="image/png">


@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
