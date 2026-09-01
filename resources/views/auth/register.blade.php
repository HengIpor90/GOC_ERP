<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create account — {{ config('app.name', 'OMNIHUB') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: #e2e8f0; background: #070b18; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .page { position: relative; display: grid; min-height: 100vh; grid-template-columns: 1.02fr .98fr; overflow: hidden; }
        .visual { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 48px clamp(35px,6vw,88px); border-right: 1px solid rgba(255,255,255,.09); background: radial-gradient(circle at 10% 10%,rgba(34,211,238,.24),transparent 36%),radial-gradient(circle at 90% 90%,rgba(99,102,241,.3),transparent 38%),#0a1020; }
        .visual::after { content: ""; position: absolute; inset: 0; opacity: .055; background-image: linear-gradient(rgba(255,255,255,.7) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.7) 1px,transparent 1px); background-size: 54px 54px; pointer-events: none; }
        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; width: fit-content; }
        .brand-mark { display: grid; width: 46px; height: 46px; place-items: center; border-radius: 14px; background: linear-gradient(135deg,#6366f1,#22d3ee); box-shadow: 0 15px 35px rgba(79,70,229,.35); font-weight: 900; }
        .brand strong { display: block; letter-spacing: .15em; } .brand small { color: #64748b; }
        .visual-copy { position: relative; z-index: 1; max-width: 590px; padding-block: 55px; animation: rise .7s ease both; }
        .visual-copy > span { color: #67e8f9; font-size: 12px; font-weight: 900; letter-spacing: .18em; }
        .visual h1 { margin: 18px 0; font-size: clamp(42px,5vw,68px); line-height: 1.05; letter-spacing: -.05em; }
        .visual h1 em { display: block; color: transparent; background: linear-gradient(90deg,#67e8f9,#818cf8,#6ee7b7); background-clip: text; -webkit-background-clip: text; font-style: normal; }
        .visual p { max-width: 510px; color: #94a3b8; font-size: 17px; line-height: 1.8; }
        .steps { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; }
        .step { padding: 13px; border: 1px solid rgba(255,255,255,.09); border-radius: 13px; background: rgba(255,255,255,.04); }
        .step b { display: block; color: #67e8f9; font-size: 12px; }.step small { color: #64748b; font-size: 10px; }
        .form-side { display: grid; place-items: center; padding: 34px; }
        .card { width: min(460px,100%); padding-block: 30px; animation: rise .7s .12s ease both; }
        .mobile-brand { display: none; margin-bottom: 30px; }
        .back { display: inline-flex; margin-bottom: 24px; color: #64748b; font-size: 13px; transition: .2s; }.back:hover { color: white; }
        h2 { margin: 0; color: white; font-size: 34px; letter-spacing: -.035em; }
        .subtitle { margin: 8px 0 27px; color: #64748b; }
        .field { margin-bottom: 16px; }
        label { display: block; margin-bottom: 7px; color: #cbd5e1; font-size: 13px; font-weight: 700; }
        input { width: 100%; height: 50px; padding: 0 15px; color: white; border: 1px solid rgba(255,255,255,.12); outline: none; border-radius: 14px; background: rgba(255,255,255,.045); transition: border .2s,box-shadow .2s; }
        input:focus { border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,.13); }
        .invalid { border-color: #ef4444 !important; }
        .error { margin: 6px 0 0; color: #fca5a5; font-size: 12px; }
        .hint { margin: 7px 0 0; color: #64748b; font-size: 11px; }
        button { width: 100%; height: 53px; margin-top: 4px; color: white; border: 0; border-radius: 14px; background: linear-gradient(135deg,#0891b2,#4f46e5); box-shadow: 0 16px 38px rgba(8,145,178,.23); cursor: pointer; font-weight: 850; transition: .2s; }
        button:hover { transform: translateY(-2px); box-shadow: 0 20px 42px rgba(79,70,229,.36); }
        .terms { margin: 17px 0 0; color: #475569; text-align: center; font-size: 11px; }
        .switch { margin-top: 20px; color: #64748b; text-align: center; font-size: 14px; }.switch a { color: #67e8f9; font-weight: 800; }
        @keyframes rise { from { opacity: 0; transform: translateY(22px); } to { opacity: 1; transform: none; } }
        @media (max-width:900px) { .page { grid-template-columns: 1fr; }.visual { display:none }.form-side { min-height:100vh }.mobile-brand { display:flex } }
        @media (max-width:480px) { .form-side { padding:22px 20px }.card { padding-block:18px }h2 { font-size:30px } }
    </style>
</head>
<body>
    <main class="page">
        <section class="visual">
            <a href="{{ route('home') }}" class="brand"><span class="brand-mark">O</span><span><strong>OMNIHUB</strong><small>Company ERP</small></span></a>
            <div class="visual-copy"><span>CREATE YOUR WORKSPACE</span><h1>Start organized.<em>Scale confidently.</em></h1><p>Create one secure account and begin managing your business operations from a connected dashboard.</p></div>
            <div class="steps"><div class="step"><b>01 · Register</b><small>Create your account</small></div><div class="step"><b>02 · Sign in</b><small>Secure your session</small></div><div class="step"><b>03 · Manage</b><small>Open your dashboard</small></div></div>
        </section>

        <section class="form-side">
            <div class="card">
                <a href="{{ route('home') }}" class="brand mobile-brand"><span class="brand-mark">O</span><span><strong>OMNIHUB</strong><small>Company ERP</small></span></a>
                <a href="{{ route('home') }}" class="back">← Back to home</a>
                <h2>Create account</h2>
                <p class="subtitle">Enter your information to get started.</p>

                <form method="POST" action="{{ route('register.store') }}">
                    @csrf

                    <div class="field">
                        <label for="name">Full name</label>
                        <input id="name" class="@error('name') invalid @enderror" type="text" name="name" value="{{ old('name') }}" autocomplete="name" autofocus required>
                        @error('name')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Email address</label>
                        <input id="email" class="@error('email') invalid @enderror" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
                        @error('email')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" class="@error('password') invalid @enderror" type="password" name="password" autocomplete="new-password" required>
                        <p class="hint">At least 8 characters with letters and numbers.</p>
                        @error('password')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required>
                    </div>

                    <button type="submit">Create my account →</button>
                    <p class="terms">Your password is securely hashed by Laravel before it is saved.</p>
                </form>

                <p class="switch">Already have an account? <a href="{{ route('login') }}">Log in</a></p>
            </div>
        </section>
    </main>
</body>
</html>
