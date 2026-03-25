<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
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
            display: grid;
            grid-template-columns: minmax(0, 0.94fr) minmax(360px, 1.06fr);
            gap: 18px;
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
        }

        .helper {
            color: var(--muted);
            font-size: 13px;
        }

        .button {
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
            .content {
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

            .actions {
                flex-direction: column;
                align-items: stretch;
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
            <div class="card">
                <h2>Upload CSV</h2>
                <p>Use a Shopify export or a comparable CSV with product title, description, pricing, handle, and SKU.</p>

                @if (session('success'))
                    <div class="notice notice-success">
                        <strong>Upload accepted</strong>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                <div class="notice notice-info" id="queueNotice">
                    Imports are queued in the background. Keep the worker running and this page will update automatically.
                </div>

                <div id="latestNoticeWrap"></div>

                @if ($errors->any())
                    <div class="notice notice-error">
                        <strong>Upload error</strong>
                        <div>{{ $errors->first('file') ?? 'The upload could not be processed.' }}</div>
                    </div>
                @endif

                <form method="POST" action="/upload" enctype="multipart/form-data">
                    @csrf
                    <div class="upload-panel">
                        <label class="field-label" for="file" style="margin-top:14px;">Select CSV file</label>
                        <input class="file-input" id="file" type="file" name="file" accept=".csv,.txt" required>

                        <div class="actions">
                            <div class="helper">Accepted file types: `.csv`, `.txt` up to 50 MB.</div>
                            <button class="button" type="submit">Start Import</button>
                        </div>
                    </div>
                </form>

                <div class="subgrid">
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

            <aside class="card">
                <div class="stack-section">
                    <section>
                        <div class="sidebar-head">
                            <div>
                                <h3>Recent Imports</h3>
                                <p>Latest jobs and their live status.</p>
                            </div>
                        </div>

                        <div class="list" id="recentUploads">
                            <div class="empty">Loading current imports...</div>
                        </div>
                    </section>

                    <section>
                        <div class="sidebar-head">
                            <div>
                                <h3>Failed Rows</h3>
                                <p>Latest validation issues from the importer.</p>
                            </div>
                        </div>

                        <div class="list" id="failedRows">
                            <div class="empty">Loading failed rows...</div>
                        </div>
                    </section>
                </div>
            </aside>
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
        const failedRows = document.getElementById('failedRows');
        const queueNotice = document.getElementById('queueNotice');
        const latestNoticeWrap = document.getElementById('latestNoticeWrap');
        const toastStack = document.getElementById('toastStack');
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

        function renderUploads(items) {
            if (!items.length) {
                recentUploads.innerHTML = '<div class="empty">No imports yet.</div>';
                return;
            }

            recentUploads.innerHTML = items.map((upload) => `
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
                </div>
            `).join('');
        }

        function renderLatestNotice(notice) {
            if (!notice) {
                latestNoticeWrap.innerHTML = '';
                return;
            }

            const className = notice.type === 'warning' ? 'notice notice-warning' : 'notice notice-success';
            latestNoticeWrap.innerHTML = `
                <div class="${className}">
                    <strong>${escapeHtml(notice.title)}</strong>
                    <div>${escapeHtml(notice.message)}</div>
                </div>
            `;
        }

        function renderFailedRows(items) {
            if (!items.length) {
                failedRows.innerHTML = '<div class="empty">No failed rows.</div>';
                return;
            }

            failedRows.innerHTML = items.map((item) => `
                <div class="error-item">
                    <p class="error-title">${escapeHtml(item.title)}</p>
                    <span class="error-meta">Upload #${item.upload_id} · ${escapeHtml(item.created_at || '')}</span>
                    <span class="error-message">${escapeHtml(item.error_message)}</span>
                </div>
            `).join('');
        }

        function renderQueueNotice(items) {
            const processing = items.filter((upload) => upload.status === 'processing').length;
            const pending = items.filter((upload) => upload.status === 'pending').length;

            if (processing > 0) {
                queueNotice.className = 'notice notice-info';
                queueNotice.textContent = `${processing} import${processing > 1 ? 's are' : ' is'} processing in the background.`;
                return;
            }

            if (pending > 0) {
                queueNotice.className = 'notice notice-info';
                queueNotice.textContent = `${pending} import${pending > 1 ? 's are' : ' is'} waiting in the queue.`;
                return;
            }

            queueNotice.className = 'notice notice-info';
            queueNotice.textContent = 'Background import queue is idle. New uploads will be picked up automatically.';
        }

        function persistSeenNotifications() {
            localStorage.setItem('seen_upload_notifications', JSON.stringify([...seenNotifications]));
        }

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
                renderFailedRows(payload.failed_rows || []);
                renderQueueNotice(items);
                renderLatestNotice(payload.latest_notice);
                trackUploadCompletion(items);
                syncLabel.textContent = `Last synced ${payload.generated_at}`;
            } catch (error) {
                recentUploads.innerHTML = '<div class="empty">Could not load current imports.</div>';
                queueNotice.className = 'notice notice-error';
                queueNotice.textContent = 'Live dashboard data is temporarily unavailable.';
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
