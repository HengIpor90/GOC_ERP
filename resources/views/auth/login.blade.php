<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in — {{ config('app.name', 'OMNIHUB') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: #e2e8f0; background: #070b18; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .page { position: relative; display: grid; min-height: 100vh; grid-template-columns: .95fr 1.05fr; overflow: hidden; }
        .visual { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 48px clamp(35px, 6vw, 88px); border-right: 1px solid rgba(255,255,255,.09); background: radial-gradient(circle at 10% 10%, rgba(99,102,241,.36), transparent 38%), radial-gradient(circle at 95% 95%, rgba(34,211,238,.18), transparent 35%), #0a1020; }
        .visual::after { content: ""; position: absolute; inset: 0; opacity: .055; background-image: linear-gradient(rgba(255,255,255,.7) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.7) 1px,transparent 1px); background-size: 54px 54px; pointer-events: none; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; width: fit-content; }
        .brand-mark { display: grid; width: 46px; height: 46px; place-items: center; border-radius: 14px; background: linear-gradient(135deg,#6366f1,#22d3ee); box-shadow: 0 15px 35px rgba(79,70,229,.35); font-weight: 900; }
        .brand strong { display: block; letter-spacing: .15em; } .brand small { color: #64748b; }
        .visual-copy { position: relative; z-index: 1; max-width: 580px; padding-block: 70px; animation: rise .7s ease both; }
        .visual-copy span { color: #a5b4fc; font-size: 12px; font-weight: 900; letter-spacing: .18em; }
        .visual h1 { margin: 18px 0; font-size: clamp(42px, 5vw, 68px); line-height: 1.05; letter-spacing: -.05em; }
        .visual h1 em { display: block; color: transparent; background: linear-gradient(90deg,#818cf8,#67e8f9,#6ee7b7); background-clip: text; -webkit-background-clip: text; font-style: normal; }
        .visual p { max-width: 500px; color: #94a3b8; font-size: 17px; line-height: 1.8; }
        .points { position: relative; z-index: 1; display: flex; flex-wrap: wrap; gap: 18px; color: #64748b; font-size: 12px; }
        .points b { color: #34d399; }
        .form-side { display: grid; place-items: center; padding: 35px; }
        .card { width: min(450px, 100%); animation: rise .7s .12s ease both; }
        .mobile-brand { display: none; margin-bottom: 35px; }
        .back { display: inline-flex; margin-bottom: 28px; color: #64748b; font-size: 13px; transition: .2s; } .back:hover { color: white; }
        h2 { margin: 0; color: white; font-size: 34px; letter-spacing: -.035em; }
        .subtitle { margin: 9px 0 30px; color: #64748b; }
        .field { margin-bottom: 18px; }
        label { display: block; margin-bottom: 8px; color: #cbd5e1; font-size: 13px; font-weight: 700; }
        input[type=email], input[type=password] { width: 100%; height: 52px; padding: 0 15px; color: white; border: 1px solid rgba(255,255,255,.12); outline: none; border-radius: 14px; background: rgba(255,255,255,.045); transition: border .2s, box-shadow .2s; }
        input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,.13); }
        .invalid { border-color: #ef4444 !important; }
        .error { margin: 7px 0 0; color: #fca5a5; font-size: 12px; }
        .row { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 4px 0 23px; color: #94a3b8; font-size: 13px; }
        .remember { display: flex; align-items: center; gap: 8px; cursor: pointer; } .remember input { accent-color: #6366f1; }
        button { width: 100%; height: 53px; color: white; border: 0; border-radius: 14px; background: linear-gradient(135deg,#6366f1,#4f46e5); box-shadow: 0 16px 38px rgba(79,70,229,.28); cursor: pointer; font-weight: 850; transition: .2s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 20px 42px rgba(79,70,229,.38); }
        .switch { margin-top: 25px; color: #64748b; text-align: center; font-size: 14px; } .switch a { color: #a5b4fc; font-weight: 800; }
        @keyframes rise { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
        @media (max-width: 900px) { .page { grid-template-columns: 1fr; } .visual { display: none; } .form-side { min-height: 100vh; } .mobile-brand { display: flex; } }
        @media (max-width: 480px) { .form-side { padding: 24px 20px; } h2 { font-size: 30px; } }
    </style>
</head>
<body>
    <main class="page">
        <section class="visual">
            <a href="{{ route('home') }}" class="brand"><span class="brand-mark">O</span><span><strong>OMNIHUB</strong><small>Company ERP</small></span></a>
            <div class="visual-copy"><span>WELCOME BACK</span><h1>Continue building.<em>Stay in control.</em></h1><p>Sign in to manage your companies, products, inventory, sales, orders, customers, and team members.</p></div>
            <div class="points"><span><b>✓</b> Secure session</span><span><b>✓</b> Protected dashboard</span><span><b>✓</b> Role-based access</span></div>
        </section>

        <section class="form-side">
            <div class="card">
                <a href="{{ route('home') }}" class="brand mobile-brand"><span class="brand-mark">O</span><span><strong>OMNIHUB</strong><small>Company ERP</small></span></a>
                <a href="{{ route('home') }}" class="back">← Back to home</a>
                <h2>Log in</h2>
                <p class="subtitle">Enter your account details to continue.</p>

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" class="@error('email') invalid @enderror" type="email" name="email" value="{{ old('email') }}" autocomplete="email" autofocus required>
                        @error('email')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" class="@error('password') invalid @enderror" type="password" name="password" autocomplete="current-password" required>
                        @error('password')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="row">
                        <label class="remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Remember me</label>
                    </div>

                    <button type="submit">Log in securely →</button>
                </form>

                <p class="switch">Don't have an account? <a href="{{ route('register') }}">Create one</a></p>
            </div>
        </section>
    </main>
</body>
</html>
