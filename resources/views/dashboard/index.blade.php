@php
    $currentRoute = request()->route()?->getName();
    $modules = [
        ['route' => 'dashboard', 'letter' => 'D', 'label' => 'Dashboard'],
        ['route' => 'products.index', 'letter' => 'P', 'label' => 'Products'],
        ['route' => 'inventory.index', 'letter' => 'I', 'label' => 'Inventory'],
        ['route' => 'sales.index', 'letter' => 'S', 'label' => 'Sales'],
        ['route' => 'orders.index', 'letter' => 'O', 'label' => 'Orders'],
        ['route' => 'customers.index', 'letter' => 'C', 'label' => 'Customers'],
        ['route' => 'reports.index', 'letter' => 'R', 'label' => 'Reports'],
    ];
    $activeModule = collect($modules)->firstWhere('route', $currentRoute) ?? $modules[0];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $activeModule['label'] }} — {{ config('app.name', 'OMNIHUB') }}</title>
    <style>
        :root {
            --sidebar: #080d1c;
            --sidebar-soft: #111a30;
            --page: #f1f5f9;
            --card: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --primary: #6366f1;
            --cyan: #06b6d4;
            --sidebar-width: 272px;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--ink);
            background: var(--page);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        button { font: inherit; }

        .sidebar-toggle {
            position: fixed;
            width: 1px;
            height: 1px;
            opacity: 0;
            pointer-events: none;
        }

        .sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 50;
            display: flex;
            width: var(--sidebar-width);
            flex-direction: column;
            overflow-y: auto;
            color: #e2e8f0;
            background:
                radial-gradient(circle at 10% 0, rgba(99, 102, 241, .2), transparent 28%),
                var(--sidebar);
            box-shadow: 18px 0 50px rgba(15, 23, 42, .12);
            transition: transform .28s ease;
        }

        .sidebar-head {
            display: flex;
            min-height: 86px;
            align-items: center;
            justify-content: space-between;
            padding: 18px 20px;
            border-bottom: 1px solid rgba(148, 163, 184, .12);
        }
        .brand { display: flex; align-items: center; gap: 11px; }
        .brand-mark {
            display: grid;
            width: 43px;
            height: 43px;
            place-items: center;
            flex: 0 0 auto;
            color: white;
            border-radius: 13px;
            background: linear-gradient(135deg, var(--primary), var(--cyan));
            box-shadow: 0 10px 24px rgba(99, 102, 241, .28);
            font-weight: 900;
        }
        .brand strong { display: block; color: white; font-size: 15px; letter-spacing: .12em; }
        .brand small { display: block; margin-top: 2px; color: #64748b; font-size: 9px; letter-spacing: .14em; }

        .close-sidebar {
            display: none;
            width: 38px;
            height: 38px;
            place-items: center;
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, .18);
            border-radius: 10px;
            cursor: pointer;
        }

        .nav-caption {
            margin: 25px 22px 10px;
            color: #64748b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .side-nav { display: grid; gap: 6px; padding: 0 13px; }
        .side-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 13px;
            min-height: 51px;
            padding: 8px 12px;
            color: #94a3b8;
            border: 1px solid transparent;
            border-radius: 13px;
            font-size: 14px;
            font-weight: 700;
            transition: .2s ease;
        }
        .side-link:hover { color: white; background: rgba(255, 255, 255, .055); }
        .side-link.active {
            color: white;
            border-color: rgba(129, 140, 248, .2);
            background: linear-gradient(90deg, rgba(99, 102, 241, .24), rgba(6, 182, 212, .08));
        }
        .side-link.active::before {
            content: "";
            position: absolute;
            left: -13px;
            width: 4px;
            height: 28px;
            border-radius: 0 6px 6px 0;
            background: linear-gradient(var(--primary), var(--cyan));
        }
        .side-icon {
            display: grid;
            width: 34px;
            height: 34px;
            place-items: center;
            flex: 0 0 auto;
            color: #cbd5e1;
            border-radius: 10px;
            background: rgba(255, 255, 255, .06);
            font-size: 12px;
            font-weight: 900;
        }
        .side-link.active .side-icon { color: white; background: rgba(99, 102, 241, .45); }

        .sidebar-user {
            margin: auto 13px 15px;
            padding: 14px;
            border: 1px solid rgba(148, 163, 184, .13);
            border-radius: 16px;
            background: rgba(255, 255, 255, .045);
        }
        .profile { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .avatar {
            display: grid;
            width: 39px;
            height: 39px;
            place-items: center;
            flex: 0 0 auto;
            color: white;
            border-radius: 12px;
            background: linear-gradient(135deg, #4f46e5, #0891b2);
            font-weight: 900;
        }
        .profile-text { min-width: 0; }
        .profile-text strong,
        .profile-text small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .profile-text strong { color: #f8fafc; font-size: 13px; }
        .profile-text small { margin-top: 3px; color: #64748b; font-size: 10px; }
        .logout-form { margin-top: 12px; }
        .logout {
            width: 100%;
            padding: 10px 12px;
            color: #cbd5e1;
            border: 1px solid rgba(148, 163, 184, .14);
            border-radius: 10px;
            background: rgba(255, 255, 255, .04);
            cursor: pointer;
            font-weight: 800;
            transition: .2s;
        }
        .logout:hover { color: white; border-color: rgba(248, 113, 113, .3); background: rgba(239, 68, 68, .12); }

        .mobile-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            visibility: hidden;
            opacity: 0;
            background: rgba(2, 6, 23, .6);
            backdrop-filter: blur(3px);
            transition: .25s;
        }

        .app { min-height: 100vh; margin-left: var(--sidebar-width); }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            min-height: 76px;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 13px clamp(18px, 3vw, 38px);
            border-bottom: 1px solid rgba(226, 232, 240, .9);
            background: rgba(255, 255, 255, .88);
            backdrop-filter: blur(18px);
        }
        .page-identity { display: flex; align-items: center; gap: 13px; }
        .menu-button {
            display: none;
            width: 42px;
            height: 42px;
            place-items: center;
            color: var(--ink);
            border: 1px solid var(--line);
            border-radius: 12px;
            background: white;
            cursor: pointer;
            font-size: 19px;
        }
        .page-identity h1 { margin: 0; font-size: 18px; letter-spacing: -.02em; }
        .page-identity p { margin: 3px 0 0; color: #94a3b8; font-size: 11px; }
        .home-link {
            padding: 10px 14px;
            color: #475569;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: white;
            font-size: 13px;
            font-weight: 800;
            transition: .2s;
        }
        .home-link:hover { color: #4338ca; border-color: #c7d2fe; }

        .content { width: min(1240px, calc(100% - 48px)); margin: 0 auto; padding: 34px 0 65px; }
        .flash { margin-bottom: 20px; padding: 14px 16px; color: #166534; border: 1px solid #bbf7d0; border-radius: 13px; background: #f0fdf4; }
        .welcome {
            position: relative;
            overflow: hidden;
            padding: clamp(27px, 4vw, 38px);
            color: white;
            border-radius: 24px;
            background: linear-gradient(135deg, #111827, #1e1b4b 62%, #164e63);
            box-shadow: 0 22px 55px rgba(15, 23, 42, .14);
        }
        .welcome::before,
        .welcome::after { content: ""; position: absolute; border-radius: 50%; }
        .welcome::before { right: -65px; top: -110px; width: 300px; height: 300px; background: rgba(34, 211, 238, .13); }
        .welcome::after { right: 180px; bottom: -130px; width: 230px; height: 230px; background: rgba(129, 140, 248, .13); }
        .welcome small { position: relative; z-index: 1; color: #a5b4fc; font-weight: 900; letter-spacing: .15em; }
        .welcome h2 { position: relative; z-index: 1; margin: 10px 0 6px; font-size: clamp(28px, 4vw, 43px); letter-spacing: -.045em; }
        .welcome p { position: relative; z-index: 1; max-width: 610px; margin: 0; color: #cbd5e1; line-height: 1.65; }

        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 20px; }
        .stat {
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: var(--card);
            box-shadow: 0 8px 25px rgba(15, 23, 42, .035);
        }
        .stat-badge { display: grid; width: 41px; height: 41px; place-items: center; margin-bottom: 17px; color: #4f46e5; border-radius: 12px; background: #eef2ff; font-size: 11px; font-weight: 900; }
        .stat:nth-child(2) .stat-badge { color: #0891b2; background: #ecfeff; }
        .stat:nth-child(3) .stat-badge { color: #059669; background: #ecfdf5; }
        .stat:nth-child(4) .stat-badge { color: #ea580c; background: #fff7ed; }
        .stat strong { display: block; font-size: 25px; letter-spacing: -.03em; }
        .stat small { color: #94a3b8; }

        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(290px, .75fr); gap: 18px; margin-top: 20px; }
        .panel { padding: 23px; border: 1px solid var(--line); border-radius: 19px; background: white; }
        .panel-head { display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 21px; }
        .panel-head h3 { margin: 0; font-size: 16px; }
        .panel-head span { color: #94a3b8; font-size: 11px; }
        .empty {
            display: grid;
            min-height: 185px;
            place-items: center;
            padding: 24px;
            text-align: center;
            border: 1px dashed #cbd5e1;
            border-radius: 15px;
            background: #f8fafc;
        }
        .empty-icon { display: grid; width: 48px; height: 48px; place-items: center; margin: 0 auto 12px; color: #6366f1; border-radius: 14px; background: #eef2ff; font-weight: 900; }
        .empty strong { display: block; font-size: 14px; }
        .empty p { max-width: 360px; margin: 6px auto 0; color: #94a3b8; font-size: 12px; line-height: 1.6; }

        .module-page {
            min-height: calc(100vh - 175px);
            display: grid;
            place-items: center;
            padding: 28px;
            text-align: center;
            border: 1px solid var(--line);
            border-radius: 24px;
            background: white;
        }
        .module-page-icon { display: grid; width: 68px; height: 68px; place-items: center; margin: 0 auto 18px; color: white; border-radius: 20px; background: linear-gradient(135deg, var(--primary), var(--cyan)); box-shadow: 0 16px 35px rgba(99, 102, 241, .22); font-size: 23px; font-weight: 900; }
        .module-page h2 { margin: 0; font-size: clamp(28px, 5vw, 42px); letter-spacing: -.045em; }
        .module-page p { max-width: 520px; margin: 10px auto 0; color: var(--muted); line-height: 1.7; }
        .module-status { display: inline-block; margin-top: 20px; padding: 8px 12px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 999px; background: #eef2ff; font-size: 11px; font-weight: 900; letter-spacing: .07em; text-transform: uppercase; }

        .products-head { display: flex; align-items: center; justify-content: space-between; gap: 18px; margin-bottom: 20px; }
        .products-head h2 { margin: 0; font-size: clamp(25px, 4vw, 34px); letter-spacing: -.04em; }
        .products-head p { margin: 6px 0 0; color: var(--muted); font-size: 13px; }
        .head-actions { display: flex; align-items: center; gap: 9px; }
        .print-all-link { padding: 11px 15px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 11px; background: #eef2ff; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .add-link { padding: 11px 15px; color: white; border-radius: 11px; background: linear-gradient(135deg, #4f46e5, #0891b2); box-shadow: 0 10px 25px rgba(79, 70, 229, .18); font-size: 12px; font-weight: 900; white-space: nowrap; }
        .product-metrics { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
        .product-metric { padding: 17px 18px; border: 1px solid var(--line); border-radius: 16px; background: white; }
        .product-metric small { display: block; color: #94a3b8; font-size: 11px; }
        .product-metric strong { display: block; margin-top: 7px; font-size: 24px; }
        .product-metric:nth-child(2) strong { color: #059669; }
        .product-metric:nth-child(3) strong { color: #d97706; }
        .product-metric:nth-child(4) strong { color: #dc2626; }
        .form-errors { margin-bottom: 18px; padding: 15px 17px; color: #991b1b; border: 1px solid #fecaca; border-radius: 14px; background: #fef2f2; font-size: 12px; }
        .form-errors strong { display: block; margin-bottom: 5px; }
        .form-errors ul { margin: 0; padding-left: 18px; line-height: 1.7; }
        .product-panel { margin-bottom: 18px; }
        .product-panel-title { margin: 0 0 17px; font-size: 16px; }
        .product-form-grid { display: grid; grid-template-columns: 1.4fr 1fr 1fr 1.2fr .8fr .7fr .8fr auto; gap: 11px; align-items: end; }
        .field { display: grid; gap: 7px; min-width: 0; }
        .field span { color: #64748b; font-size: 10px; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; }
        .field input,
        .field select,
        .filter-form input,
        .filter-form select,
        .table-input,
        .table-select {
            width: 100%;
            height: 42px;
            color: #1e293b;
            border: 1px solid #dbe3ed;
            border-radius: 10px;
            outline: none;
            background: #f8fafc;
            font: inherit;
            font-size: 12px;
            transition: .18s;
        }
        .field input,
        .field select,
        .filter-form input,
        .filter-form select { padding: 0 11px; }
        .field input:focus,
        .field select:focus,
        .filter-form input:focus,
        .filter-form select:focus,
        .table-input:focus,
        .table-select:focus { border-color: #818cf8; background: white; box-shadow: 0 0 0 3px rgba(99, 102, 241, .1); }
        .save-product { height: 42px; padding: 0 16px; color: white; border: 0; border-radius: 10px; background: #0f172a; cursor: pointer; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .list-head { display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 17px; }
        .list-head h3 { margin: 0; font-size: 16px; }
        .filter-form { display: grid; grid-template-columns: minmax(180px, 280px) 125px auto; gap: 8px; }
        .filter-button { height: 42px; padding: 0 14px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 10px; background: #eef2ff; cursor: pointer; font-size: 12px; font-weight: 900; }
        .table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 14px; }
        .product-table { width: 100%; min-width: 940px; border-collapse: collapse; }
        .product-table th { padding: 11px 10px; color: #94a3b8; border-bottom: 1px solid var(--line); background: #f8fafc; font-size: 9px; letter-spacing: .09em; text-align: left; text-transform: uppercase; }
        .product-table td { padding: 10px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        .product-table tr:last-child td { border-bottom: 0; }
        .table-input,
        .table-select { height: 38px; padding: 0 9px; }
        .table-input.product-name { min-width: 150px; font-weight: 800; }
        .product-image-cell { min-width: 190px; }
        .product-image-editor { display: grid; grid-template-columns: 52px minmax(115px, 1fr); gap: 9px; align-items: center; }
        .product-thumbnail { width: 52px; height: 52px; object-fit: cover; border: 1px solid #dbe3ed; border-radius: 11px; background: #f8fafc; }
        .image-preview-button { display: inline-grid; padding: 0; border: 0; border-radius: 11px; background: transparent; cursor: zoom-in; }
        .image-preview-button:focus-visible { outline: 3px solid rgba(99, 102, 241, .3); outline-offset: 2px; }
        .history-product { display: flex; align-items: center; gap: 9px; min-width: 220px; padding: 4px 0; }
        .history-product + .history-product { margin-top: 5px; border-top: 1px solid #edf2f7; }
        .history-product-image { width: 42px; height: 42px; object-fit: cover; border: 1px solid #dbe3ed; border-radius: 9px; background: #f8fafc; }
        .history-product-info { min-width: 0; }
        .history-product-info strong,
        .history-product-info span { display: block; }
        .history-product-info strong { overflow: hidden; max-width: 260px; text-overflow: ellipsis; white-space: nowrap; }
        .image-modal { position: fixed; inset: 0; z-index: 1000; display: none; align-items: center; justify-content: center; padding: 24px; background: rgba(2, 6, 23, .82); backdrop-filter: blur(5px); }
        .image-modal.is-open { display: flex; }
        .image-modal-card { position: relative; width: min(760px, 94vw); max-height: 92vh; padding: 16px; border-radius: 18px; background: white; box-shadow: 0 28px 80px rgba(0, 0, 0, .4); }
        .image-modal-photo { display: block; width: 100%; max-height: 72vh; object-fit: contain; border-radius: 13px; background: #f8fafc; }
        .image-modal-title { margin: 12px 42px 0 2px; overflow: hidden; color: #0f172a; font-size: 15px; font-weight: 900; text-overflow: ellipsis; white-space: nowrap; }
        .image-modal-close { position: absolute; right: 12px; top: 12px; display: grid; width: 36px; height: 36px; place-items: center; color: white; border: 0; border-radius: 50%; background: rgba(15, 23, 42, .82); cursor: pointer; font-size: 20px; }
        .table-file { display: block; width: 100%; color: #64748b; font-size: 9px; }
        .table-file::file-selector-button { margin-right: 6px; padding: 6px 8px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 7px; background: #eef2ff; cursor: pointer; font-size: 9px; font-weight: 800; }
        .remove-image-label { display: flex; align-items: center; gap: 5px; margin-top: 5px; color: #94a3b8; font-size: 9px; }
        .remove-image-label input { margin: 0; }
        .table-actions { display: flex; align-items: center; gap: 7px; }
        .update-button,
        .review-trigger,
        .delete-trigger,
        .delete-confirm { display: inline-grid; height: 35px; place-items: center; padding: 0 10px; border-radius: 9px; cursor: pointer; font-size: 10px; font-weight: 900; }
        .update-button { color: #4338ca; border: 1px solid #c7d2fe; background: #eef2ff; }
        .rating-badge { display: inline-flex; align-items: center; gap: 4px; padding: 7px 9px; color: #92400e; border-radius: 999px; background: #fffbeb; font-size: 10px; font-weight: 900; white-space: nowrap; }
        .rating-star { color: #f59e0b; font-size: 13px; }
        .rating-badge small { color: #94a3b8; font-size: 9px; }
        .review-details,
        .delete-details { position: relative; }
        .review-details summary,
        .delete-details summary { list-style: none; }
        .review-details summary::-webkit-details-marker,
        .delete-details summary::-webkit-details-marker { display: none; }
        .review-trigger { color: #047857; border: 1px solid #a7f3d0; background: #ecfdf5; }
        .review-popover { position: absolute; right: 0; bottom: calc(100% + 7px); z-index: 20; width: 290px; padding: 14px; border: 1px solid #a7f3d0; border-radius: 12px; background: white; box-shadow: 0 16px 40px rgba(15, 23, 42, .18); }
        .review-header { margin-bottom: 11px; }
        .review-header strong,
        .review-header span { display: block; }
        .review-header strong { font-size: 12px; }
        .review-header span { overflow: hidden; margin-top: 3px; color: #64748b; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .review-form { display: grid; gap: 10px; }
        .review-form .field { gap: 5px; }
        .review-form select,
        .review-form textarea { width: 100%; border: 1px solid #dbe3ed; border-radius: 9px; background: #f8fafc; font: inherit; font-size: 11px; }
        .review-form select { height: 38px; padding: 0 9px; }
        .review-form textarea { min-height: 82px; padding: 9px; resize: vertical; }
        .submit-review-button { height: 36px; color: white; border: 0; border-radius: 9px; background: #059669; cursor: pointer; font-size: 10px; font-weight: 900; }
        .delete-trigger { color: #b91c1c; border: 1px solid #fecaca; background: #fef2f2; }
        .delete-popover { position: absolute; right: 0; bottom: calc(100% + 7px); z-index: 10; width: 190px; padding: 11px; border: 1px solid #fecaca; border-radius: 11px; background: white; box-shadow: 0 14px 35px rgba(15, 23, 42, .15); }
        .delete-popover p { margin: 0 0 8px; color: #64748b; font-size: 10px; line-height: 1.45; }
        .delete-confirm { width: 100%; color: white; border: 0; background: #dc2626; }
        .product-empty { min-height: 230px; }
        .product-pagination { display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 17px; }
        .product-pagination a,
        .product-pagination span { padding: 8px 11px; border: 1px solid var(--line); border-radius: 9px; background: white; font-size: 11px; }
        .product-pagination .disabled { color: #cbd5e1; background: #f8fafc; }
        .product-pagination .page-number { color: #64748b; border: 0; background: transparent; }
        .module-form-grid { display: grid; grid-template-columns: repeat(6, minmax(100px, 1fr)) auto; gap: 11px; align-items: end; }
        .module-form-grid .wide { grid-column: span 2; }
        .sale-form { display: grid; gap: 16px; }
        .sale-meta-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 11px; }
        .sale-items-card { overflow: hidden; border: 1px solid var(--line); border-radius: 14px; background: #f8fafc; }
        .sale-items-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 14px; border-bottom: 1px solid var(--line); background: white; }
        .sale-items-toolbar strong { font-size: 12px; }
        .add-sale-item { height: 34px; padding: 0 12px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 9px; background: #eef2ff; cursor: pointer; font-size: 10px; font-weight: 900; }
        .sale-items { display: grid; gap: 10px; padding: 12px; }
        .sale-item-row { display: grid; grid-template-columns: minmax(190px, 1.6fr) minmax(125px, .9fr) minmax(85px, .55fr) minmax(85px, .55fr) minmax(105px, .7fr) minmax(85px, .55fr) minmax(100px, .65fr) auto; gap: 9px; align-items: end; padding: 11px; border: 1px solid #e2e8f0; border-radius: 11px; background: white; }
        .pack-size-field { transition: opacity .2s; }
        .pack-size-field.is-hidden { opacity: .45; }
        .pack-size-field.is-hidden input { background: #f1f5f9; }
        .sale-line-total { display: grid; height: 42px; align-items: center; padding: 0 11px; color: #059669; border-radius: 10px; background: #ecfdf5; font-size: 12px; font-weight: 900; white-space: nowrap; }
        .remove-sale-item { width: 42px; height: 42px; color: #b91c1c; border: 1px solid #fecaca; border-radius: 10px; background: #fef2f2; cursor: pointer; font-size: 17px; font-weight: 900; }
        .remove-sale-item:disabled { visibility: hidden; }
        .sale-form-footer { display: flex; align-items: center; justify-content: flex-end; gap: 16px; }
        .sale-grand-total { color: #64748b; font-size: 11px; }
        .sale-grand-total strong { display: inline-block; margin-left: 6px; color: #059669; font-size: 20px; }
        .muted { color: #94a3b8; font-size: 11px; }
        .money { color: #059669; font-weight: 900; white-space: nowrap; }
        .quantity { font-weight: 900; white-space: nowrap; }
        .status-badge { display: inline-block; padding: 6px 9px; border-radius: 999px; background: #f1f5f9; color: #475569; font-size: 9px; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; }
        .status-badge.active,
        .status-badge.completed,
        .status-badge.paid,
        .status-badge.receive { color: #047857; background: #ecfdf5; }
        .status-badge.pending,
        .status-badge.processing,
        .status-badge.unpaid { color: #b45309; background: #fffbeb; }
        .status-badge.inactive,
        .status-badge.cancelled,
        .status-badge.issue { color: #b91c1c; background: #fef2f2; }
        .inline-status { display: flex; gap: 7px; align-items: center; }
        .inline-status select { width: 112px; height: 35px; padding: 0 8px; border: 1px solid #dbe3ed; border-radius: 9px; background: #f8fafc; font-size: 10px; }
        .mini-button { height: 35px; padding: 0 10px; color: #4338ca; border: 1px solid #c7d2fe; border-radius: 9px; background: #eef2ff; cursor: pointer; font-size: 10px; font-weight: 900; }
        .activity-list { display: grid; gap: 9px; }
        .activity-row { display: flex; align-items: center; justify-content: space-between; gap: 13px; padding: 12px 13px; border: 1px solid #edf2f7; border-radius: 12px; background: #f8fafc; }
        .activity-info { display: flex; align-items: center; gap: 11px; min-width: 0; }
        .activity-mark { display: grid; width: 35px; height: 35px; place-items: center; flex: 0 0 auto; color: #4f46e5; border-radius: 10px; background: #eef2ff; font-size: 10px; font-weight: 900; }
        .activity-info strong,
        .activity-info small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .activity-info strong { font-size: 12px; }
        .activity-info small { margin-top: 3px; color: #94a3b8; font-size: 10px; }
        .stock-number { min-width: 35px; text-align: right; font-size: 13px; font-weight: 900; }
        .report-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; margin-bottom: 18px; }
        .report-grid .panel { min-width: 0; }
        .report-list { display: grid; gap: 8px; }
        .report-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 11px 12px; border: 1px solid #edf2f7; border-radius: 11px; background: #f8fafc; }
        .report-row strong { display: block; overflow: hidden; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
        .report-row small { display: block; margin-top: 3px; color: #94a3b8; font-size: 10px; }
        .order-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 18px; }
        .order-summary div { padding: 13px; text-align: center; border: 1px solid var(--line); border-radius: 12px; background: #f8fafc; }
        .order-summary strong { display: block; font-size: 18px; }
        .order-summary small { color: #94a3b8; font-size: 9px; text-transform: uppercase; }

        @media (max-width: 1050px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
            .product-form-grid { grid-template-columns: repeat(3, 1fr); }
            .module-form-grid { grid-template-columns: repeat(3, 1fr); }
            .sale-item-row { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .remove-sale-item { width: 100%; }
        }

        @media (max-width: 820px) {
            .sidebar { transform: translateX(-100%); }
            .app { margin-left: 0; }
            .menu-button,
            .close-sidebar { display: grid; }
            #sidebar-toggle:checked ~ .sidebar { transform: translateX(0); }
            #sidebar-toggle:checked ~ .mobile-overlay { visibility: visible; opacity: 1; }
        }

        @media (max-width: 560px) {
            .topbar { min-height: 68px; padding: 11px 13px; }
            .page-identity p { display: none; }
            .home-link { padding: 9px 11px; font-size: 12px; }
            .content { width: min(100% - 24px, 1240px); padding-top: 20px; }
            .stats { grid-template-columns: 1fr; }
            .stat { display: grid; grid-template-columns: auto 1fr; column-gap: 14px; align-items: center; }
            .stat-badge { grid-row: 1 / 3; margin: 0; }
            .panel { padding: 17px; }
            .module-page { padding: 22px 16px; }
            .products-head,
            .list-head { align-items: stretch; flex-direction: column; }
            .head-actions { display: grid; grid-template-columns: 1fr; }
            .add-link,
            .print-all-link { text-align: center; }
            .product-metrics { grid-template-columns: repeat(2, 1fr); }
            .product-form-grid,
            .module-form-grid,
            .filter-form,
            .report-grid { grid-template-columns: 1fr; }
            .module-form-grid .wide { grid-column: auto; }
            .sale-meta-grid,
            .sale-item-row { grid-template-columns: 1fr; }
            .sale-items-toolbar,
            .sale-form-footer { align-items: stretch; flex-direction: column; }
            .save-product { width: 100%; }
            .order-summary { grid-template-columns: repeat(2, 1fr); }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <input class="sidebar-toggle" type="checkbox" id="sidebar-toggle" aria-label="Toggle sidebar">

    <aside class="sidebar" aria-label="Main navigation">
        <div class="sidebar-head">
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-mark">O</span>
                <span><strong>OMNIHUB</strong><small>COMPANY ERP</small></span>
            </a>
            <label class="close-sidebar" for="sidebar-toggle" aria-label="Close sidebar">×</label>
        </div>

        <p class="nav-caption">Workspace</p>
        <nav class="side-nav">
            @foreach ($modules as $module)
                <a href="{{ route($module['route']) }}" class="side-link{{ $currentRoute === $module['route'] ? ' active' : '' }}" @if ($currentRoute === $module['route']) aria-current="page" @endif>
                    <span class="side-icon">{{ $module['letter'] }}</span>
                    <span>{{ $module['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-user">
            <div class="profile">
                <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <span class="profile-text">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ auth()->user()->email }}</small>
                </span>
            </div>
            <form class="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout" type="submit">Log out</button>
            </form>
        </div>
    </aside>

    <label class="mobile-overlay" for="sidebar-toggle" aria-label="Close sidebar"></label>

    <div class="app">
        <header class="topbar">
            <div class="page-identity">
                <label class="menu-button" for="sidebar-toggle" aria-label="Open sidebar">☰</label>
                <div>
                    <h1>{{ $activeModule['label'] }}</h1>
                    <p id="live-cambodia-time">{{ now()->format('l, F j, Y · h:i:s A') }} · Cambodia time</p>
                </div>
            </div>
            <a class="home-link" href="{{ route('home') }}">View home →</a>
        </header>

        <main class="content">
            @if (session('success'))
                <div class="flash">{{ session('success') }}</div>
            @endif

            @if ($currentRoute === 'dashboard')
                <section class="welcome">
                    <small>AUTHENTICATED WORKSPACE</small>
                    <h2>Welcome, {{ auth()->user()->name }}</h2>
                    <p>Your secure Laravel session is active. Use the sidebar to manage products, inventory, sales, orders, customers, and reports.</p>
                </section>

                <section class="stats" aria-label="Dashboard summary">
                    <article class="stat"><span class="stat-badge">PR</span><strong>{{ $dashboardStats['products'] }}</strong><small>Products</small></article>
                    <article class="stat"><span class="stat-badge">ST</span><strong>{{ $dashboardStats['stock'] }}</strong><small>Stock units</small></article>
                    <article class="stat"><span class="stat-badge">SA</span><strong>${{ number_format((float) $dashboardStats['sales_today'], 2) }}</strong><small>Sales today</small></article>
                    <article class="stat"><span class="stat-badge">OR</span><strong>{{ $dashboardStats['pending_orders'] }}</strong><small>Pending orders</small></article>
                </section>

                <section class="dashboard-grid">
                    <article class="panel">
                        <div class="panel-head"><h3>Recent sales</h3><span>{{ $recentSales->count() }} latest</span></div>
                        @forelse ($recentSales as $sale)
                            @if ($loop->first)<div class="activity-list">@endif
                            <div class="activity-row">
                                <div class="activity-info"><span class="activity-mark">S</span><span><strong>{{ $sale->items->pluck('product.name')->filter()->join(', ') ?: 'Deleted products' }}</strong><small>{{ $sale->invoiceNumber() }} · {{ $sale->customer?->name ?? 'Walk-in customer' }} · {{ $sale->sold_at->diffForHumans() }}</small></span></div>
                                <span class="money">${{ number_format((float) $sale->total, 2) }}</span>
                            </div>
                            @if ($loop->last)</div>@endif
                        @empty
                            <div class="empty"><div><span class="empty-icon">A</span><strong>No sales yet</strong><p>Record your first sale from the Sales page.</p></div></div>
                        @endforelse
                    </article>
                    <article class="panel">
                        <div class="panel-head"><h3>Low stock</h3><span>{{ $lowStockProducts->count() }} items</span></div>
                        @forelse ($lowStockProducts as $product)
                            @if ($loop->first)<div class="activity-list">@endif
                            <div class="activity-row">
                                <div class="activity-info"><span class="activity-mark">P</span><span><strong>{{ $product->name }}</strong><small>{{ $product->sku }}</small></span></div>
                                <span class="stock-number">{{ $product->stock }}</span>
                            </div>
                            @if ($loop->last)</div>@endif
                        @empty
                            <div class="empty"><div><span class="empty-icon">✓</span><strong>Stock looks good</strong><p>No products are at or below five units.</p></div></div>
                        @endforelse
                    </article>
                </section>
            @elseif ($currentRoute === 'products.index')
                <div class="products-head">
                    <div>
                        <h2>Products</h2>
                        <p>Create products, update price and stock, or search your catalogue.</p>
                    </div>
                    <a class="add-link" href="#add-product">+ Add product</a>
                </div>

                <section class="product-metrics" aria-label="Product summary">
                    <article class="product-metric"><small>Total products</small><strong>{{ $productStats['total'] }}</strong></article>
                    <article class="product-metric"><small>Active products</small><strong>{{ $productStats['active'] }}</strong></article>
                    <article class="product-metric"><small>Low stock (1–5)</small><strong>{{ $productStats['low_stock'] }}</strong></article>
                    <article class="product-metric"><small>Out of stock</small><strong>{{ $productStats['out_of_stock'] }}</strong></article>
                </section>

                @if ($errors->any())
                    <div class="form-errors">
                        <strong>Please fix these fields:</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <section class="panel product-panel" id="add-product">
                    <h3 class="product-panel-title">Add new product</h3>
                    <form class="product-form-grid" method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data">
                        @csrf
                        <label class="field">
                            <span>Product name</span>
                            <input type="text" name="name" value="{{ old('name') }}" maxlength="150" placeholder="Premium Cement" required>
                        </label>
                        <label class="field">
                            <span>SKU</span>
                            <input type="text" name="sku" value="{{ old('sku') }}" maxlength="60" placeholder="CEM-001" required>
                        </label>
                        <label class="field">
                            <span>Category</span>
                            <input type="text" name="category" value="{{ old('category') }}" maxlength="100" placeholder="Cement">
                        </label>
                        <label class="field">
                            <span>Product image</span>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <label class="field">
                            <span>Price ($)</span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" max="9999999999.99" step="0.01" placeholder="0.00" required>
                        </label>
                        <label class="field">
                            <span>Stock</span>
                            <input type="number" name="stock" value="{{ old('stock', 0) }}" min="0" max="4294967295" step="1" required>
                        </label>
                        <label class="field">
                            <span>Status</span>
                            <select name="status" required>
                                <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                            </select>
                        </label>
                        <button class="save-product" type="submit">Save product</button>
                    </form>
                </section>

                <section class="panel">
                    <div class="list-head">
                        <h3>Product catalogue</h3>
                        <form class="filter-form" method="GET" action="{{ route('products.index') }}">
                            <input type="search" name="q" value="{{ $search }}" placeholder="Search name, SKU or category">
                            <select name="status" aria-label="Filter status">
                                <option value="all" @selected($status === 'all')>All status</option>
                                <option value="active" @selected($status === 'active')>Active</option>
                                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                            </select>
                            <button class="filter-button" type="submit">Search</button>
                        </form>
                    </div>

                    @forelse ($products as $product)
                        @if ($loop->first)
                            <div class="table-wrap">
                                <table class="product-table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>SKU</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Status</th>
                                            <th>Rating</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        @endif

                                        <tr>
                                            <td class="product-image-cell">
                                                <div class="product-image-editor">
                                                    <button class="image-preview-button" type="button" data-image-preview="{{ $product->image_url }}" data-image-title="{{ $product->name }}" aria-label="View larger image of {{ $product->name }}">
                                                        <img class="product-thumbnail" src="{{ $product->image_url }}" alt="{{ $product->name }}">
                                                    </button>
                                                    <div>
                                                        <input class="table-file" form="update-product-{{ $product->id }}" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                                                        @if ($product->image)
                                                            <label class="remove-image-label"><input form="update-product-{{ $product->id }}" type="checkbox" name="remove_image" value="1"> Remove image</label>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td><input class="table-input product-name" form="update-product-{{ $product->id }}" type="text" name="name" value="{{ $product->name }}" maxlength="150" required></td>
                                            <td><input class="table-input" form="update-product-{{ $product->id }}" type="text" name="sku" value="{{ $product->sku }}" maxlength="60" required></td>
                                            <td><input class="table-input" form="update-product-{{ $product->id }}" type="text" name="category" value="{{ $product->category }}" maxlength="100" placeholder="—"></td>
                                            <td><input class="table-input" form="update-product-{{ $product->id }}" type="number" name="price" value="{{ $product->price }}" min="0" max="9999999999.99" step="0.01" required></td>
                                            <td><input class="table-input" form="update-product-{{ $product->id }}" type="number" name="stock" value="{{ $product->stock }}" min="0" max="4294967295" step="1" required></td>
                                            <td>
                                                <select class="table-select" form="update-product-{{ $product->id }}" name="status" required>
                                                    <option value="active" @selected($product->status === 'active')>Active</option>
                                                    <option value="inactive" @selected($product->status === 'inactive')>Inactive</option>
                                                </select>
                                            </td>
                                            <td>
                                                <span class="rating-badge" title="Average customer rating">
                                                    <span class="rating-star">★</span>
                                                    {{ number_format($product->averageRating(), 1) }}
                                                    <small>({{ $product->reviews_count ?? 0 }})</small>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="table-actions">
                                                    <form id="update-product-{{ $product->id }}" method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PATCH')
                                                    </form>
                                                    <button class="update-button" form="update-product-{{ $product->id }}" type="submit">Update</button>
                                                    <details class="review-details">
                                                        <summary class="review-trigger">Review</summary>
                                                        <div class="review-popover">
                                                            <div class="review-header">
                                                                <strong>Review product</strong>
                                                                <span>{{ $product->name }}</span>
                                                            </div>
                                                            <form class="review-form" method="POST" action="{{ route('products.reviews.store', $product) }}">
                                                                @csrf
                                                                <label class="field">
                                                                    <span>Rating</span>
                                                                    <select name="rating" required>
                                                                        <option value="5">★★★★★ Excellent</option>
                                                                        <option value="4">★★★★☆ Good</option>
                                                                        <option value="3">★★★☆☆ Average</option>
                                                                        <option value="2">★★☆☆☆ Poor</option>
                                                                        <option value="1">★☆☆☆☆ Bad</option>
                                                                    </select>
                                                                </label>
                                                                <label class="field">
                                                                    <span>Comment</span>
                                                                    <textarea name="comment" maxlength="1000" placeholder="Write product review"></textarea>
                                                                </label>
                                                                <button class="submit-review-button" type="submit">Submit review</button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                    <details class="delete-details">
                                                        <summary class="delete-trigger">Delete</summary>
                                                        <div class="delete-popover">
                                                            <p>Delete <strong>{{ $product->name }}</strong>? This cannot be undone.</p>
                                                            <form method="POST" action="{{ route('products.destroy', $product) }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button class="delete-confirm" type="submit">Confirm delete</button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                </div>
                                            </td>
                                        </tr>

                        @if ($loop->last)
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @empty
                        <div class="empty product-empty">
                            <div>
                                <span class="empty-icon">P</span>
                                <strong>No products found</strong>
                                <p>Add your first product above, or change the search and status filter.</p>
                            </div>
                        </div>
                    @endforelse

                    @if ($products->hasPages())
                        <nav class="product-pagination" aria-label="Product pages">
                            @if ($products->onFirstPage())
                                <span class="disabled">← Previous</span>
                            @else
                                <a href="{{ $products->previousPageUrl() }}">← Previous</a>
                            @endif

                            <span class="page-number">Page {{ $products->currentPage() }} of {{ $products->lastPage() }}</span>

                            @if ($products->hasMorePages())
                                <a href="{{ $products->nextPageUrl() }}">Next →</a>
                            @else
                                <span class="disabled">Next →</span>
                            @endif
                        </nav>
                    @endif
                </section>
            @elseif ($currentRoute === 'inventory.index')
                <div class="products-head"><div><h2>Inventory</h2><p>Receive stock, issue stock, and review every inventory movement.</p></div><a class="add-link" href="#stock-movement">+ Stock movement</a></div>
                <section class="product-metrics" aria-label="Inventory summary">
                    <article class="product-metric"><small>Available units</small><strong>{{ $inventoryStats['stock'] }}</strong></article>
                    <article class="product-metric"><small>Total received</small><strong>{{ $inventoryStats['received'] }}</strong></article>
                    <article class="product-metric"><small>Total issued</small><strong>{{ $inventoryStats['issued'] }}</strong></article>
                    <article class="product-metric"><small>Low-stock products</small><strong>{{ $inventoryStats['low_stock'] }}</strong></article>
                </section>

                @if ($errors->any())
                    <div class="form-errors"><strong>Please fix these fields:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="panel product-panel" id="stock-movement">
                    <h3 class="product-panel-title">Add stock movement</h3>
                    <form class="module-form-grid" method="POST" action="{{ route('inventory.store') }}">
                        @csrf
                        <label class="field wide"><span>Product</span><select name="product_id" required><option value="">Select product</option>@foreach ($products as $product)<option value="{{ $product->id }}" @selected((string) old('product_id') === (string) $product->id)>{{ $product->name }} · {{ $product->stock }} units</option>@endforeach</select></label>
                        <label class="field"><span>Movement</span><select name="type" required><option value="receive" @selected(old('type', 'receive') === 'receive')>Receive stock</option><option value="issue" @selected(old('type') === 'issue')>Issue stock</option></select></label>
                        <label class="field"><span>Quantity</span><input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" step="1" required></label>
                        <label class="field wide"><span>Note</span><input type="text" name="note" value="{{ old('note') }}" maxlength="255" placeholder="Supplier, reference or reason"></label>
                        <button class="save-product" type="submit">Save movement</button>
                    </form>
                </section>

                <section class="panel">
                    <div class="panel-head"><h3>Stock history</h3><span>{{ $movements->total() }} movements</span></div>
                    @forelse ($movements as $movement)
                        @if ($loop->first)<div class="table-wrap"><table class="product-table"><thead><tr><th>Date</th><th>Product</th><th>SKU</th><th>Type</th><th>Quantity</th><th>Note</th><th>User</th></tr></thead><tbody>@endif
                        <tr>
                            <td class="muted">{{ $movement->created_at->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') }}</td>
                            <td><strong>{{ $movement->product?->name ?? 'Deleted product' }}</strong></td>
                            <td>{{ $movement->product?->sku ?? '—' }}</td>
                            <td><span class="status-badge {{ $movement->type }}">{{ $movement->type }}</span></td>
                            <td class="quantity">{{ $movement->type === 'receive' ? '+' : '−' }}{{ $movement->quantity }}</td>
                            <td class="muted">{{ $movement->note ?: '—' }}</td>
                            <td class="muted">{{ $movement->creator?->name ?? 'System' }}</td>
                        </tr>
                        @if ($loop->last)</tbody></table></div>@endif
                    @empty
                        <div class="empty product-empty"><div><span class="empty-icon">I</span><strong>No stock movements</strong><p>Receive or issue stock using the form above.</p></div></div>
                    @endforelse
                    @if ($movements->hasPages())
                        <nav class="product-pagination">@if ($movements->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $movements->previousPageUrl() }}">← Previous</a>@endif<span class="page-number">Page {{ $movements->currentPage() }} of {{ $movements->lastPage() }}</span>@if ($movements->hasMorePages())<a href="{{ $movements->nextPageUrl() }}">Next →</a>@else<span class="disabled">Next →</span>@endif</nav>
                    @endif
                </section>
            @elseif ($currentRoute === 'sales.index')
                <div class="products-head"><div><h2>Sales</h2><p>Record completed sales and automatically deduct product stock.</p></div><div class="head-actions"><a class="print-all-link" href="{{ route('sales.print-all') }}" target="_blank" rel="noopener">Print all</a><a class="add-link" href="#record-sale">+ Record sale</a></div></div>
                <section class="product-metrics" aria-label="Sales summary">
                    <article class="product-metric"><small>Total revenue</small><strong>${{ number_format((float) $salesStats['revenue'], 2) }}</strong></article>
                    <article class="product-metric"><small>Revenue today</small><strong>${{ number_format((float) $salesStats['today'], 2) }}</strong></article>
                    <article class="product-metric"><small>Transactions</small><strong>{{ $salesStats['transactions'] }}</strong></article>
                    <article class="product-metric"><small>Units sold</small><strong>{{ $salesStats['units'] }}</strong></article>
                </section>

                @if ($errors->any())
                    <div class="form-errors"><strong>Please fix these fields:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="panel product-panel" id="record-sale">
                    <h3 class="product-panel-title">Record new sale</h3>
                    @php($oldSaleItems = old('items', [['product_id' => '', 'sale_type' => 'general', 'quantity' => 1, 'units_per_pack' => 1, 'unit_price' => '', 'discount_rate' => 0]]))
                    <form class="sale-form" method="POST" action="{{ route('sales.store') }}" id="multi-sale-form">
                        @csrf
                        <div class="sale-meta-grid">
                            <label class="field"><span>Customer</span><select name="customer_id"><option value="">Walk-in customer</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>@endforeach</select></label>
                            <label class="field"><span>Payment bank</span><select name="payment_method" required>@foreach ($paymentBanks as $key => $bank)<option value="{{ $key }}" @selected(old('payment_method', 'aba') === $key)>{{ strtoupper($bank['name'] ?? $key) }}</option>@endforeach</select></label>
                        </div>

                        <div class="sale-items-card">
                            <div class="sale-items-toolbar"><strong>Invoice products <span class="muted">· Pack size &amp; discount are set manually</span></strong><button class="add-sale-item" id="add-sale-item" type="button">+ Add product</button></div>
                            <div class="sale-items" id="sale-items">
                                @foreach ($oldSaleItems as $index => $oldItem)
                                    @include('dashboard._invoice-item-row', ['itemIndex' => $index, 'oldItem' => $oldItem])
                                @endforeach
                            </div>
                        </div>

                        <div class="sale-form-footer"><span class="sale-grand-total">Invoice total <strong id="sale-grand-total">$0.00</strong></span><button class="save-product" type="submit">Record invoice</button></div>
                    </form>

                    <template id="sale-item-template">
                        @include('dashboard._invoice-item-row', ['itemIndex' => '__INDEX__', 'oldItem' => []])
                    </template>
                </section>

                <section class="panel">
                    <div class="panel-head"><h3>Sales history</h3><span>{{ $sales->total() }} transactions</span></div>
                    @forelse ($sales as $sale)
                        @if ($loop->first)<div class="table-wrap"><table class="product-table"><thead><tr><th>Date</th><th>Invoice</th><th>Products</th><th>Customer</th><th>Units</th><th>Bank</th><th>Total</th><th>Action</th></tr></thead><tbody>@endif
                        <tr>
                            <td class="muted">{{ $sale->sold_at->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') }}</td>
                            <td><strong>{{ $sale->invoiceNumber() }}</strong></td>
                            <td>@foreach ($sale->items as $item)<div class="history-product">@if ($item->product)<button class="image-preview-button" type="button" data-image-preview="{{ $item->product->image_url }}" data-image-title="{{ $item->product->name }}" aria-label="View larger image of {{ $item->product->name }}"><img class="history-product-image" src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"></button>@endif<div class="history-product-info"><strong>{{ $item->product?->name ?? 'Deleted product' }}</strong><span class="muted">{{ $item->product?->sku ?? '—' }} · {{ $item->saleTypeLabel() }} · {{ $item->sale_quantity }} {{ $item->sellingUnitLabel() }} × ${{ number_format((float) $item->unit_price, 2) }}@if ((float) $item->discount_rate > 0) · −{{ number_format((float) $item->discount_rate, 2) }}%@endif</span></div></div>@endforeach</td>
                            <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                            <td class="quantity">{{ $sale->items->sum('quantity') }}</td>
                            <td>{{ strtoupper($sale->payment_method ?? 'ABA') }}</td>
                            <td class="money">${{ number_format((float) $sale->total, 2) }}</td>
                            <td><div class="table-actions"><a class="update-button" href="{{ route('sales.invoice', $sale) }}" target="_blank" rel="noopener">Print</a><details class="delete-details"><summary class="delete-trigger">Delete</summary><div class="delete-popover"><p>Delete this invoice and restore {{ $sale->items->sum('quantity') }} stock units?</p><form method="POST" action="{{ route('sales.destroy', $sale) }}">@csrf @method('DELETE')<button class="delete-confirm" type="submit">Confirm delete</button></form></div></details></div></td>
                        </tr>
                        @if ($loop->last)</tbody></table></div>@endif
                    @empty
                        <div class="empty product-empty"><div><span class="empty-icon">S</span><strong>No sales recorded</strong><p>Add products and record your first sale above.</p></div></div>
                    @endforelse
                    @if ($sales->hasPages())
                        <nav class="product-pagination">@if ($sales->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $sales->previousPageUrl() }}">← Previous</a>@endif<span class="page-number">Page {{ $sales->currentPage() }} of {{ $sales->lastPage() }}</span>@if ($sales->hasMorePages())<a href="{{ $sales->nextPageUrl() }}">Next →</a>@else<span class="disabled">Next →</span>@endif</nav>
                    @endif
                </section>
            @elseif ($currentRoute === 'orders.index')
                <div class="products-head"><div><h2>Orders</h2><p>Create customer orders and update their processing status.</p></div><a class="add-link" href="#create-order">+ Create order</a></div>
                <section class="product-metrics" aria-label="Order summary">
                    <article class="product-metric"><small>Pending</small><strong>{{ $orderStats['pending'] }}</strong></article>
                    <article class="product-metric"><small>Processing</small><strong>{{ $orderStats['processing'] }}</strong></article>
                    <article class="product-metric"><small>Completed</small><strong>{{ $orderStats['completed'] }}</strong></article>
                    <article class="product-metric"><small>Order value</small><strong>${{ number_format((float) $orderStats['total'], 2) }}</strong></article>
                </section>

                @if ($errors->any())
                    <div class="form-errors"><strong>Please fix these fields:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="panel product-panel" id="create-order">
                    <h3 class="product-panel-title">Create new order</h3>
                    @php($oldOrderItems = old('items', [['product_id' => '', 'sale_type' => 'general', 'quantity' => 1, 'units_per_pack' => 1, 'unit_price' => '', 'discount_rate' => 0]]))
                    <form class="sale-form" method="POST" action="{{ route('orders.store') }}" id="multi-order-form">
                        @csrf
                        <div class="module-form-grid">
                            <label class="field wide"><span>Customer</span><select name="customer_id"><option value="">Walk-in customer</option>@foreach ($customers as $customer)<option value="{{ $customer->id }}" @selected((string) old('customer_id') === (string) $customer->id)>{{ $customer->name }}</option>@endforeach</select></label>
                            <label class="field"><span>Payment bank</span><select name="payment_method" required>@foreach ($paymentBanks as $key => $bank)<option value="{{ $key }}" @selected(old('payment_method', 'aba') === $key)>{{ strtoupper($bank['name'] ?? $key) }}</option>@endforeach</select></label>
                            <label class="field"><span>Payment</span><select name="payment_status" required><option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>Unpaid / មិនទាន់បង់</option><option value="paid" @selected(old('payment_status') === 'paid')>Paid / បានបង់</option></select></label>
                            <label class="field"><span>Order status</span><select name="status" required><option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option><option value="processing" @selected(old('status') === 'processing')>Processing</option><option value="completed" @selected(old('status') === 'completed')>Completed</option><option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option></select></label>
                        </div>
                        <div class="sale-items-card">
                            <div class="sale-items-toolbar"><strong>Order products <span class="muted">· Pack size &amp; discount are set manually</span></strong><button class="add-sale-item" id="add-order-item" type="button">+ Add product</button></div>
                            <div class="sale-items" id="order-items">
                                @foreach ($oldOrderItems as $index => $oldItem)
                                    @include('dashboard._invoice-item-row', ['itemIndex' => $index, 'oldItem' => $oldItem])
                                @endforeach
                            </div>
                        </div>
                        <div class="sale-form-footer"><span class="sale-grand-total">Order total <strong id="order-grand-total">$0.00</strong></span><button class="save-product" type="submit">Create order</button></div>
                    </form>
                    <template id="order-item-template">
                        @include('dashboard._invoice-item-row', ['itemIndex' => '__INDEX__', 'oldItem' => []])
                    </template>
                </section>

                <section class="panel">
                    <div class="panel-head"><h3>Order history</h3><span>{{ $orders->total() }} orders</span></div>
                    @forelse ($orders as $order)
                        @if ($loop->first)<div class="table-wrap"><table class="product-table"><thead><tr><th>Order</th><th>Customer</th><th>Products</th><th>Units</th><th>Bank</th><th>Payment</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>@endif
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong><br><span class="muted">{{ $order->ordered_at->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') }}</span></td>
                            <td>{{ $order->customer?->name ?? 'Walk-in' }}</td>
                            <td>@foreach ($order->items as $item)<div class="history-product">@if ($item->product)<button class="image-preview-button" type="button" data-image-preview="{{ $item->product->image_url }}" data-image-title="{{ $item->product->name }}" aria-label="View larger image of {{ $item->product->name }}"><img class="history-product-image" src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}"></button>@endif<div class="history-product-info"><strong>{{ $item->product?->name ?? 'Deleted product' }}</strong><span class="muted">{{ $item->saleTypeLabel() }} · {{ $item->sale_quantity }} {{ $item->sellingUnitLabel() }}@if ((float) $item->discount_rate > 0) · −{{ number_format((float) $item->discount_rate, 2) }}%@endif</span></div></div>@endforeach</td>
                            <td class="quantity">{{ $order->items->sum('quantity') }}</td>
                            <td>{{ strtoupper($order->payment_method ?? 'ABA') }}</td>
                            <td><span class="status-badge {{ $order->payment_status }}">{{ $order->payment_status }}</span></td>
                            <td class="money">${{ number_format((float) $order->total, 2) }}</td>
                            <td><span class="status-badge {{ $order->status }}">{{ $order->status }}</span></td>
                            <td>
                                <div class="table-actions">
                                    <a class="update-button" href="{{ route('orders.invoice', $order) }}" target="_blank" rel="noopener">Print</a>
                                    <form class="inline-status" method="POST" action="{{ route('orders.update', $order) }}">@csrf @method('PATCH')<select name="status"><option value="pending" @selected($order->status === 'pending')>Pending</option><option value="processing" @selected($order->status === 'processing')>Processing</option><option value="completed" @selected($order->status === 'completed')>Completed</option><option value="cancelled" @selected($order->status === 'cancelled')>Cancelled</option></select><select name="payment_status"><option value="unpaid" @selected($order->payment_status === 'unpaid')>Unpaid</option><option value="paid" @selected($order->payment_status === 'paid')>Paid</option></select><button class="mini-button" type="submit">Save</button></form>
                                    <details class="delete-details"><summary class="delete-trigger">Delete</summary><div class="delete-popover"><p>Delete order <strong>{{ $order->order_number }}</strong>?</p><form method="POST" action="{{ route('orders.destroy', $order) }}">@csrf @method('DELETE')<button class="delete-confirm" type="submit">Confirm delete</button></form></div></details>
                                </div>
                            </td>
                        </tr>
                        @if ($loop->last)</tbody></table></div>@endif
                    @empty
                        <div class="empty product-empty"><div><span class="empty-icon">O</span><strong>No orders yet</strong><p>Create your first customer order above.</p></div></div>
                    @endforelse
                    @if ($orders->hasPages())
                        <nav class="product-pagination">@if ($orders->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $orders->previousPageUrl() }}">← Previous</a>@endif<span class="page-number">Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>@if ($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">Next →</a>@else<span class="disabled">Next →</span>@endif</nav>
                    @endif
                </section>
            @elseif ($currentRoute === 'customers.index')
                <div class="products-head"><div><h2>Customers</h2><p>Manage customer contacts, addresses, and account status.</p></div><a class="add-link" href="#add-customer">+ Add customer</a></div>
                <section class="product-metrics" aria-label="Customer summary">
                    <article class="product-metric"><small>Total customers</small><strong>{{ $customerStats['total'] }}</strong></article>
                    <article class="product-metric"><small>Active</small><strong>{{ $customerStats['active'] }}</strong></article>
                    <article class="product-metric"><small>Inactive</small><strong>{{ $customerStats['inactive'] }}</strong></article>
                    <article class="product-metric"><small>New this month</small><strong>{{ $customerStats['new_month'] }}</strong></article>
                </section>

                @if ($errors->any())
                    <div class="form-errors"><strong>Please fix these fields:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
                @endif

                <section class="panel product-panel" id="add-customer">
                    <h3 class="product-panel-title">Add new customer</h3>
                    <form class="module-form-grid" method="POST" action="{{ route('customers.store') }}">
                        @csrf
                        <label class="field"><span>Name</span><input type="text" name="name" value="{{ old('name') }}" maxlength="150" placeholder="Customer name" required></label>
                        <label class="field"><span>Email</span><input type="email" name="email" value="{{ old('email') }}" maxlength="150" placeholder="customer@example.com"></label>
                        <label class="field"><span>Phone</span><input type="text" name="phone" value="{{ old('phone') }}" maxlength="50" placeholder="012 345 678"></label>
                        <label class="field wide"><span>Address</span><input type="text" name="address" value="{{ old('address') }}" maxlength="500" placeholder="Customer address"></label>
                        <label class="field"><span>Status</span><select name="status"><option value="active" @selected(old('status', 'active') === 'active')>Active</option><option value="inactive" @selected(old('status') === 'inactive')>Inactive</option></select></label>
                        <button class="save-product" type="submit">Save customer</button>
                    </form>
                </section>

                <section class="panel">
                    <div class="list-head"><h3>Customer directory</h3><form class="filter-form" method="GET" action="{{ route('customers.index') }}"><input type="search" name="q" value="{{ $search }}" placeholder="Search name, email or phone"><select name="status"><option value="all" @selected($status === 'all')>All status</option><option value="active" @selected($status === 'active')>Active</option><option value="inactive" @selected($status === 'inactive')>Inactive</option></select><button class="filter-button" type="submit">Search</button></form></div>
                    @forelse ($customers as $customer)
                        @if ($loop->first)<div class="table-wrap"><table class="product-table"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Status</th><th>Actions</th></tr></thead><tbody>@endif
                        <tr>
                            <td><input class="table-input product-name" form="update-customer-{{ $customer->id }}" type="text" name="name" value="{{ $customer->name }}" maxlength="150" required></td>
                            <td><input class="table-input" form="update-customer-{{ $customer->id }}" type="email" name="email" value="{{ $customer->email }}" maxlength="150" placeholder="—"></td>
                            <td><input class="table-input" form="update-customer-{{ $customer->id }}" type="text" name="phone" value="{{ $customer->phone }}" maxlength="50" placeholder="—"></td>
                            <td><input class="table-input" form="update-customer-{{ $customer->id }}" type="text" name="address" value="{{ $customer->address }}" maxlength="500" placeholder="—"></td>
                            <td><select class="table-select" form="update-customer-{{ $customer->id }}" name="status"><option value="active" @selected($customer->status === 'active')>Active</option><option value="inactive" @selected($customer->status === 'inactive')>Inactive</option></select></td>
                            <td><div class="table-actions"><form id="update-customer-{{ $customer->id }}" method="POST" action="{{ route('customers.update', $customer) }}">@csrf @method('PATCH')</form><button class="update-button" form="update-customer-{{ $customer->id }}" type="submit">Update</button><details class="delete-details"><summary class="delete-trigger">Delete</summary><div class="delete-popover"><p>Delete <strong>{{ $customer->name }}</strong>?</p><form method="POST" action="{{ route('customers.destroy', $customer) }}">@csrf @method('DELETE')<button class="delete-confirm" type="submit">Confirm delete</button></form></div></details></div></td>
                        </tr>
                        @if ($loop->last)</tbody></table></div>@endif
                    @empty
                        <div class="empty product-empty"><div><span class="empty-icon">C</span><strong>No customers found</strong><p>Add your first customer or change the search filters.</p></div></div>
                    @endforelse
                    @if ($customers->hasPages())
                        <nav class="product-pagination">@if ($customers->onFirstPage())<span class="disabled">← Previous</span>@else<a href="{{ $customers->previousPageUrl() }}">← Previous</a>@endif<span class="page-number">Page {{ $customers->currentPage() }} of {{ $customers->lastPage() }}</span>@if ($customers->hasMorePages())<a href="{{ $customers->nextPageUrl() }}">Next →</a>@else<span class="disabled">Next →</span>@endif</nav>
                    @endif
                </section>
            @elseif ($currentRoute === 'reports.index')
    <div class="products-head">
        <div>
            <h2>Reports</h2>
            <p>Review sales + orders revenue, inventory, low stock, and performance.</p>
        </div>
        <span class="module-status">Live database report</span>
    </div>

    <section class="product-metrics" aria-label="Report summary">
        <article class="product-metric">
            <small>Total (Sales + Orders)</small>
            <strong>${{ number_format((float) ($reportStats['total_revenue'] ?? 0), 2) }}</strong>
        </article>
        <article class="product-metric">
            <small>Sales revenue</small>
            <strong>${{ number_format((float) ($reportStats['sales_revenue'] ?? 0), 2) }}</strong>
        </article>
        <article class="product-metric">
            <small>Orders revenue</small>
            <strong>${{ number_format((float) ($reportStats['orders_revenue'] ?? 0), 2) }}</strong>
        </article>
        <article class="product-metric">
            <small>This month (both)</small>
            <strong>${{ number_format((float) ($reportStats['month_revenue'] ?? 0), 2) }}</strong>
        </article>
        <article class="product-metric">
            <small>Inventory value</small>
            <strong>${{ number_format((float) ($reportStats['inventory_value'] ?? 0), 2) }}</strong>
        </article>
        <article class="product-metric">
            <small>Customers</small>
            <strong>{{ $reportStats['customers'] ?? 0 }}</strong>
        </article>
    </section>

    <section class="report-grid">
        <article class="panel">
            <div class="panel-head">
                <h3>Top-selling products</h3>
                <span>By units sold (sales)</span>
            </div>
            @forelse ($topProducts as $item)
                @if ($loop->first)<div class="report-list">@endif
                <div class="report-row">
                    <span>
                        <strong>{{ $item->product?->name ?? 'Deleted product' }}</strong>
                        <small>{{ $item->units_sold }} units sold</small>
                    </span>
                    <span class="money">${{ number_format((float) $item->revenue, 2) }}</span>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <div class="empty">
                    <div>
                        <span class="empty-icon">T</span>
                        <strong>No sales data</strong>
                        <p>Top products appear after sales are recorded.</p>
                    </div>
                </div>
            @endforelse
        </article>

        <article class="panel">
            <div class="panel-head">
                <h3>Low-stock products</h3>
                <span>5 units or fewer</span>
            </div>
            @forelse ($lowStockProducts as $product)
                @if ($loop->first)<div class="report-list">@endif
                <div class="report-row">
                    <span>
                        <strong>{{ $product->name }}</strong>
                        <small>{{ $product->sku }} · {{ $product->category ?: 'Uncategorized' }}</small>
                    </span>
                    <span class="stock-number">{{ $product->stock }}</span>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <div class="empty">
                    <div>
                        <span class="empty-icon">✓</span>
                        <strong>Stock looks good</strong>
                        <p>No products at or below 5 units.</p>
                    </div>
                </div>
            @endforelse
        </article>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h3>Order status</h3>
            <span>Count + amount</span>
        </div>
        <div class="order-summary">
            @foreach (['pending', 'processing', 'completed', 'cancelled'] as $status)
                <div>
                    <strong>{{ $orderSummary[$status]->total ?? 0 }}</strong>
                    <small>{{ ucfirst($status) }}</small>
                    <div class="money" style="margin-top:4px;font-size:12px;">
                        ${{ number_format((float) ($orderSummary[$status]->amount ?? 0), 2) }}
                    </div>
                </div>
            @endforeach
        </div>
        <p style="margin:12px 0 0;color:#64748b;font-size:12px;">
            Completed orders amount:
            <strong>${{ number_format((float) ($reportStats['completed_orders_revenue'] ?? 0), 2) }}</strong>
            · Month sales:
            <strong>${{ number_format((float) ($reportStats['month_sales'] ?? 0), 2) }}</strong>
            · Month orders:
            <strong>${{ number_format((float) ($reportStats['month_orders'] ?? 0), 2) }}</strong>
        </p>
    </section>

    <section class="panel">
        <div class="panel-head">
            <h3>Recent sales report</h3>
            <span>{{ $recentSales->count() }} latest</span>
        </div>
        @forelse ($recentSales as $sale)
            @if ($loop->first)
                <div class="table-wrap">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Invoice</th>
                                <th>Products</th>
                                <th>Customer</th>
                                <th>Units</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
            <tr>
                <td class="muted">{{ $sale->sold_at->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') }}</td>
                <td><strong>{{ $sale->invoiceNumber() }}</strong></td>
                <td><strong>{{ $sale->items->pluck('product.name')->filter()->join(', ') ?: 'Deleted products' }}</strong></td>
                <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                <td class="quantity">{{ $sale->items->sum('quantity') }}</td>
                <td class="money">${{ number_format((float) $sale->total, 2) }}</td>
            </tr>
            @if ($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <div class="empty product-empty">
                <div>
                    <span class="empty-icon">R</span>
                    <strong>No sales data</strong>
                    <p>Record sales to populate this report.</p>
                </div>
            </div>
        @endforelse
    </section>

    <section class="panel">
        <div class="panel-head">
            <h3>Recent orders report</h3>
            <span>{{ $recentOrders->count() }} latest</span>
        </div>
        @forelse ($recentOrders as $order)
            @if ($loop->first)
                <div class="table-wrap">
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Order</th>
                                <th>Products</th>
                                <th>Customer</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
            @endif
            <tr>
                <td class="muted">{{ optional($order->ordered_at)->timezone('Asia/Phnom_Penh')->format('F d, Y h:i A') ?? '—' }}</td>
                <td><strong>{{ $order->order_number }}</strong></td>
                <td>{{ $order->items->pluck('product.name')->filter()->join(', ') ?: 'Deleted products' }}</td>
                <td>{{ $order->customer?->name ?? 'Walk-in' }}</td>
                <td><span class="status-badge {{ $order->payment_status }}">{{ $order->payment_status }}</span></td>
                <td><span class="status-badge {{ $order->status }}">{{ $order->status }}</span></td>
                <td class="money">${{ number_format((float) $order->total, 2) }}</td>
            </tr>
            @if ($loop->last)
                        </tbody>
                    </table>
                </div>
            @endif
        @empty
            <div class="empty product-empty">
                <div>
                    <span class="empty-icon">O</span>
                    <strong>No orders data</strong>
                    <p>Create orders to populate this report.</p>
                </div>
            </div>
        @endforelse
    </section>

            @else
                <section class="module-page">
                    <div>
                        <span class="module-page-icon">{{ $activeModule['letter'] }}</span>
                        <h2>{{ $activeModule['label'] }}</h2>
                        <p>This protected Laravel module is ready. Connect this route to its controller, model, database table, and Blade content when you build the {{ strtolower($activeModule['label']) }} workflow.</p>
                        <span class="module-status">Authenticated module</span>
                    </div>
                </section>
            @endif
        </main>
    </div>

    <div class="image-modal" id="product-image-modal" role="dialog" aria-modal="true" aria-labelledby="product-image-modal-title" aria-hidden="true">
        <div class="image-modal-card">
            <button class="image-modal-close" id="product-image-modal-close" type="button" aria-label="Close image preview">×</button>
            <img class="image-modal-photo" id="product-image-modal-photo" src="" alt="">
            <div class="image-modal-title" id="product-image-modal-title"></div>
        </div>
    </div>

    <script>
        (() => {
            const liveTime = document.getElementById('live-cambodia-time');

            if (liveTime) {
                const timeFormatter = new Intl.DateTimeFormat('en-GB', {
                    timeZone: 'Asia/Phnom_Penh',
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true,
                });
                const updateLiveTime = () => {
                    liveTime.textContent = `${timeFormatter.format(new Date())} · Cambodia time`;
                };

                updateLiveTime();
                window.setInterval(updateLiveTime, 1000);
            }

            const money = value => `$${value.toFixed(2)}`;

            const setupInvoiceItems = ({ itemsId, templateId, addId, totalId }) => {
                const items = document.getElementById(itemsId);
                const template = document.getElementById(templateId);
                const addButton = document.getElementById(addId);
                const grandTotal = document.getElementById(totalId);

                if (!items || !template || !addButton || !grandTotal) {
                    return;
                }

                let nextIndex = items.querySelectorAll('.sale-item-row').length;

                const refreshRemoveButtons = () => {
                    const rows = items.querySelectorAll('.sale-item-row');
                    rows.forEach(row => {
                        row.querySelector('.remove-sale-item').disabled = rows.length === 1;
                    });
                };

                const updateRow = (row, resetPrice = false) => {
                    const product = row.querySelector('.sale-product');
                    const option = product.options[product.selectedIndex];
                    const type = row.querySelector('.sale-type').value;
                    const quantity = row.querySelector('.sale-quantity');
                    const packInput = row.querySelector('.units-per-pack');
                    const packField = row.querySelector('.pack-size-field');
                    const price = row.querySelector('.sale-price');
                    const discount = row.querySelector('.sale-discount');
                    const isWholesale = type === 'wholesale';

                    if (!isWholesale) {
                        packInput.value = 1;
                    }

                    packInput.readOnly = !isWholesale;
                    packField.classList.toggle('is-hidden', !isWholesale);
                    row.querySelector('.sale-quantity-label').textContent = isWholesale ? 'Pack quantity / ចំនួនដុំ' : 'Unit quantity / ចំនួនរាយ';
                    row.querySelector('.sale-price-label').textContent = isWholesale ? 'Price / pack ($)' : 'Price / unit ($)';

                    const unitsPerPack = isWholesale ? Math.max(1, Number(packInput.value) || 1) : 1;
                    const basePrice = option && option.value ? Number(option.dataset.price) || 0 : 0;
                    const stock = option && option.value ? Number(option.dataset.stock) || 0 : 0;

                    if (option && option.value) {
                        quantity.max = String(Math.floor(stock / unitsPerPack));

                        if (resetPrice || price.value === '') {
                            price.value = (basePrice * unitsPerPack).toFixed(2);
                        }
                    } else {
                        quantity.removeAttribute('max');

                        if (resetPrice) {
                            price.value = '';
                        }
                    }

                    const enteredQuantity = Number(quantity.value) || 0;
                    const enteredPrice = Number(price.value) || 0;
                    const discountRate = Math.min(100, Math.max(0, Number(discount.value) || 0));
                    const lineTotal = enteredQuantity * enteredPrice * (1 - discountRate / 100);

                    row.querySelector('.sale-line-total').textContent = money(lineTotal);

                    return lineTotal;
                };

                const updateTotals = () => {
                    let total = 0;
                    items.querySelectorAll('.sale-item-row').forEach(row => {
                        total += updateRow(row);
                    });
                    grandTotal.textContent = money(total);
                    refreshRemoveButtons();
                };

                items.addEventListener('change', event => {
                    if (event.target.matches('.sale-product, .sale-type')) {
                        updateRow(event.target.closest('.sale-item-row'), true);
                        updateTotals();
                    }
                });

                items.addEventListener('input', event => {
                    if (event.target.classList.contains('units-per-pack')) {
                        updateRow(event.target.closest('.sale-item-row'), true);
                        updateTotals();
                    } else if (event.target.matches('.sale-quantity, .sale-price, .sale-discount')) {
                        updateTotals();
                    }
                });

                items.addEventListener('click', event => {
                    const removeButton = event.target.closest('.remove-sale-item');

                    if (!removeButton) {
                        return;
                    }

                    removeButton.closest('.sale-item-row').remove();
                    updateTotals();
                });

                addButton.addEventListener('click', () => {
                    if (items.querySelectorAll('.sale-item-row').length >= 50) {
                        return;
                    }

                    items.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(nextIndex++)));
                    updateTotals();
                });

                updateTotals();
            };

            setupInvoiceItems({ itemsId: 'sale-items', templateId: 'sale-item-template', addId: 'add-sale-item', totalId: 'sale-grand-total' });
            setupInvoiceItems({ itemsId: 'order-items', templateId: 'order-item-template', addId: 'add-order-item', totalId: 'order-grand-total' });

            const imageModal = document.getElementById('product-image-modal');
            const imageModalPhoto = document.getElementById('product-image-modal-photo');
            const imageModalTitle = document.getElementById('product-image-modal-title');
            const imageModalClose = document.getElementById('product-image-modal-close');
            let previousImageTrigger = null;

            const closeImageModal = () => {
                if (!imageModal) {
                    return;
                }

                imageModal.classList.remove('is-open');
                imageModal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
                imageModalPhoto.src = '';
                previousImageTrigger?.focus();
            };

            document.addEventListener('click', event => {
                const imageTrigger = event.target.closest('[data-image-preview]');

                if (imageTrigger && imageModal) {
                    previousImageTrigger = imageTrigger;
                    imageModalPhoto.src = imageTrigger.dataset.imagePreview;
                    imageModalPhoto.alt = imageTrigger.dataset.imageTitle || 'Product image';
                    imageModalTitle.textContent = imageTrigger.dataset.imageTitle || 'Product image';
                    imageModal.classList.add('is-open');
                    imageModal.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';
                    imageModalClose.focus();
                    return;
                }

                if (event.target === imageModal || event.target === imageModalClose) {
                    closeImageModal();
                }
            });

            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && imageModal?.classList.contains('is-open')) {
                    closeImageModal();
                }
            });
        })();
    </script>
</body>
</html>
<!-- dashboard.index -->
