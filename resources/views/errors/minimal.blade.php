<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code') | {{ config('app.name', 'سامانه آگهی') }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:Vazirmatn,Tahoma,'Segoe UI',sans-serif;background:#f4f6f7;color:#1f2937;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:2rem 1rem;text-align:center}
        .brand{display:flex;align-items:center;gap:.55rem;text-decoration:none;color:#4b4dbe;font-weight:800;font-size:1.15rem;margin-bottom:2rem}
        .brand .mark{display:flex;align-items:center;justify-content:center;width:2.25rem;height:2.25rem;border-radius:.625rem;background:#4b4dbe;color:#fff}
        code.c{display:block;font-size:3.5rem;font-weight:800;color:#4b4dbe;line-height:1;letter-spacing:.05em}
        h1{font-size:1.25rem;margin:1rem 0 .5rem}
        p{color:#6b7280;font-size:.95rem;max-width:34rem;line-height:1.9}
        .actions{margin-top:1.75rem;display:flex;gap:.75rem;flex-wrap:wrap;justify-content:center}
        .btn{display:inline-block;background:#4b4dbe;color:#fff;text-decoration:none;font-size:.9rem;font-weight:600;padding:.65rem 1.4rem;border-radius:.625rem}
        .btn--ghost{background:#fff;color:#4b4dbe;border:1px solid #d6d7f0}
    </style>
</head>
<body>
    <a class="brand" href="{{ url('/') }}"><span class="mark">آ</span><span>{{ config('app.name', 'سامانه آگهی') }}</span></a>
    <code class="c">@yield('code')</code>
    <h1>@yield('title')</h1>
    <p>@yield('message')</p>
    <div class="actions">
        <a class="btn" href="{{ url('/') }}">صفحهٔ اصلی</a>
        <a class="btn btn--ghost" href="{{ url('/search') }}">جست‌وجوی آگهی‌ها</a>
    </div>
</body>
</html>
