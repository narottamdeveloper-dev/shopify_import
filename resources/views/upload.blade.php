<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>VRHUNEC Import Console</title>
    <style>
        :root {
            --bg: #dfe6ee;
            --surface: rgba(255, 255, 255, 0.94);
            --surface-strong: #ffffff;
            --border: rgba(15, 23, 42, 0.10);
            --text: #0f172a;
            --muted: #5b6472;
            --muted-strong: #334155;
            --accent: #143a57;
            --accent-2: #245f86;
            --success: #1f7a50;
            --warning: #9a6700;
            --danger: #b42318;
            --shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            --radius: 20px;
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
        }

        body {
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(20, 58, 87, 0.14), transparent 26%),
                radial-gradient(circle at top right, rgba(36, 95, 134, 0.10), transparent 24%),
                linear-gradient(180deg, #eef3f8 0%, var(--bg) 100%);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            line-height: 1.5;
        }

        .page {
            width: min(1240px, calc(100% - 32px));
            margin: 20px auto 32px;
            display: grid;
            gap: 18px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: linear-gradient(135deg, #0f172a, #12263f);
            color: #e2e8f0;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .brand-logo {
            display: block;
            width: auto;
            height: 28px;
        }

        .brand-text strong {
            display: none;
        }

        .brand-text span {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: #f8fafc;
            line-height: 1;
            margin-top: 1px;
        }

        .sync {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #e2e8f0;
            font-size: 13px;
            white-space: nowrap;
        }

        .sync-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: var(--success);
            box-shadow: 0 0 0 5px rgba(31, 122, 80, 0.12);
        }

        .pill-link {
            text-decoration: none;
            color: var(--accent);
            background: rgba(24, 78, 119, 0.08);
            padding: 9px 12px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .hero {
            padding: 28px;
            border: 1px solid var(--border);
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(245, 248, 252, 0.94));
            box-shadow: var(--shadow);
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
            gap: 20px;
            align-items: start;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--accent);
            background: rgba(24, 78, 119, 0.08);
        }

        h1 {
            margin: 14px 0 8px;
            font-size: clamp(30px, 4vw, 48px);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .hero-copy {
            max-width: 680px;
            margin: 0;
            color: var(--muted);
            font-size: 15px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .stat {
            padding: 18px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            background: var(--surface-strong);
        }

        .stat span {
            display: block;
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .stat strong {
            font-size: 28px;
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .content {
            display: block;
        }

        .card {
            padding: 22px;
            border-radius: 26px;
            border: 1px solid var(--border);
            background: var(--surface);
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .card h2,
        .card h3 {
            margin: 0;
            letter-spacing: -0.02em;
        }

        .card p {
            margin: 10px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .tab-shell {
            display: grid;
            gap: 18px;
        }

        .tab-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .tab-header-copy {
            min-width: 0;
        }

        .tab-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .tab-list {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(16, 24, 40, 0.03), rgba(16, 24, 40, 0.05));
            border: 1px solid rgba(15, 23, 42, 0.10);
            width: 100%;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
        }

        .tab-button {
            flex: 1 1 0;
            appearance: none;
            border: 0;
            cursor: pointer;
            border-radius: 999px;
            padding: 11px 16px;
            background: transparent;
            color: #475569;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.01em;
            transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
        }

        .tab-button[aria-selected="true"] {
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            box-shadow: 0 12px 20px rgba(24, 78, 119, 0.20);
            transform: translateY(-1px);
        }

        .tab-panels {
            display: grid;
        }

        .tab-panel {
            display: none;
        }

        .tab-panel.is-active {
            display: block;
        }

        .panel-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 18px;
            align-items: start;
            margin-top: 18px;
        }

        .panel-grid.activity-grid {
            grid-template-columns: minmax(0, 1fr);
        }

        .upload-stack {
            display: grid;
            gap: 18px;
            margin-top: 18px;
        }

        .upload-primary {
            display: grid;
        }

        .panel-stack {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .panel-section {
            padding: 0;
            border: 0;
            background: transparent;
            box-shadow: none;
        }

        .panel-section .sidebar-head p {
            margin-top: 6px;
        }

        .log-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .log-item {
            display: grid;
            gap: 10px;
            padding: 16px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .log-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .log-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }

        .log-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .log-message {
            color: var(--muted-strong);
            font-size: 13px;
        }

        .log-context {
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fafc;
            border: 1px solid rgba(15, 23, 42, 0.06);
            color: #475569;
            font-size: 12px;
            line-height: 1.55;
            overflow-wrap: anywhere;
        }

        .severity {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 72px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .severity-info {
            color: var(--accent);
            background: rgba(24, 78, 119, 0.10);
        }

        .severity-warning {
            color: var(--warning);
            background: rgba(154, 103, 0, 0.12);
        }

        .severity-error {
            color: var(--danger);
            background: rgba(180, 35, 24, 0.12);
        }

        .notice {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--border);
            font-size: 14px;
        }

        .notice strong {
            display: block;
            margin-bottom: 4px;
        }

        .notice-success {
            color: var(--success);
            background: rgba(31, 122, 80, 0.08);
            border-color: rgba(31, 122, 80, 0.16);
        }

        .notice-warning {
            color: var(--warning);
            background: rgba(154, 103, 0, 0.08);
            border-color: rgba(154, 103, 0, 0.16);
        }

        .notice-error {
            color: var(--danger);
            background: rgba(180, 35, 24, 0.08);
            border-color: rgba(180, 35, 24, 0.16);
        }

        .notice-info {
            color: var(--accent);
            background: rgba(24, 78, 119, 0.08);
            border-color: rgba(24, 78, 119, 0.14);
        }

        .upload-panel {
            margin-top: 16px;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(24, 78, 119, 0.12);
            background: linear-gradient(180deg, #fff, #f9fbfe);
        }

        .dropzone {
            display: grid;
            gap: 14px;
            padding: 22px;
            border-radius: 20px;
            border: 1.5px dashed rgba(20, 58, 87, 0.24);
            background:
                linear-gradient(180deg, rgba(20, 58, 87, 0.03), rgba(36, 95, 134, 0.02)),
                #fff;
            text-align: center;
            cursor: pointer;
            transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease, box-shadow 0.18s ease;
        }

        .dropzone:hover,
        .dropzone.is-dragover {
            border-color: rgba(20, 58, 87, 0.55);
            background:
                linear-gradient(180deg, rgba(20, 58, 87, 0.06), rgba(36, 95, 134, 0.04)),
                #fff;
            box-shadow: 0 12px 26px rgba(20, 58, 87, 0.10);
            transform: translateY(-1px);
        }

        .dropzone-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            margin: 0 auto;
            padding: 7px 12px;
            border-radius: 999px;
            color: var(--accent);
            background: rgba(24, 78, 119, 0.08);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .dropzone-title {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .dropzone-copy {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .dropzone-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .dropzone-file {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .dropzone-filename {
            display: block;
            font-size: 13px;
            color: var(--muted-strong);
            text-align: center;
        }

        .field-label {
            display: block;
            margin-bottom: 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--muted-strong);
        }

        .file-input {
            width: 100%;
            padding: 15px;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: #fff;
            font-size: 14px;
            color: var(--muted-strong);
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 14px;
            flex-wrap: wrap;
            width: 100%;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1 1 100%;
            width: 100%;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-group > .button {
            flex: 0 1 280px;
            min-width: 280px;
        }

        .row-actions {
            display: flex;
            gap: 10px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .helper {
            color: var(--muted);
            font-size: 13px;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: 13px 18px;
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(24, 78, 119, 0.18);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .button:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(24, 78, 119, 0.22);
        }

        .button-muted {
            background: #e8eef5;
            color: #17324f;
            box-shadow: none;
        }

        .button-muted:disabled {
            opacity: 0.55;
            cursor: not-allowed;
            transform: none;
        }

        .subgrid {
            margin-top: 16px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .mini-card {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .mini-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
        }

        .mini-card span {
            color: var(--muted);
            font-size: 13px;
        }

        .sidebar-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
        }

        .list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .stack-section {
            display: grid;
            gap: 16px;
        }

        .row {
            padding: 16px;
            border-radius: 18px;
            border: 1px solid var(--border);
            background: #fff;
        }

        .row-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .row-title {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .row-meta {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12px;
        }

        .row-submeta {
            display: block;
            margin-top: 8px;
            color: var(--muted);
            font-size: 12px;
        }

        .progress {
            height: 8px;
            margin-top: 12px;
            border-radius: 999px;
            background: #edf2f7;
            overflow: hidden;
        }

        .progress > div {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
        }

        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 92px;
            padding: 8px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .status-pending,
        .status-processing {
            color: var(--warning);
            background: rgba(154, 103, 0, 0.10);
        }

        .status-completed {
            color: var(--success);
            background: rgba(31, 122, 80, 0.10);
        }

        .status-failed {
            color: var(--danger);
            background: rgba(180, 35, 24, 0.10);
        }

        .status-skipped {
            color: var(--accent);
            background: rgba(24, 78, 119, 0.10);
        }

        .error-item {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid rgba(180, 35, 24, 0.14);
            background: rgba(180, 35, 24, 0.04);
        }

        .error-title {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
        }

        .error-meta {
            display: block;
            margin-top: 4px;
            font-size: 12px;
            color: var(--muted);
        }

        .error-message {
            display: block;
            margin-top: 8px;
            font-size: 13px;
            color: var(--danger);
        }

        .empty {
            padding: 22px;
            text-align: center;
            color: var(--muted);
            border-radius: 18px;
            border: 1px dashed var(--border);
            background: #fff;
        }

        .toast-stack {
            position: fixed;
            right: 20px;
            bottom: 20px;
            z-index: 30;
            display: grid;
            gap: 10px;
            width: min(360px, calc(100% - 32px));
        }

        .toast {
            padding: 14px 16px;
            border-radius: 16px;
            color: #fff;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.22);
        }

        .toast strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .toast span {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.84);
        }

        .toast-success {
            background: linear-gradient(135deg, #1f7a50, #145338);
        }

        .toast-error {
            background: linear-gradient(135deg, #b42318, #7f1d1d);
        }

        .toast-info {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
        }

        @media (max-width: 980px) {
            .hero-grid,
            .panel-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .page {
                width: min(100% - 20px, 100%);
                margin-top: 12px;
            }

            .topbar,
            .hero,
            .card {
                border-radius: 20px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats,
            .subgrid {
                grid-template-columns: 1fr;
            }

            .panel-stack {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .action-group {
                justify-content: stretch;
                width: 100%;
            }

            .button {
                width: 100%;
            }

            .row-top {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="topbar">
            <div class="brand">
                <img class="brand-logo" src="/logo.svg" alt="VRHUNEC">
                <div class="brand-text">
                    <span>Import console</span>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="sync">
                    <span class="sync-dot"></span>
                    <span id="syncLabel">Syncing live data</span>
                </div>
            </div>
        </header>

        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">VRHUNEC</div>
                    <h1>Products bulk import with background processing and control duplicate products.</h1>
                    <p class="hero-copy">
                        Upload Shopify-ready CSV files, process them in the queue, skip duplicates safely, and keep the entire import lifecycle visible in one place.
                    </p>
                </div>

                <div class="stats">
                    <div class="stat">
                        <span>Total uploads</span>
                        <strong data-stat="uploads">0</strong>
                    </div>
                    <div class="stat">
                        <span>Products imported</span>
                        <strong data-stat="products">0</strong>
                    </div>
                    <div class="stat">
                        <span>Completed jobs</span>
                        <strong data-stat="completed">0</strong>
                    </div>
                    <div class="stat">
                        <span>Skipped duplicates</span>
                        <strong data-stat="skipped">0</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="card tab-shell">
                <div class="tab-header">
                    <div class="tab-actions">
                        <a class="button button-muted" href="/collection-products/export">Download Collection CSV</a>
                    </div>
                </div>

                <div class="tab-list" role="tablist" aria-label="Import workspace sections">
                    <button class="tab-button" type="button" role="tab" aria-selected="true" aria-controls="uploadPanel" id="uploadTab" data-tab-target="uploadPanel">Upload CSV</button>
                    <button class="tab-button" type="button" role="tab" aria-selected="false" aria-controls="activityPanel" id="activityTab" data-tab-target="activityPanel">Import Activity</button>
                    <button class="tab-button" type="button" role="tab" aria-selected="false" aria-controls="logsPanel" id="logsTab" data-tab-target="logsPanel">Logs</button>
                </div>

                <div class="tab-panels">
                    <section class="tab-panel is-active" id="uploadPanel" role="tabpanel" aria-labelledby="uploadTab">
                        <div class="upload-stack">
                            <div class="upload-primary">
                                <h2>Upload CSV</h2>
                                <p>Use a Shopify export or a comparable CSV with product title, description, pricing, handle, and SKU.</p>

                                @if (session('success'))
                                    <div class="notice notice-success">
                                        <strong>Upload accepted</strong>
                                        <div>{{ session('success') }}</div>
                                    </div>
                                @endif

                                @if ($errors->any())
                                    <div class="notice notice-error">
                                        <strong>Upload error</strong>
                                        <div>{{ $errors->first('file') ?? 'The upload could not be processed.' }}</div>
                                    </div>
                                @endif

                                <form method="POST" action="/upload" enctype="multipart/form-data">
                                    @csrf
                                    <div class="upload-panel">
                                        <label class="dropzone" for="file" id="dropzone">
                                            <strong class="dropzone-title">Drag and drop your CSV</strong>
                                            <p class="dropzone-copy">Choose a Shopify-ready CSV or drop a file here to start the import.</p>
                                            <div class="dropzone-actions">
                                                <span class="button button-muted">Choose File</span>
                                                <span class="dropzone-filename" id="selectedFileName">No file chosen</span>
                                            </div>
                                        </label>
                                        <input class="dropzone-file" id="file" type="file" name="file" accept=".csv,.txt" required>

                                        <div class="actions">
                                            <div class="helper">Accepted file types: `.csv`, `.txt` up to 50 MB.</div>
                                            <div class="action-group">
                                                <button class="button" type="submit">Start Import</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="panel-stack">
                                <div class="mini-card">
                                    <strong>Queue safe</strong>
                                    <span>Jobs run in the background so large imports do not block the request cycle.</span>
                                </div>
                                <div class="mini-card">
                                    <strong>Chunked processing</strong>
                                    <span>Rows are dispatched in smaller jobs to keep imports responsive at scale.</span>
                                </div>
                                <div class="mini-card">
                                    <strong>Duplicate control</strong>
                                    <span>Records already present are skipped and surfaced clearly in the UI.</span>
                                </div>
                                <div class="mini-card">
                                    <strong>Live visibility</strong>
                                    <span>Progress, status, and completion notices update automatically as the job runs.</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="tab-panel" id="activityPanel" role="tabpanel" aria-labelledby="activityTab">
                        <div class="panel-grid activity-grid">
                            <div class="card panel-section">
                                <div class="sidebar-head">
                                    <div>
                                        <h3>Recent Imports</h3>
                                        <p>Latest jobs and their live status.</p>
                                    </div>
                                </div>

                                <div class="list" id="recentUploads">
                                    <div class="empty">Loading current imports...</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="tab-panel" id="logsPanel" role="tabpanel" aria-labelledby="logsTab">
                        <div class="card panel-section">
                            <div class="sidebar-head">
                                <div>
                                    <h3>Import Logs</h3>
                                    <p>Execution events, validation messages, and collection actions.</p>
                                </div>
                            </div>

                            <div class="log-list" id="importLogs">
                                <div class="empty">Loading logs...</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </section>
    </main>

    <div class="toast-stack" id="toastStack"></div>

    <script>
        const trackedUploadId = @json(session('tracked_upload_id'));
        const stats = {
            uploads: document.querySelector('[data-stat="uploads"]'),
            products: document.querySelector('[data-stat="products"]'),
            completed: document.querySelector('[data-stat="completed"]'),
            skipped: document.querySelector('[data-stat="skipped"]'),
        };

        const syncLabel = document.getElementById('syncLabel');
        const recentUploads = document.getElementById('recentUploads');
        const importLogs = document.getElementById('importLogs');
        const toastStack = document.getElementById('toastStack');
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file');
        const selectedFileName = document.getElementById('selectedFileName');
        const tabButtons = Array.from(document.querySelectorAll('[data-tab-target]'));
        const tabPanels = Array.from(document.querySelectorAll('.tab-panel'));
        const seenNotifications = new Set(JSON.parse(localStorage.getItem('seen_upload_notifications') || '[]'));

        function formatNumber(value) {
            return new Intl.NumberFormat().format(value || 0);
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatContextValue(value) {
            if (value === null || value === undefined || value === '') {
                return '—';
            }

            if (typeof value === 'object') {
                return JSON.stringify(value);
            }

            return String(value);
        }

        function setActiveTab(panelId) {
            tabButtons.forEach((button) => {
                const active = button.dataset.tabTarget === panelId;
                button.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            tabPanels.forEach((panel) => {
                panel.classList.toggle('is-active', panel.id === panelId);
            });
        }

        function updateSelectedFileName() {
            selectedFileName.textContent = fileInput.files?.length ? fileInput.files[0].name : 'No file chosen';
        }

        function renderUploads(items) {
            if (!items.length) {
                recentUploads.innerHTML = '<div class="empty">No imports yet.</div>';
                return;
            }

            recentUploads.innerHTML = items.map((upload) => `
                ${(() => {
                    const hasImportedProducts = (upload.successful_rows || 0) > 0;
                    const cleanupButton = upload.status === 'completed'
                        ? (
                            hasImportedProducts
                                ? `
                                    <div class="row-actions">
                                        <form method="POST" action="/upload/${upload.id}/remove-from-collection">
                                            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                            <button class="button button-muted" type="submit">Remove from Collection</button>
                                        </form>
                                    </div>
                                `
                                : `
                                    <div class="row-actions">
                                        <button class="button button-muted" type="button" disabled>No imported products to remove</button>
                                    </div>
                                `
                        )
                        : '';

                    return `
                <div class="row">
                    <div class="row-top">
                        <div>
                            <p class="row-title">${escapeHtml(upload.file_name)}</p>
                            <div class="row-meta">#${upload.id} · ${escapeHtml(upload.created_at || '')}</div>
                        </div>
                        <div class="status status-${escapeHtml(upload.status)}">${escapeHtml(upload.status_label || upload.status)}</div>
                    </div>
                    <div class="row-submeta">
                        ${upload.processed_rows || 0}/${upload.total_rows || 0} processed · ${upload.successful_rows || 0} imported · ${upload.skipped_rows || 0} skipped · ${upload.failed_rows || 0} failed
                    </div>
                    <div class="progress">
                        <div style="width:${upload.progress || 0}%"></div>
                    </div>
                    ${cleanupButton}
                </div>
            `;})()}
            `).join('');
        }

        function renderLogs(items) {
            if (!items.length) {
                importLogs.innerHTML = '<div class="empty">No logs yet.</div>';
                return;
            }

            importLogs.innerHTML = items.map((log) => {
                const contextEntries = Object.entries(log.context || {});
                const contextText = contextEntries.length
                    ? contextEntries.map(([key, value]) => `${escapeHtml(key)}: ${escapeHtml(formatContextValue(value))}`).join(' · ')
                    : 'No extra context';

                return `
                    <article class="log-item">
                        <div class="log-head">
                            <div>
                                <p class="log-title">${escapeHtml(log.message)}</p>
                                <div class="log-meta">
                                    <span>#${log.id}</span>
                                    <span>${escapeHtml(log.created_at || '')}</span>
                                    ${log.upload_id ? `<span>Upload #${log.upload_id}${log.upload_name ? ` · ${escapeHtml(log.upload_name)}` : ''}</span>` : ''}
                                </div>
                            </div>
                            <span class="severity severity-${escapeHtml(log.severity || 'info')}">${escapeHtml(log.severity || 'info')}</span>
                        </div>
                        <div class="log-message">
                            ${log.upload_status ? `Upload status: ${escapeHtml(log.upload_status)}.` : 'System log entry.'}
                        </div>
                        <div class="log-context">${contextText}</div>
                    </article>
                `;
            }).join('');
        }

        function persistSeenNotifications() {
            localStorage.setItem('seen_upload_notifications', JSON.stringify([...seenNotifications]));
        }

        fileInput.addEventListener('change', updateSelectedFileName);

        if (dropzone) {
            dropzone.addEventListener('dragover', (event) => {
                event.preventDefault();
                dropzone.classList.add('is-dragover');
            });

            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('is-dragover');
            });

            dropzone.addEventListener('drop', (event) => {
                event.preventDefault();
                dropzone.classList.remove('is-dragover');

                if (event.dataTransfer?.files?.length) {
                    fileInput.files = event.dataTransfer.files;
                    updateSelectedFileName();
                }
            });
        }

        tabButtons.forEach((button) => {
            button.addEventListener('click', () => setActiveTab(button.dataset.tabTarget));
        });

        function pushToast(type, title, message) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<strong>${escapeHtml(title)}</strong><span>${escapeHtml(message)}</span>`;
            toastStack.prepend(toast);

            window.setTimeout(() => {
                toast.remove();
            }, 5000);
        }

        function notifyUpload(upload) {
            const key = `${upload.id}:${upload.status}`;

            if (seenNotifications.has(key)) {
                return;
            }

            seenNotifications.add(key);
            persistSeenNotifications();

            if (upload.status === 'completed') {
                const duplicateText = upload.skipped_rows > 0
                    ? ` ${upload.skipped_rows} duplicate${upload.skipped_rows > 1 ? 's were' : ' was'} skipped.`
                    : '';

                pushToast('success', 'Import completed', `${upload.file_name} finished successfully.${duplicateText}`);
            }

            if (upload.status === 'failed') {
                pushToast('error', 'Import failed', `${upload.file_name} finished with errors.`);
            }

            if ('Notification' in window && Notification.permission === 'granted') {
                const title = upload.status === 'completed' ? 'Import completed' : 'Import failed';
                const body = upload.status === 'completed'
                    ? `${upload.file_name} has finished processing.${upload.skipped_rows > 0 ? ` ${upload.skipped_rows} duplicates were skipped.` : ''}`
                    : `${upload.file_name} could not be imported successfully.`;

                new Notification(title, { body });
            }
        }

        function trackUploadCompletion(items) {
            if (!trackedUploadId) {
                return;
            }

            const tracked = items.find((upload) => upload.id === trackedUploadId);

            if (!tracked) {
                return;
            }

            if (tracked.status === 'completed' || tracked.status === 'failed') {
                notifyUpload(tracked);
            }
        }

        async function loadDashboard() {
            try {
                const response = await fetch(`/dashboard-data?t=${Date.now()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    throw new Error('Unable to load dashboard data.');
                }

                const payload = await response.json();

                Object.entries(stats).forEach(([key, element]) => {
                    element.textContent = formatNumber(payload.stats?.[key]);
                });

                const items = payload.recent_uploads || [];

                renderUploads(items);
                renderLogs(payload.logs || []);
                trackUploadCompletion(items);
                syncLabel.textContent = `Last synced ${payload.generated_at}`;
            } catch (error) {
                recentUploads.innerHTML = '<div class="empty">Could not load current imports.</div>';
            }
        }

        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().catch(() => {});
        }

        loadDashboard();
        setInterval(loadDashboard, 5000);
    </script>
</body>
</html>
