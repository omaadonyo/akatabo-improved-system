@props([
    'title' => '',
    'business' => null,
])

@php
    $biz = $business ?? activeBusiness();
    $brand = $biz?->name ?? config('app.name');
    $logoUrl = $biz?->logo ? url(Storage::url($biz->logo)) : null;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ? $title . ' — ' . $brand : $brand }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f4f5f7;
            color: #1a1a2e;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper { padding: 40px 16px; }
        .container {
            max-width: 560px; margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.04);
        }
        .header {
            padding: 32px 40px 20px;
            text-align: center;
            border-bottom: 2px solid #f0f0f5;
        }
        .header .logo {
            max-height: 56px;
            width: auto;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            letter-spacing: -0.3px;
        }
        .header .accent {
            display: inline-block;
            width: 40px;
            height: 3px;
            background: linear-gradient(90deg, #6366f1, #8b5cf6);
            border-radius: 2px;
            margin-top: 8px;
        }
        .body { padding: 32px 40px; }
        .body h2 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            color: #1a1a2e;
        }
        .body p {
            font-size: 15px;
            color: #4a4a6a;
            margin-bottom: 12px;
        }
        .body p:last-child { margin-bottom: 0; }
        .details {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 16px 0;
            font-size: 14px;
        }
        .details .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e8e8ee;
        }
        .details .row:last-child { border-bottom: none; }
        .details .row .label { color: #6b6b8a; font-size: 13px; }
        .details .row .value { font-weight: 600; color: #1a1a2e; }
        .footer {
            padding: 24px 40px;
            text-align: center;
            font-size: 12px;
            color: #8a8aaa;
            border-top: 1px solid #f0f0f5;
        }
        .footer .contact { margin-bottom: 8px; }
        .footer .contact p { margin-bottom: 2px; color: #8a8aaa; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; margin: 12px 0; }
        th { text-align: left; padding: 8px 12px; background: #f8f9fc; font-weight: 600; color: #1a1a2e; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 8px 12px; border-bottom: 1px solid #f0f0f5; color: #4a4a6a; }
        td:last-child, th:last-child { text-align: right; }
        .total-row td { font-weight: 700; color: #1a1a2e; border-bottom: none; padding-top: 12px; }
        .admin-badge {
            display: inline-block;
            padding: 4px 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6366f1;
            background: #eef2ff;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        @media only screen and (max-width: 480px) {
            .wrapper { padding: 16px 8px; }
            .header { padding: 24px 20px 16px; }
            .body { padding: 24px 20px; }
            .footer { padding: 16px 20px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brand }}" class="logo">
                @else
                    <h1>{{ $brand }}</h1>
                @endif
                <div class="accent"></div>
            </div>
            <div class="body">
                {{ $slot }}
            </div>
            <div class="footer">
                <div class="contact">
                    <p style="font-weight: 600; color: #1a1a2e;">{{ $brand }}</p>
                    @if ($biz?->email)
                        <p>{{ $biz->email }}</p>
                    @endif
                    @if ($biz?->address)
                        <p>{{ $biz->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>
</html>
