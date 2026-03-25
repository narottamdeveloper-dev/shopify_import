<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Shopify Import Admin</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --panel: rgba(255, 255, 255, 0.92);
            --panel-2: rgba(255, 255, 255, 0.98);
            --panel-border: rgba(15, 23, 42, 0.10);
            --text: #0f172a;
            --muted: #5b6472;
            --accent: #2563eb;
            --accent-2: #1d4ed8;
            --success: #15803d;
            --warning: #b45309;
            --danger: #b91c1c;
            --shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
            --radius: 24px;
        }

        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 24%),
                radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent 22%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
        }

        a { color: inherit; }
        .shell {
            width: min(1460px, calc(100% - 32px));
            margin: 16px auto 32px;
            display: grid;
            gap: 16px;
        }

        .topbar, .panel, .card {
            background: var(--panel);
            border: 1px solid var(--panel-border);
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
            border-radius: 999px;
            padding: 16px 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #ffffff;
            font-weight: 900;
            background: linear-gradient(135deg, var(--accent), #60a5fa);
        }

        .brand strong { display: block; font-size: 15px; }
        .brand span { display: block; color: var(--muted); font-size: 12px; }
        .top-actions { display: flex; gap: 10px; flex-wrap: wrap; }
        .top-link {
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 999px;
            border: 1px solid var(--panel-border);
            background: #fff;
            font-size: 13px;
            font-weight: 700;
            color: var(--text);
        }

        .layout {
            display: grid;
            grid-template-columns: 300px minmax(0, 1fr);
            gap: 18px;
        }

        .sidebar, .content {
            display: grid;
            gap: 18px;
        }

        .sidebar .panel, .content .panel, .card {
            border-radius: 28px;
            padding: 24px;
        }

        .hero {
            display: grid;
            gap: 18px;
            background: linear-gradient(180deg, #ffffff, #f8fbff);
        }

        h1, h2, h3, p { margin: 0; }

        .metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            padding: 0;
        }

        .metric {
            min-height: 104px;
            border-radius: 20px;
            padding: 18px 18px 16px;
            background: #fff;
            border: 1px solid var(--panel-border);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.035);
        }

        .metric span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .metric strong { font-size: 28px; line-height: 1; letter-spacing: -0.03em; }

        .sidebar-title, .section-title {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: baseline;
            margin-bottom: 12px;
        }

        .sidebar-title h2, .section-title h2 {
            font-size: 16px;
            letter-spacing: -0.02em;
        }

        .hint { color: var(--muted); font-size: 13px; }

        .notice {
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid var(--panel-border);
            font-size: 14px;
        }
        .notice-success { background: rgba(21, 128, 61, 0.08); color: var(--success); }
        .notice-warning { background: rgba(180, 83, 9, 0.08); color: var(--warning); }
        .notice-error { background: rgba(185, 28, 28, 0.08); color: var(--danger); }

        form { margin: 0; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }
        .field { display: grid; gap: 7px; }
        label {
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }
        input, select {
            width: 100%;
            padding: 13px 14px;
            border-radius: 14px;
            border: 1px solid var(--panel-border);
            background: #fff;
            color: var(--text);
            font: inherit;
            outline: none;
        }
        input::placeholder { color: #64748b; }

        .button {
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
        }
        .button-primary {
            color: #fff;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
        }
        .button-ghost {
            color: var(--text);
            background: #fff;
            border: 1px solid var(--panel-border);
        }
        .button-danger {
            color: var(--danger);
            background: rgba(185, 28, 28, 0.08);
        }

        .store-list, .feed {
            display: grid;
            gap: 10px;
        }

        .store-item, .feed-item, .log-item, .duplicate-item {
            border-radius: 18px;
            border: 1px solid var(--panel-border);
            background: #fff;
            padding: 14px 16px;
        }
        .store-item strong, .feed-item strong, .log-item strong, .duplicate-item strong {
            display: block;
            font-size: 14px;
        }
        .store-item span, .feed-item span, .log-item span, .duplicate-item span {
            display: block;
            color: var(--muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid transparent;
        }
        .pill-success { color: var(--success); background: rgba(21, 128, 61, 0.10); }
        .pill-warning { color: var(--warning); background: rgba(180, 83, 9, 0.10); }
        .pill-danger { color: var(--danger); background: rgba(185, 28, 28, 0.10); }
        .pill-neutral { color: var(--accent); background: rgba(37, 99, 235, 0.10); }

        .split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .stack {
            display: grid;
            gap: 16px;
        }

        .empty {
            padding: 18px;
            text-align: center;
            color: var(--muted);
            border-radius: 16px;
            border: 1px dashed var(--panel-border);
            background: #fff;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .store-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        @media (max-width: 1180px) {
            .layout, .split { grid-template-columns: 1fr; }
        }

        @media (max-width: 720px) {
            .shell { width: min(100% - 20px, 100%); }
            .topbar { flex-direction: column; align-items: flex-start; border-radius: 24px; }
            .metrics, .form-grid { grid-template-columns: 1fr; }
            .actions, .top-actions, .store-actions { width: 100%; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <header class="topbar">
            <div class="brand">
                <div class="brand-mark">S</div>
                <div>
                    <strong>Shopify Import Admin</strong>
                    <span>Store connections and cleanup</span>
                </div>
            </div>
            <div class="top-actions">
                <a class="top-link" href="/">Open Import Console</a>
                <a class="top-link" href="/dashboard-data" target="_blank">Live JSON</a>
            </div>
        </header>

        @if (session('success'))
            <div class="notice notice-success"><strong>Success</strong><div>{{ session('success') }}</div></div>
        @endif

        @if ($errors->any())
            <div class="notice notice-error"><strong>Action failed</strong><div>{{ $errors->first() }}</div></div>
        @endif

        <section class="panel hero">
            <div class="metrics">
                <div class="metric"><span>Connected stores</span><strong>{{ number_format($stores->count()) }}</strong></div>
                <div class="metric"><span>Total uploads</span><strong>{{ number_format(count($uploads)) }}</strong></div>
                <div class="metric"><span>Duplicate groups</span><strong>{{ number_format(count($duplicateGroups)) }}</strong></div>
                <div class="metric"><span>Cleanup logs</span><strong>{{ number_format($cleanupLogs->count()) }}</strong></div>
            </div>
        </section>

        <section class="layout">
            <aside class="sidebar">
                <section class="panel">
                    <div class="sidebar-title">
                        <div>
                            <h2>Connect store</h2>
                        </div>
                    </div>

                    <form method="POST" action="/admin/stores">
                        @csrf
                        <div class="form-grid">
                            <div class="field">
                                <label for="name">Store label</label>
                                <input id="name" name="name" type="text" placeholder="Primary demo store" required>
                            </div>
                            <div class="field">
                                <label for="store_url">Store URL</label>
                                <input id="store_url" name="store_url" type="text" placeholder="examplestore.myshopify.com" required>
                            </div>
                            <div class="field">
                                <label for="access_token">Access token</label>
                                <input id="access_token" name="access_token" type="password" placeholder="shpat_..." required>
                            </div>
                            <div class="field">
                                <label for="api_version">API version</label>
                                <input id="api_version" name="api_version" type="text" value="2024-01" required>
                            </div>
                            <div class="field">
                                <label for="collection_id">Collection ID</label>
                                <input id="collection_id" name="collection_id" type="text" placeholder="464337174767">
                            </div>
                            <div class="field">
                                <label for="is_default">Default store</label>
                                <select id="is_default" name="is_default">
                                    <option value="1">Yes</option>
                                    <option value="0" selected>No</option>
                                </select>
                            </div>
                        </div>
                        <div class="actions" style="margin-top:14px;">
                            <button class="button button-primary" type="submit">Save Store</button>
                        </div>
                    </form>
                </section>

                <section class="panel">
                    <div class="sidebar-title">
                        <div>
                            <h2>Store list</h2>
                        </div>
                    </div>

                    <div class="store-list">
                        @forelse ($stores as $store)
                            <div class="store-item">
                                <strong>{{ $store->name }}</strong>
                                <span>{{ $store->store_url }}</span>
                                <span>API {{ $store->api_version }} · Collection {{ $store->collection_id ?: 'n/a' }}</span>
                                <div class="store-actions">
                                    @if ($store->is_default)
                                        <span class="pill pill-neutral">Default</span>
                                    @else
                                        <form method="POST" action="/admin/stores/{{ $store->id }}/default">
                                            @csrf
                                            <button class="button button-ghost" type="submit">Set Default</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="/admin/stores/{{ $store->id }}/test">
                                        @csrf
                                        <button class="button button-ghost" type="submit">Test</button>
                                    </form>
                                </div>
                                <div class="store-actions">
                                    <form method="POST" action="/admin/stores/{{ $store->id }}/cleanup-duplicates">
                                        @csrf
                                        <button class="button button-ghost" type="submit">Cleanup Duplicates</button>
                                    </form>
                                    <form method="POST" action="/admin/stores/{{ $store->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="button button-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="empty">No store connections yet.</div>
                        @endforelse
                    </div>
                </section>
            </aside>

            <section class="content">
                <section class="card">
                    <div class="section-title">
                        <div>
                            <h2>Recent imports</h2>
                        </div>
                    </div>

                    <div class="feed">
                        @forelse ($uploads as $upload)
                            <div class="feed-item">
                                <div class="actions" style="justify-content:space-between;">
                                    <div>
                                        <strong>{{ $upload->file_name }}</strong>
                                        <span>#{{ $upload->id }} · {{ $upload->shopifyStore?->name ?? 'No store selected' }}</span>
                                        <span>{{ ucfirst($upload->status) }} · {{ $upload->created_at?->format('d M Y, h:i A') }}</span>
                                    </div>
                                    @php
                                        $status = strtolower((string) $upload->status);
                                        $pillClass = match ($status) {
                                            'completed' => 'pill-success',
                                            'failed' => 'pill-danger',
                                            'processing', 'pending' => 'pill-warning',
                                            default => 'pill-neutral',
                                        };
                                    @endphp
                                    <span class="pill {{ $pillClass }}">{{ ucfirst($upload->status) }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="empty">No uploads yet.</div>
                        @endforelse
                    </div>
                </section>

                <section class="split">
                    <section class="card">
                    <div class="section-title">
                        <div>
                            <h2>Duplicate groups</h2>
                        </div>
                    </div>

                        <div class="feed">
                            @forelse ($duplicateGroups as $group)
                                <div class="duplicate-item">
                                    <strong>{{ $group['store_name'] }} · {{ $group['title'] }}</strong>
                                    <span>{{ $group['source_key'] ?: 'No source key' }}</span>
                                    <span>{{ number_format($group['duplicate_count']) }} records found · Price {{ $group['price'] }}</span>
                                </div>
                            @empty
                                <div class="empty">No duplicate groups found.</div>
                            @endforelse
                        </div>
                    </section>

                    <section class="card">
                    <div class="section-title">
                        <div>
                            <h2>Cleanup history</h2>
                        </div>
                    </div>

                        <div class="feed">
                            @forelse ($cleanupLogs as $log)
                                <div class="log-item">
                                    <strong>{{ $log->message }}</strong>
                                    <span>{{ $log->created_at?->format('d M Y, h:i A') }}</span>
                                    <span>@json($log->context)</span>
                                </div>
                            @empty
                                <div class="empty">No cleanup activity yet.</div>
                            @endforelse
                        </div>
                    </section>
                </section>
            </section>
        </section>
    </main>
</body>
</html>
