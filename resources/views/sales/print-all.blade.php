<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>All Sales Report</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbe3ed;
            --primary: #4f46e5;
            --cyan: #0891b2;
        }
        * { box-sizing: border-box; }
        body { margin: 0; color: var(--ink); background: #eef2f7; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        button, input, a { font: inherit; }
        .toolbar { display: flex; width: min(1380px, calc(100% - 28px)); align-items: end; justify-content: space-between; gap: 14px; margin: 20px auto 12px; }
        .toolbar-group, .filter-form { display: flex; align-items: end; gap: 8px; }
        .toolbar a, .toolbar button { display: inline-grid; min-height: 40px; place-items: center; padding: 0 15px; border-radius: 10px; cursor: pointer; text-decoration: none; font-size: 12px; font-weight: 900; }
        .back, .clear { color: #475569; border: 1px solid var(--line); background: white; }
        .apply { color: #4338ca; border: 1px solid #c7d2fe; background: #eef2ff; }
        .print { color: white; border: 0; background: linear-gradient(135deg, var(--primary), var(--cyan)); }
        .date-field { display: grid; gap: 5px; color: var(--muted); font-size: 9px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase; }
        .date-field input { height: 40px; padding: 0 10px; color: var(--ink); border: 1px solid var(--line); border-radius: 10px; background: white; font-size: 12px; }
        .sheet { width: min(1380px, calc(100% - 28px)); margin: 0 auto 35px; padding: 34px; border-radius: 6px; background: white; box-shadow: 0 24px 65px rgba(15, 23, 42, .14); }
        .report-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 28px; padding-bottom: 22px; border-bottom: 2px solid var(--ink); }
        .brand { display: flex; align-items: center; gap: 12px; }
        .mark { display: grid; width: 50px; height: 50px; place-items: center; color: white; border-radius: 14px; background: linear-gradient(135deg, var(--primary), var(--cyan)); font-size: 18px; font-weight: 900; }
        .brand strong { display: block; font-size: 18px; letter-spacing: .1em; }
        .brand small { display: block; margin-top: 4px; color: var(--muted); font-size: 9px; letter-spacing: .09em; }
        .title { text-align: right; }
        .title h1 { margin: 0; font-size: 30px; letter-spacing: -.04em; }
        .title .khmer { margin: 4px 0 0; color: var(--primary); font-size: 12px; font-weight: 900; }
        .title .meta { margin: 8px 0 0; color: var(--muted); font-size: 9px; line-height: 1.6; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 20px 0; }
        .summary article { padding: 14px 16px; border: 1px solid var(--line); border-radius: 11px; background: #f8fafc; }
        .summary small { display: block; color: var(--muted); font-size: 9px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .summary strong { display: block; margin-top: 5px; font-size: 21px; }
        .summary article:last-child strong { color: #059669; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        thead { display: table-header-group; }
        th { padding: 10px 8px; color: white; background: var(--ink); font-size: 8px; letter-spacing: .07em; text-align: left; text-transform: uppercase; }
        th:first-child { border-radius: 7px 0 0 7px; }
        th:last-child { border-radius: 0 7px 7px 0; text-align: right; }
        td { padding: 10px 8px; border-bottom: 1px solid #e8edf3; font-size: 9px; vertical-align: top; }
        td:last-child { text-align: right; font-weight: 900; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .product { display: block; max-width: 190px; font-weight: 800; }
        .sub { display: block; margin-top: 3px; color: #94a3b8; font-size: 8px; }
        .empty { padding: 32px 10px; color: var(--muted); text-align: center; }
        .bottom { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 28px; align-items: start; margin-top: 22px; }
        .payments { padding: 14px 16px; border: 1px solid var(--line); border-radius: 11px; }
        .payments h2 { margin: 0 0 8px; font-size: 11px; }
        .payment-row { display: grid; grid-template-columns: 1fr auto auto; gap: 18px; padding: 7px 0; border-top: 1px solid #edf2f7; font-size: 9px; }
        .payment-row span:last-child { min-width: 70px; text-align: right; font-weight: 900; }
        .grand { padding: 16px; color: white; border-radius: 11px; background: var(--ink); }
        .grand div { display: flex; justify-content: space-between; gap: 20px; font-size: 10px; }
        .grand strong { font-size: 18px; }
        .footer { display: flex; justify-content: space-between; gap: 30px; margin-top: 45px; color: var(--muted); font-size: 8px; }
        .signature { min-width: 170px; padding-top: 8px; border-top: 1px solid #94a3b8; text-align: center; }
        .error { width: min(1380px, calc(100% - 28px)); margin: 10px auto; padding: 12px 14px; color: #991b1b; border: 1px solid #fecaca; border-radius: 10px; background: #fef2f2; font-size: 11px; }
        @media (max-width: 760px) {
            .toolbar, .toolbar-group, .filter-form, .report-head { align-items: stretch; flex-direction: column; }
            .toolbar .toolbar-group:last-child { flex-direction: row; }
            .sheet { padding: 22px 16px; }
            .title { text-align: left; }
            .summary { grid-template-columns: 1fr; }
            .bottom { grid-template-columns: 1fr; }
        }
        @page { size: A4 landscape; margin: 9mm; }
        @media print {
            body { background: white; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .no-print { display: none !important; }
            .sheet { width: 100%; margin: 0; padding: 0; border-radius: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @if ($errors->any())
        <div class="error no-print">{{ $errors->first() }}</div>
    @endif

    <div class="toolbar no-print">
        <form class="filter-form" method="GET" action="{{ route('sales.print-all') }}">
            <a class="back" href="{{ route('sales.index') }}">← Back to sales</a>
            <label class="date-field">From<input type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
            <label class="date-field">To<input type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
            <button class="apply" type="submit">Apply dates</button>
            @if (($filters['from'] ?? null) || ($filters['to'] ?? null))
                <a class="clear" href="{{ route('sales.print-all') }}">Clear</a>
            @endif
        </form>
        <div class="toolbar-group">
            <button class="print" type="button" onclick="window.print()">Print all / Save PDF</button>
        </div>
    </div>

    <main class="sheet">
        <header class="report-head">
            <div class="brand">
                <span class="mark">SVL</span>
                <span>
                    <strong>{{ strtoupper(config('app.name', 'GOC_ERP')) }}</strong>
                    <small>SALES &amp; INVENTORY MANAGEMENT</small>
                </span>
            </div>
            <div class="title">
                <h1>All Sales Report</h1>
                <p class="khmer">របាយការណ៍លក់ទាំងអស់</p>
                <p class="meta">
                    Printed (Cambodia): {{ now()->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') }}<br>
                    Period:
                    @if (($filters['from'] ?? null) || ($filters['to'] ?? null))
                        {{ $filters['from'] ?? 'Beginning' }} — {{ $filters['to'] ?? 'Today' }}
                    @else
                        All dates
                    @endif
                </p>
            </div>
        </header>

        <section class="summary" aria-label="Sales totals">
            <article><small>Transactions</small><strong>{{ number_format($summary['transactions']) }}</strong></article>
            <article><small>Units sold</small><strong>{{ number_format($summary['units']) }}</strong></article>
            <article><small>Total revenue</small><strong>${{ number_format($summary['revenue'], 2) }}</strong></article>
        </section>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>No.</th><th>Invoice</th><th>Date</th><th>Products / SKU</th><th>Customer</th><th>Units</th><th>Payment</th><th>Cashier</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><strong>{{ $sale->invoiceNumber() }}</strong></td>
                            <td>{{ $sale->sold_at?->timezone('Asia/Phnom_Penh')->format('F d, Y') ?? '—' }}<span class="sub">{{ $sale->sold_at?->timezone('Asia/Phnom_Penh')->format('h:i A') }}</span></td>
                            <td>
                                @foreach ($sale->items as $item)
                                    <span class="product">{{ $item->product?->name ?? 'Deleted product' }}</span>
                                    <span class="sub">{{ $item->product?->sku ?? '—' }} · {{ $item->saleTypeLabel() }} · {{ $item->sale_quantity }} {{ $item->sellingUnitLabel() }} × ${{ number_format((float) $item->unit_price, 2) }}@if ((float) $item->discount_rate > 0) · −{{ number_format((float) $item->discount_rate, 2) }}%@endif</span>
                                @endforeach
                            </td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td>{{ number_format($sale->items->sum('quantity')) }}</td>
                            <td>{{ strtoupper($sale->payment_method ?: 'ABA') }}</td>
                            <td>{{ $sale->creator?->name ?? 'System' }}</td>
                            <td>${{ number_format((float) $sale->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td class="empty" colspan="9">No sales found for the selected dates.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <section class="bottom">
            <div class="payments">
                <h2>Payment summary</h2>
                @forelse ($paymentTotals as $method => $totals)
                    <div class="payment-row"><strong>{{ $method }}</strong><span>{{ number_format($totals['transactions']) }} transactions</span><span>${{ number_format($totals['total'], 2) }}</span></div>
                @empty
                    <div class="payment-row"><span>No payments</span><span>0 transactions</span><span>$0.00</span></div>
                @endforelse
            </div>
            <div class="grand">
                <div><span>Grand total</span><strong>${{ number_format($summary['revenue'], 2) }}</strong></div>
            </div>
        </section>

        <footer class="footer">
            <span>Generated by {{ config('app.name', 'GOC_ERP') }} · {{ $summary['transactions'] }} sales record(s)</span>
            <span class="signature">Authorized signature</span>
        </footer>
    </main>
</body>
</html>
