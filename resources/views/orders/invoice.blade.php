<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order Invoice {{ $order->order_number }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --primary: #4f46e5;
            --cyan: #0891b2;
            --bank: {{ $bank['color'] ?? '#0055A5' }};
            --accent: {{ $bank['accent'] ?? '#E31C23' }};
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: #eef2f7; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        button, a { font: inherit; }
        .actions { display: flex; width: min(850px, calc(100% - 28px)); justify-content: flex-end; gap: 9px; margin: 22px auto 12px; }
        .actions a, .actions button { display: inline-grid; height: 40px; place-items: center; padding: 0 15px; border-radius: 10px; cursor: pointer; text-decoration: none; font-size: 12px; font-weight: 900; }
        .back { color: #475569; border: 1px solid var(--line); background: white; }
        .print { color: white; border: 0; background: linear-gradient(135deg, var(--primary), var(--cyan)); }
        .sheet { width: min(850px, calc(100% - 28px)); min-height: 1050px; margin: 0 auto 35px; padding: clamp(28px, 6vw, 58px); border-radius: 5px; background: white; box-shadow: 0 24px 65px rgba(15, 23, 42, .14); }
        .invoice-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 30px; padding-bottom: 30px; border-bottom: 2px solid var(--ink); }
        .brand { display: flex; align-items: center; gap: 13px; }
        .mark { display: grid; width: 52px; height: 52px; place-items: center; color: white; border-radius: 15px; background: linear-gradient(135deg, var(--primary), var(--cyan)); font-size: 20px; font-weight: 900; }
        .brand strong { display: block; font-size: 19px; letter-spacing: .12em; }
        .brand small { display: block; margin-top: 3px; color: var(--muted); font-size: 9px; letter-spacing: .12em; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { margin: 0; font-size: clamp(27px, 5vw, 40px); letter-spacing: -.045em; }
        .invoice-title p { margin: 6px 0 0; color: var(--primary); font-size: 12px; font-weight: 900; letter-spacing: .08em; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; padding: 30px 0; }
        .meta-block h2 { margin: 0 0 12px; color: #94a3b8; font-size: 9px; letter-spacing: .13em; text-transform: uppercase; }
        .meta-block strong { display: block; margin-bottom: 5px; font-size: 15px; }
        .meta-block p { margin: 3px 0; color: var(--muted); font-size: 11px; line-height: 1.55; }
        .invoice-meta { display: grid; grid-template-columns: auto 1fr; gap: 7px 18px; }
        .invoice-meta dt { color: #94a3b8; font-size: 10px; }
        .invoice-meta dd { margin: 0; text-align: right; font-size: 11px; font-weight: 800; }
        .paid { display: inline-block; padding: 5px 8px; color: #047857; border-radius: 999px; background: #ecfdf5; font-size: 9px; font-weight: 900; text-transform: uppercase; }
        .paid.unpaid, .paid.pending, .paid.processing { color: #b45309; background: #fffbeb; }
        .paid.cancelled { color: #b91c1c; background: #fef2f2; }
        .items { width: 100%; border-collapse: collapse; }
        .items th { padding: 12px 11px; color: white; background: var(--ink); font-size: 9px; letter-spacing: .08em; text-align: left; text-transform: uppercase; }
        .items th:first-child { border-radius: 8px 0 0 8px; }
        .items th:last-child { border-radius: 0 8px 8px 0; text-align: right; }
        .items td { padding: 17px 11px; border-bottom: 1px solid var(--line); font-size: 12px; vertical-align: top; }
        .items td:last-child { text-align: right; font-weight: 900; }
        .product-name { display: block; font-size: 13px; }
        .product-sku { display: block; margin-top: 4px; color: #94a3b8; font-size: 9px; }
        .totals { width: min(320px, 100%); margin: 28px 0 0 auto; }
        .total-row { display: flex; justify-content: space-between; gap: 20px; padding: 9px 5px; color: var(--muted); font-size: 11px; }
        .grand-total { margin-top: 5px; padding: 15px 14px; color: white; border-radius: 10px; background: var(--ink); font-size: 15px; font-weight: 900; }

        .payment-wrap { display: flex; justify-content: flex-start; margin-top: 30px; }
        .qr-card {
            width: 300px;
            padding: 10px 8px; 
            border: 1px solid #dbe3ef;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
            text-align: center;
        }
        .qr-card .qr-title {
            margin: 0 0 12px;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.04em;
            color: var(--ink);
            text-transform: uppercase;
        }
        .qr-card .pay-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 6px;
            font-size: 20px;
            font-weight: 900;
            letter-spacing: -0.02em;
            color: var(--bank);
            line-height: 1;
        }
        .qr-card .tagline {
            margin: 0 0 12px;
            font-size: 10px;
            color: var(--muted);
            font-style: italic;
        }
        .qr-frame {
            position: relative;
            display: inline-grid;
            place-items: center;
            width: 150px;
            height: 150px;
            margin: 0 auto 12px;
        }
        .qr-frame::before,
        .qr-frame::after {
            content: "";
            position: absolute;
            width: 22px;
            height: 22px;
            border: 3px solid #38bdf8;
            pointer-events: none;
        }
        .qr-frame::before {
            top: 0; left: 0;
            border-right: 0; border-bottom: 0;
            border-radius: 4px 0 0 0;
        }
        .qr-frame::after {
            bottom: 0; right: 0;
            border-left: 0; border-top: 0;
            border-radius: 0 0 4px 0;
        }
        .qr-corners-tr,
        .qr-corners-bl {
            position: absolute;
            width: 22px;
            height: 22px;
            border: 3px solid #38bdf8;
            pointer-events: none;
        }
        .qr-corners-tr {
            top: 0; right: 0;
            border-left: 0; border-bottom: 0;
            border-radius: 0 4px 0 0;
        }
        .qr-corners-bl {
            bottom: 0; left: 0;
            border-right: 0; border-top: 0;
            border-radius: 0 0 0 4px;
        }
        .qr-frame img {
            width: 128px;
            height: 128px;
            object-fit: contain;
            display: block;
        }
        .qr-card .account-name {
            margin: 0;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 0.04em;
            color: var(--ink);
            text-transform: uppercase;
        }
        .qr-card .mid {
            margin: 4px 0 0;
            font-size: 9px;
            color: var(--muted);
            letter-spacing: 0.02em;
        }
        .qr-card .pay-link {
            display: inline-block;
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 8px;
            background: var(--bank);
            color: white;
            text-decoration: none;
            font-size: 10px;
            font-weight: 900;
        }

        .footer { display: grid; grid-template-columns: 1fr auto; gap: 25px; align-items: end; margin-top: 40px; padding-top: 22px; border-top: 1px solid var(--line); }
        .footer strong { display: block; font-size: 13px; }
        .footer p { max-width: 430px; margin: 5px 0 0; color: var(--muted); font-size: 10px; line-height: 1.6; }
        .signature { min-width: 160px; padding-top: 9px; text-align: center; border-top: 1px solid #94a3b8; color: var(--muted); font-size: 9px; }

        @media (max-width: 600px) {
            .sheet { min-height: auto; padding: 25px 18px; }
            .invoice-head, .meta-grid, .footer { grid-template-columns: 1fr; flex-direction: column; }
            .invoice-title { text-align: left; }
            .meta-grid { gap: 24px; }
            .payment-wrap { justify-content: center; }
            .footer { margin-top: 50px; }
            .signature { margin-top: 25px; }
        }
        @page { size: A4; margin: 12mm; }
        @media print {
            body { background: white; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .sheet { width: 100%; min-height: auto; margin: 0; padding: 0; border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="actions no-print">
        <a class="back" href="{{ route('orders.index') }}">← Back to orders</a>
        <button class="print" type="button" onclick="window.print()">Print / Save PDF</button>
    </div>

    <main class="sheet">
        <header class="invoice-head">
            <div class="brand">
                <span class="mark">SVL</span>
                <span>
                    <strong>{{ strtoupper(config('app.name', 'OMNIHUB')) }}</strong>
                    <small>ផ្គត់ផ្គង់សំភារៈ គ្រឿងសំណង់និងអគ្គិសនីគ្រប់ប្រភេទ</small>
                </span>
            </div>
            <div class="invoice-title">
                <h1>Order Invoice</h1>
                <p>វិក្កយបត្រកម្មង់</p>
            </div>
        </header>

        <section class="meta-grid">
            <div class="meta-block">
                <h2>Bill to / អតិថិជន</h2>
                <strong>{{ $order->customer?->name ?? 'Walk-in Customer' }}</strong>
                @if ($order->customer?->phone)<p>{{ $order->customer->phone }}</p>@endif
                @if ($order->customer?->email)<p>{{ $order->customer->email }}</p>@endif
                @if ($order->customer?->address)<p>{{ $order->customer->address }}</p>@endif
            </div>
            <div class="meta-block">
                <h2>Order details</h2>
                <dl class="invoice-meta">
                    <dt>Order No.</dt>
                    <dd>{{ $order->order_number }}</dd>
                    <dt>Date</dt>
                    <dd>{{ optional($order->ordered_at)->timezone('Asia/Phnom_Penh')->format('F d, Y') ?? '—' }}</dd>
                    <dt>Time (Cambodia)</dt>
                    <dd>{{ optional($order->ordered_at)->now()->timezone('Asia/Phnom_Penh')->format('h:i A') ?? '—' }}</dd>
                    <dt>Created by</dt>
                    <dd>{{ $order->creator?->name ?? 'System' }}</dd>
                    <dt>Payment</dt>
                    <dd>{{ strtoupper($order->payment_method ?? 'ABA') }}</dd>
                    <dt>Payment status</dt>
                    <dd><span class="paid {{ $order->payment_status }}">{{ strtoupper($order->payment_status) }}</span></dd>
                    <dt>Order status</dt>
                    <dd><span class="paid {{ $order->status }}">{{ strtoupper($order->status) }}</span></dd>
                </dl>
            </div>
        </section>

        <table class="items">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>
                            <strong class="product-name">{{ $item->product?->name ?? 'Deleted product' }}</strong>
                            <span class="product-sku">SKU: {{ $item->product?->sku ?? '—' }}</span>
                        </td>
                        <td>{{ $item->saleTypeLabel() }}</td>
                        <td>{{ number_format($item->sale_quantity) }} {{ $item->sellingUnitLabel() }}<span class="product-sku">{{ number_format($item->quantity) }} stock units</span></td>
                        <td>${{ number_format((float) $item->unit_price, 2) }}<span class="product-sku">per {{ $item->sellingUnitLabel() }}</span></td>
                        <td>{{ number_format((float) $item->discount_rate, 2) }}%</td>
                        <td>${{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @php($orderSubtotal = (float) $order->items->sum(fn ($item) => $item->subtotal ?? $item->total))
        <section class="totals">
            <div class="total-row"><span>Subtotal</span><strong>${{ number_format($orderSubtotal, 2) }}</strong></div>
            <div class="total-row"><span>Discount</span><strong>−${{ number_format(max(0, $orderSubtotal - (float) $order->total), 2) }}</strong></div>
            <div class="total-row"><span>Tax</span><strong>$0.00</strong></div>
            <div class="total-row grand-total"><span>Total</span><strong>${{ number_format((float) $order->total, 2) }}</strong></div>
        </section>

        <div class="payment-wrap">

            <div class="qr-card">

                <h3 class="qr-title">{{ $bank['title'] ?? ('PAYMENT OR ' . strtoupper($bank['name'] ?? 'BANK')) }}</h3>

                <div class="pay-logo">
                    <span>{{ $bank['label'] ?? strtoupper($bank['name'] ?? 'PAY') }}</span>
                </div>
                <p class="tagline">{{ $bank['tagline'] ?? 'Scan. Pay. Done.' }}</p>

                <div class="qr-frame">
                    <span class="qr-corners-tr"></span>
                    <span class="qr-corners-bl"></span>
                    <a href="{{ $bank['pay_url'] ?? '#' }}" target="_blank" rel="noopener noreferrer">
                        <img src="{{ asset($bank['qr_image']) }}"
                             alt="{{ $bank['name'] ?? 'Bank' }} QR">
                    </a>
                </div>

                <p class="account-name">{{ $bank['account_name'] ?? '' }}</p>
                @if (!empty($bank['mid']))
                    <p class="mid">{{ $bank['mid'] }}</p>
                @endif

                @if (!empty($bank['pay_url']) && $bank['pay_url'] !== '#')
                    <a class="pay-link no-print" href="{{ $bank['pay_url'] }}" target="_blank" rel="noopener noreferrer">{{ $bank['button'] ?? 'Pay →' }}</a>
                @endif
            </div>
        </div>

        <footer class="footer">
            <div>
                <strong>Thank you for your order.</strong>
                <p>This order invoice was generated by {{ config('app.name', 'OMNIHUB') }}. Please keep it for your records.</p>
            </div>
            <div class="signature">Authorized signature</div>
        </footer>
    </main>
</body>
</html>
