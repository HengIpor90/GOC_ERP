<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="OMNIHUB company management system for products, inventory, sales, orders, customers, and teams.">

    <title>{{ config('app.name', 'OMNIHUB') }} — Company ERP</title>

    <style>
        :root {
            --background: #070b18;
            --surface: rgba(255, 255, 255, .055);
            --surface-strong: #11182b;
            --border: rgba(255, 255, 255, .11);
            --text: #f8fafc;
            --muted: #94a3b8;
            --indigo: #6366f1;
            --cyan: #22d3ee;
            --green: #34d399;
            --amber: #fbbf24;
            --shadow: 0 30px 90px rgba(0, 0, 0, .42);
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text);
            background: var(--background);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }
        .container { width: min(1180px, calc(100% - 40px)); margin-inline: auto; }

        .page-background {
            position: fixed;
            inset: 0;
            z-index: -2;
            overflow: hidden;
            pointer-events: none;
        }

        .page-background::after {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .055;
            background-image:
                linear-gradient(rgba(255,255,255,.65) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.65) 1px, transparent 1px);
            background-size: 58px 58px;
            mask-image: linear-gradient(to bottom, black, transparent 85%);
        }

        .orb {
            position: absolute;
            width: 520px;
            aspect-ratio: 1;
            border-radius: 50%;
            filter: blur(90px);
            opacity: .18;
            animation: drift 12s ease-in-out infinite;
        }

        .orb-one { top: -120px; left: -180px; background: var(--indigo); }
        .orb-two { top: 30%; right: -240px; background: var(--cyan); animation-delay: -5s; }

        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            border-bottom: 1px solid var(--border);
            background: rgba(7, 11, 24, .78);
            backdrop-filter: blur(18px);
        }

        .nav { min-height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 24px; }
        .brand { display: flex; align-items: center; gap: 12px; }

        .brand-mark {
            display: grid;
            width: 44px;
            height: 44px;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--indigo), var(--cyan));
            box-shadow: 0 12px 30px rgba(99, 102, 241, .35);
            font-weight: 900;
        }

        .brand-name { display: block; font-size: 15px; font-weight: 900; letter-spacing: .16em; }
        .brand-subtitle { display: block; color: var(--muted); font-size: 11px; letter-spacing: .04em; }
        .nav-links, .nav-actions { display: flex; align-items: center; gap: 8px; }
        .nav-links { gap: 30px; }
        .nav-link { color: #cbd5e1; font-size: 14px; transition: color .2s ease; }
        .nav-link:hover { color: white; }

        .button {
            display: inline-flex;
            min-height: 46px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 20px;
            border: 1px solid transparent;
            border-radius: 14px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 800;
            transition: transform .2s ease, background .2s ease, box-shadow .2s ease;
        }

        .button:hover { transform: translateY(-2px); }
        .button-primary { background: white; color: #0f172a; box-shadow: 0 14px 35px rgba(255, 255, 255, .12); }
        .button-indigo { color: white; background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 16px 40px rgba(79, 70, 229, .32); }
        .button-ghost { color: #e2e8f0; border-color: var(--border); background: rgba(255, 255, 255, .04); }

        #menu-toggle { position: absolute; opacity: 0; pointer-events: none; }
        .menu-button { display: none; width: 44px; height: 44px; place-items: center; border: 1px solid var(--border); border-radius: 12px; cursor: pointer; }
        .menu-button span, .menu-button::before, .menu-button::after { content: ""; width: 20px; height: 2px; background: white; transition: .2s ease; }
        .menu-button { flex-direction: column; gap: 5px; }
        .mobile-menu { display: none; }

        .hero {
            display: grid;
            min-height: 760px;
            grid-template-columns: 1.05fr .95fr;
            align-items: center;
            gap: 72px;
            padding-block: 90px;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            padding: 8px 14px;
            color: #c7d2fe;
            border: 1px solid rgba(129, 140, 248, .24);
            border-radius: 999px;
            background: rgba(99, 102, 241, .1);
            font-size: 13px;
            font-weight: 700;
            animation: rise .75s ease both;
        }

        .eyebrow-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 0 6px rgba(52, 211, 153, .1); }

        h1 {
            max-width: 730px;
            margin: 24px 0 0;
            font-size: clamp(48px, 6vw, 78px);
            line-height: 1.03;
            letter-spacing: -.055em;
            animation: rise .75s .12s ease both;
        }

        .gradient-text {
            display: block;
            color: transparent;
            background: linear-gradient(90deg, #818cf8, #67e8f9, #6ee7b7);
            background-clip: text;
            -webkit-background-clip: text;
        }

        .hero-description { max-width: 650px; margin: 26px 0 0; color: #b7c2d3; font-size: 18px; animation: rise .75s .24s ease both; }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 34px; animation: rise .75s .32s ease both; }
        .hero-checks { display: flex; flex-wrap: wrap; gap: 20px; margin-top: 30px; color: var(--muted); font-size: 13px; }
        .hero-checks span { display: inline-flex; align-items: center; gap: 8px; }
        .check { display: grid; width: 18px; height: 18px; place-items: center; color: #052e24; border-radius: 50%; background: var(--green); font-size: 11px; font-weight: 900; }

        .dashboard-wrap { position: relative; animation: rise .8s .25s ease both; }
        .dashboard {
            position: relative;
            padding: 12px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 30px;
            background: rgba(255, 255, 255, .065);
            box-shadow: var(--shadow);
            backdrop-filter: blur(18px);
            animation: float 6s ease-in-out infinite;
        }

        .dashboard-inner { padding: 25px; border: 1px solid var(--border); border-radius: 22px; background: #0e1426; }
        .dashboard-head, .section-head, .chart-head, .role-row { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
        .overline { color: #64748b; font-size: 10px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
        .dashboard-title { margin: 2px 0 0; font-size: 16px; }
        .live { padding: 5px 10px; color: #6ee7b7; border-radius: 999px; background: rgba(52, 211, 153, .1); font-size: 11px; font-weight: 800; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 24px; }
        .stat { min-width: 0; padding: 15px; border: 1px solid var(--border); border-radius: 14px; background: rgba(255, 255, 255, .035); }
        .stat-accent { width: 30px; height: 6px; margin-bottom: 12px; border-radius: 999px; background: var(--indigo); }
        .stat:nth-child(2) .stat-accent { background: var(--cyan); }
        .stat:nth-child(3) .stat-accent { background: var(--green); }
        .stat strong { display: block; font-size: clamp(17px, 2.4vw, 23px); line-height: 1.1; }
        .stat small { color: #64748b; font-size: 10px; }

        .chart { margin-top: 14px; padding: 17px; border: 1px solid var(--border); border-radius: 14px; background: rgba(255, 255, 255, .035); }
        .chart-head { font-size: 12px; font-weight: 700; }
        .chart-growth { color: var(--green); }
        .bars { display: flex; height: 125px; align-items: end; gap: 7px; margin-top: 18px; }
        .bar { flex: 1; min-width: 5px; border-radius: 5px 5px 1px 1px; background: linear-gradient(to top, #4f46e5, #67e8f9); opacity: .82; transform-origin: bottom; animation: grow .8s ease both; }
        .bar:nth-child(2n) { animation-delay: .08s; }
        .bar:nth-child(3n) { animation-delay: .16s; }
        .alerts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; }
        .alert { padding: 13px; border-radius: 13px; background: rgba(251, 191, 36, .09); }
        .alert:last-child { background: rgba(167, 139, 250, .09); }
        .alert small { display: block; color: #fcd34d; }
        .alert:last-child small { color: #c4b5fd; }
        .alert strong { font-size: 13px; }

        .floating-badge { position: absolute; z-index: 2; padding: 13px 15px; border: 1px solid var(--border); border-radius: 16px; background: rgba(17, 24, 43, .9); box-shadow: 0 20px 45px rgba(0, 0, 0, .3); backdrop-filter: blur(14px); font-size: 11px; font-weight: 800; animation: float 5s -1s ease-in-out infinite; }
        .badge-left { top: 17%; left: -35px; }
        .badge-right { right: -25px; bottom: -20px; animation-duration: 7s; }
        .badge-icon { display: block; margin-bottom: 4px; color: var(--cyan); font-size: 18px; }

        .trust-strip { border-block: 1px solid var(--border); background: rgba(255, 255, 255, .025); }
        .trust-grid { display: grid; grid-template-columns: repeat(4, 1fr); }
        .trust-item { padding: 27px 20px; text-align: center; border-right: 1px solid var(--border); }
        .trust-item:last-child { border-right: 0; }
        .trust-item strong { display: block; font-size: 18px; }
        .trust-item small { color: var(--muted); }

        .section { padding-block: 105px; }
        .section-copy { max-width: 670px; margin: 0 auto 52px; text-align: center; }
        .section-label { color: #a5b4fc; font-size: 12px; font-weight: 900; letter-spacing: .18em; text-transform: uppercase; }
        .section-title { margin: 12px 0 0; font-size: clamp(34px, 5vw, 52px); line-height: 1.12; letter-spacing: -.035em; }
        .section-description { margin: 18px 0 0; color: var(--muted); }

        .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; }
        .feature-card { position: relative; overflow: hidden; padding: 28px; border: 1px solid var(--border); border-radius: 24px; background: var(--surface); transition: transform .25s ease, border .25s ease, background .25s ease; }
        .feature-card:hover { transform: translateY(-8px); border-color: rgba(129, 140, 248, .4); background: rgba(255, 255, 255, .075); }
        .feature-number { display: grid; width: 48px; height: 48px; place-items: center; color: #c7d2fe; border-radius: 15px; background: rgba(99, 102, 241, .13); font-weight: 900; }
        .feature-card:nth-child(3n + 2) .feature-number { color: #a5f3fc; background: rgba(34, 211, 238, .1); }
        .feature-card:nth-child(3n + 3) .feature-number { color: #a7f3d0; background: rgba(52, 211, 153, .1); }
        .feature-card h3 { margin: 22px 0 8px; font-size: 20px; }
        .feature-card p { margin: 0; color: var(--muted); font-size: 14px; }

        .roles { display: grid; grid-template-columns: .9fr 1.1fr; align-items: center; gap: 72px; }
        .roles-copy .section-title { margin-bottom: 20px; }
        .roles-copy p { color: var(--muted); }
        .role-list { display: grid; gap: 11px; }
        .role-row { padding: 17px 19px; border: 1px solid var(--border); border-radius: 17px; background: var(--surface); }
        .role-main { display: flex; align-items: center; gap: 14px; }
        .role-color { width: 7px; height: 42px; border-radius: 99px; background: linear-gradient(var(--indigo), var(--cyan)); }
        .role-row:nth-child(3) .role-color { background: linear-gradient(var(--cyan), var(--green)); }
        .role-row:nth-child(4) .role-color { background: linear-gradient(#64748b, #334155); }
        .role-row strong { display: block; }
        .role-row small { color: var(--muted); }
        .role-tag { padding: 5px 10px; color: #cbd5e1; border-radius: 999px; background: rgba(255,255,255,.06); font-size: 10px; font-weight: 800; }

        .cta { position: relative; overflow: hidden; margin-bottom: 95px; padding: 70px 30px; border: 1px solid rgba(129, 140, 248, .22); border-radius: 36px; background: radial-gradient(circle at 10% 0%, rgba(99,102,241,.36), transparent 40%), radial-gradient(circle at 90% 100%, rgba(34,211,238,.2), transparent 37%), #11152b; text-align: center; box-shadow: var(--shadow); }
        .cta h2 { max-width: 720px; margin: 0 auto; font-size: clamp(34px, 5vw, 54px); line-height: 1.1; letter-spacing: -.04em; }
        .cta p { max-width: 620px; margin: 20px auto 28px; color: #b6c1d2; }
        .cta-actions { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; }

        footer { padding: 28px 0; border-top: 1px solid var(--border); color: #64748b; font-size: 13px; }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; }

        /* Home splash screen: automatically disappears after loading. */
        .home-splash {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: grid;
            place-items: center;
            overflow: hidden;
            color: #fff;
            background: #070b18;
            animation: splash-hide .7s 2.7s ease forwards;
        }

        .home-splash::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .06;
            background-image:
                linear-gradient(rgba(255,255,255,.7) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.7) 1px, transparent 1px);
            background-size: 55px 55px;
        }

        .home-splash-glow {
            position: absolute;
            width: 430px;
            aspect-ratio: 1;
            border-radius: 50%;
            background: #4f46e5;
            filter: blur(95px);
            opacity: .28;
            animation: splash-glow 4s ease-in-out infinite;
        }

        .home-splash-content {
            position: relative;
            width: min(420px, calc(100% - 40px));
            text-align: center;
            animation: splash-enter .7s ease both;
        }

        .home-splash-logo-wrap {
            position: relative;
            display: grid;
            width: 112px;
            height: 112px;
            margin: 0 auto 28px;
            place-items: center;
        }

        .home-splash-ring {
            position: absolute;
            inset: 0;
            border: 1px solid rgba(129,140,248,.38);
            border-radius: 33px;
            animation: splash-spin 6s linear infinite;
        }

        .home-splash-ring::before,
        .home-splash-ring::after {
            content: "";
            position: absolute;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #67e8f9;
            box-shadow: 0 0 20px #22d3ee;
        }

        .home-splash-ring::before { top: -5px; left: calc(50% - 4px); }
        .home-splash-ring::after { top: calc(50% - 4px); right: -5px; background: #818cf8; box-shadow: 0 0 20px #6366f1; }

        .home-splash-logo {
            display: grid;
            width: 82px;
            height: 82px;
            place-items: center;
            border: 1px solid rgba(255,255,255,.2);
            border-radius: 25px;
            background: linear-gradient(135deg, #6366f1, #22d3ee);
            box-shadow: 0 25px 60px rgba(79,70,229,.45);
            font-size: 34px;
            font-weight: 950;
        }

        .home-splash-title { margin: 0; font-size: clamp(31px, 9vw, 46px); line-height: 1.15; letter-spacing: .15em; text-indent: .15em; }
        .home-splash-subtitle { margin: 8px 0 0; color: #94a3b8; font-size: 12px; font-weight: 700; letter-spacing: .13em; }
        .home-splash-welcome { margin: 18px 0 0; color: #cbd5e1; font-size: 13px; }

        .home-splash-progress {
            width: min(290px, 80%);
            height: 5px;
            margin: 31px auto 0;
            overflow: hidden;
            border-radius: 99px;
            background: rgba(255,255,255,.09);
        }

        .home-splash-progress::after {
            content: "";
            display: block;
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, #6366f1, #22d3ee, #34d399);
            transform-origin: left;
            animation: splash-load 2.55s ease-in-out both;
        }

        .home-splash-status { display: flex; align-items: center; justify-content: center; gap: 9px; margin-top: 14px; color: #64748b; font-size: 11px; }
        .home-splash-dot { width: 6px; height: 6px; border-radius: 50%; background: #34d399; box-shadow: 0 0 0 5px rgba(52,211,153,.1); animation: splash-pulse 1.1s ease-in-out infinite; }

        @keyframes drift { 0%, 100% { transform: translate3d(0, 0, 0) scale(1); } 50% { transform: translate3d(45px, -30px, 0) scale(1.12); } }
        @keyframes rise { from { opacity: 0; transform: translateY(24px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0) rotate(-.4deg); } 50% { transform: translateY(-15px) rotate(.4deg); } }
        @keyframes grow { from { transform: scaleY(0); } to { transform: scaleY(1); } }
        @keyframes splash-enter { from { opacity: 0; transform: translateY(22px) scale(.97); } to { opacity: 1; transform: none; } }
        @keyframes splash-spin { to { transform: rotate(360deg); } }
        @keyframes splash-load { from { transform: scaleX(0); } to { transform: scaleX(1); } }
        @keyframes splash-pulse { 50% { opacity: .35; transform: scale(.75); } }
        @keyframes splash-glow { 50% { transform: translate(35px, -25px) scale(1.13); } }
        @keyframes splash-hide {
            0% { opacity: 1; visibility: visible; pointer-events: auto; }
            99% { opacity: 0; visibility: visible; pointer-events: auto; }
            100% { opacity: 0; visibility: hidden; pointer-events: none; }
        }

        @media (max-width: 980px) {
            .nav-links, .nav-actions { display: none; }
            .menu-button { display: flex; }
            #menu-toggle:checked ~ .mobile-menu { display: grid; }
            #menu-toggle:checked + .nav .menu-button span { opacity: 0; }
            #menu-toggle:checked + .nav .menu-button::before { transform: translateY(7px) rotate(45deg); }
            #menu-toggle:checked + .nav .menu-button::after { transform: translateY(-7px) rotate(-45deg); }
            .mobile-menu { gap: 10px; padding: 0 20px 20px; }
            .mobile-menu .nav-link { padding: 8px 0; }
            .hero { min-height: auto; grid-template-columns: 1fr; padding-block: 80px 100px; }
            .hero-copy { text-align: center; }
            .hero-description { margin-inline: auto; }
            .hero-actions, .hero-checks { justify-content: center; }
            .dashboard-wrap { width: min(600px, 100%); margin-inline: auto; }
            .feature-grid { grid-template-columns: repeat(2, 1fr); }
            .roles { grid-template-columns: 1fr; gap: 42px; }
            .roles-copy { text-align: center; }
        }

        @media (max-width: 650px) {
            .container { width: min(100% - 28px, 1180px); }
            .brand-subtitle { display: none; }
            .hero { padding-top: 64px; gap: 60px; }
            h1 { font-size: clamp(43px, 13vw, 60px); }
            .hero-description { font-size: 16px; }
            .hero-actions .button { width: 100%; }
            .floating-badge { display: none; }
            .dashboard-inner { padding: 17px; }
            .stats { gap: 7px; }
            .stat { padding: 11px; }
            .bars { height: 100px; gap: 4px; }
            .alerts { grid-template-columns: 1fr; }
            .trust-grid { grid-template-columns: 1fr 1fr; }
            .trust-item:nth-child(2) { border-right: 0; }
            .trust-item:nth-child(-n + 2) { border-bottom: 1px solid var(--border); }
            .feature-grid { grid-template-columns: 1fr; }
            .section { padding-block: 80px; }
            .role-tag { display: none; }
            .cta { padding: 54px 20px; border-radius: 26px; }
            .cta-actions .button { width: 100%; }
            .footer-inner { flex-direction: column; text-align: center; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; animation-iteration-count: 1 !important; }
        }
    </style>
</head>
<body>
    <div class="home-splash" id="home-splash" role="status" aria-label="Loading OMNIHUB" aria-hidden="false">
        <div class="home-splash-glow" aria-hidden="true"></div>

        <div class="home-splash-content">
            <div class="home-splash-logo-wrap">
                <div class="home-splash-ring" aria-hidden="true"></div>
                <div class="home-splash-logo">O</div>
            </div>

            <h1 class="home-splash-title">OMNIHUB</h1>
            <p class="home-splash-subtitle">COMPANY MANAGEMENT SYSTEM</p>

            @auth
                <p class="home-splash-welcome">Welcome back, {{ auth()->user()->name }}</p>
            @else
                <p class="home-splash-welcome">Preparing your business workspace</p>
            @endauth

            <div class="home-splash-progress" aria-hidden="true"></div>
            <div class="home-splash-status"><span class="home-splash-dot"></span> Loading securely...</div>
        </div>
    </div>

    <div class="page-background" aria-hidden="true">
        <div class="orb orb-one"></div>
        <div class="orb orb-two"></div>
    </div>

    <header class="site-header">
        <input type="checkbox" id="menu-toggle" aria-label="Toggle navigation">

        <nav class="nav container" aria-label="Main navigation">
            <a href="{{ url('/') }}" class="brand">
                <span class="brand-mark">O</span>
                <span>
                    <span class="brand-name">OMNIHUB</span>
                    <span class="brand-subtitle">Company ERP</span>
                </span>
            </a>

            <div class="nav-links">
                <a href="#features" class="nav-link">Features</a>
                <a href="#roles" class="nav-link">Team Roles</a>
                <a href="#start" class="nav-link">Get Started</a>
            </div>

            <div class="nav-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="button button-primary">Dashboard →</a>
                @else
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="button button-ghost">Log in</a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="button button-primary">Create account</a>
                    @endif
                @endauth
            </div>

            <label for="menu-toggle" class="menu-button" aria-label="Open navigation"><span></span></label>
        </nav>

        <div class="mobile-menu container">
            <a href="#features" class="nav-link">Features</a>
            <a href="#roles" class="nav-link">Team Roles</a>

            @auth
                <a href="{{ url('/dashboard') }}" class="button button-primary">Dashboard</a>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="button button-ghost">Log in</a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="button button-primary">Create account</a>
                @endif
            @endauth
        </div>
    </header>

    <main>
        <section class="hero container">
            <div class="hero-copy">
                <div class="eyebrow"><span class="eyebrow-dot"></span> One system for every company</div>

                <h1>
                    Control your business.
                    <span class="gradient-text">Grow with clarity.</span>
                </h1>

                <p class="hero-description">
                    OMNIHUB connects your companies, products, inventory, sales, orders,
                    customers, reports, and team permissions in one management system.
                </p>

                <div class="hero-actions">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="button button-indigo">Open Dashboard →</a>
                    @else
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="button button-indigo">Start now →</a>
                        @endif

                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="button button-ghost">Log in</a>
                        @endif
                    @endauth
                </div>

                <div class="hero-checks">
                    <span><span class="check">✓</span> Secure team roles</span>
                    <span><span class="check">✓</span> Company data isolation</span>
                    <span><span class="check">✓</span> Responsive design</span>
                </div>
            </div>

            <div class="dashboard-wrap" aria-label="Dashboard preview">
                <div class="floating-badge badge-left"><span class="badge-icon">◆</span>Role protected</div>
                <div class="floating-badge badge-right"><span class="badge-icon">●</span>Team ready</div>

                <div class="dashboard">
                    <div class="dashboard-inner">
                        <div class="dashboard-head">
                            <div>
                                <span class="overline">Company dashboard</span>
                                <h2 class="dashboard-title">OMNIHUB Cambodia</h2>
                            </div>
                            <span class="live">● Live</span>
                        </div>

                        <div class="stats">
    <div class="stat">
        <div class="stat-accent"></div>
        <strong>{{ number_format($homeStats['products'] ?? 0) }}</strong>
        <small>Products</small>
    </div>
    <div class="stat">
        <div class="stat-accent"></div>
        <strong>{{ number_format($homeStats['stock'] ?? 0) }}</strong>
        <small>Stock units</small>
    </div>
    <div class="stat">
        <div class="stat-accent"></div>
        <strong>
            @php $sales = (float) ($homeStats['sales'] ?? 0); @endphp
            @if ($sales >= 1000)
                ${{ number_format($sales / 1000, 1) }}K
            @else
                ${{ number_format($sales, 2) }}
            @endif
        </strong>
        <small>Sales</small>
    </div>
</div>
<div class="chart">
    <div class="chart-head">
        <span>Revenue overview</span>
        @if (isset($homeStats['growth']) && $homeStats['growth'] !== null)
            <span class="chart-growth">{{ $homeStats['growth'] >= 0 ? '+' : '' }}{{ $homeStats['growth'] }}%</span>
        @else
            <span class="chart-growth">—</span>
        @endif
    </div>
    <div class="bars" aria-hidden="true">
        @foreach (($homeStats['bars'] ?? [8,8,8,8,8,8,8,8,8,8,8,8]) as $h)
            <span class="bar" style="height:{{ (int) $h }}%"></span>
        @endforeach
    </div>
</div>

<div class="alerts">
    <div class="alert">
        <small>Low stock alert</small>
        <strong>{{ number_format($homeStats['low_stock'] ?? 0) }} products</strong>
    </div>
    <div class="alert">
        <small>Pending orders</small>
        <strong>{{ number_format($homeStats['pending_orders'] ?? 0) }} orders</strong>
    </div>
</div>
                    </div>
                </div>
            </div>
        </section>

        <section class="trust-strip" aria-label="System highlights">
            <div class="trust-grid container">
                <div class="trust-item"><strong>Multi-company</strong><small>Separate business data</small></div>
                <div class="trust-item"><strong>Real-time stock</strong><small>Stock in and stock out</small></div>
                <div class="trust-item"><strong>Role access</strong><small>Owner to Member</small></div>
                <div class="trust-item"><strong>Clear reports</strong><small>Sales and performance</small></div>
            </div>
        </section>

        <section class="section container" id="features">
            <div class="section-copy">
                <span class="section-label">Everything connected</span>
                <h2 class="section-title">Built for real business operations</h2>
                <p class="section-description">Manage daily work from one clean system and keep every company's information organized.</p>
            </div>

            <div class="feature-grid">
                <article class="feature-card"><span class="feature-number">01</span><h3>Multiple Companies</h3><p>Switch companies while keeping every product, sale, order, and member separated.</p></article>
                <article class="feature-card"><span class="feature-number">02</span><h3>Products</h3><p>Create products, search by SKU or name, set prices, and monitor low-stock levels.</p></article>
                <article class="feature-card"><span class="feature-number">03</span><h3>Inventory</h3><p>Receive and issue stock with movement history and automatically updated balances.</p></article>
                <article class="feature-card"><span class="feature-number">04</span><h3>Sales</h3><p>Record sales, payment methods, discounts, and automatically deduct sold stock.</p></article>
                <article class="feature-card"><span class="feature-number">05</span><h3>Orders</h3><p>Follow customer orders from pending through confirmed, processing, and completed.</p></article>
                <article class="feature-card"><span class="feature-number">06</span><h3>Reports</h3><p>See revenue, sales performance, customer totals, and top products by date.</p></article>
            </div>
        </section>

        <section class="section container roles" id="roles">
            <div class="roles-copy">
                <span class="section-label">Right access, right people</span>
                <h2 class="section-title">Team permissions that match the job</h2>
                <p>Each role only sees the features needed for their work. Permissions should also be protected by Laravel middleware and policies.</p>
            </div>

            <div class="role-list">
                <div class="role-row"><div class="role-main"><span class="role-color"></span><div><strong>Owner</strong><small>Full company control</small></div></div><span class="role-tag">FULL ACCESS</span></div>
                <div class="role-row"><div class="role-main"><span class="role-color"></span><div><strong>Admin</strong><small>Products, sales, orders, reports, and members</small></div></div><span class="role-tag">MANAGE</span></div>
                <div class="role-row"><div class="role-main"><span class="role-color"></span><div><strong>Stock Manager</strong><small>Products and inventory</small></div></div><span class="role-tag">STOCK</span></div>
                <div class="role-row"><div class="role-main"><span class="role-color"></span><div><strong>Member</strong><small>Read-only products and stock</small></div></div><span class="role-tag">VIEW</span></div>
            </div>
        </section>

        <section class="cta container" id="start">
            <h2>Ready to control your company?</h2>
            <p>Create your account, open the dashboard, and start organizing products, inventory, sales, and orders.</p>

            <div class="cta-actions">
                @auth
                    <a href="{{ url('/dashboard') }}" class="button button-primary">Open Dashboard →</a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="button button-primary">Create account →</a>
                    @endif

                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="button button-ghost">Log in</a>
                    @endif
                @endauth
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-inner container">
            <span>© {{ date('Y') }} {{ config('app.name', 'OMNIHUB') }}. All rights reserved.</span>
            <span>Company Management System</span>
        </div>
    </footer>

    <script>
        (() => {
            const splash = document.getElementById('home-splash');

            if (!splash) {
                return;
            }

            const replaySplash = () => {
                splash.setAttribute('aria-hidden', 'false');
                splash.style.animation = 'none';
                void splash.offsetWidth;
                splash.style.animation = 'splash-hide .7s 2.7s ease forwards';
            };

            splash.addEventListener('animationend', event => {
                if (event.animationName === 'splash-hide') {
                    splash.setAttribute('aria-hidden', 'true');
                }
            });

            window.addEventListener('pageshow', replaySplash);
        })();
    </script>
</body>
</html>
