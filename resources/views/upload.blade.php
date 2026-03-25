<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Shopify CSV Importer</title>
    <style>
        :root {
            --bg: #f5efe3;
            --panel: rgba(255, 252, 246, 0.92);
            --panel-strong: #fffaf0;
            --line: rgba(87, 64, 39, 0.14);
            --text: #23180f;
            --muted: #6b5a49;
            --accent: #b55d38;
            --accent-dark: #8d4323;
            --success: #1f7a50;
            --warning: #a66a16;
            --danger: #b03d2e;
            --shadow: 0 24px 60px rgba(58, 35, 18, 0.12);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(232, 169, 104, 0.35), transparent 30%),
                radial-gradient(circle at right, rgba(132, 179, 150, 0.22), transparent 28%),
                linear-gradient(135deg, #f9f3e8 0%, #f2e5cd 52%, #eadfc9 100%);
        }

        .shell {
            width: min(1140px, calc(100% - 32px));
            margin: 32px auto;
            display: grid;
            gap: 24px;
        }

        .hero {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, rgba(45, 31, 20, 0.95), rgba(93, 50, 24, 0.92));
            color: #fff7ef;
            border-radius: 28px;
            padding: 32px;
            box-shadow: var(--shadow);
        }

        .hero::after {
            content: "";
            position: absolute;
            inset: auto -10% -35% auto;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            background: rgba(255, 206, 160, 0.15);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 18px 0 10px;
            max-width: 620px;
            font-size: clamp(32px, 5vw, 56px);
            line-height: 0.95;
        }

        .hero p {
            max-width: 620px;
            margin: 0;
            color: rgba(255, 247, 239, 0.8);
            font-size: 16px;
            line-height: 1.6;
        }

        .hero-grid,
        .stats,
        .uploads {
            display: grid;
            gap: 18px;
        }

        .hero-grid {
            grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
            align-items: end;
            gap: 24px;
        }

        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .stat {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            padding: 18px;
            backdrop-filter: blur(8px);
        }

        .stat strong {
            display: block;
            margin-top: 8px;
            font-size: 30px;
            line-height: 1;
        }

        .stat span {
            color: rgba(255, 247, 239, 0.74);
            font-size: 13px;
        }

        .grid {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
            gap: 24px;
        }

        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 26px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(10px);
        }

        .card h2,
        .card h3 {
            margin: 0;
        }

        .card p {
            color: var(--muted);
            line-height: 1.6;
        }

        .notice {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            font-size: 14px;
        }

        .notice-success {
            color: var(--success);
            background: rgba(31, 122, 80, 0.09);
            border: 1px solid rgba(31, 122, 80, 0.16);
        }

        .notice-error {
            color: var(--danger);
            background: rgba(176, 61, 46, 0.08);
            border: 1px solid rgba(176, 61, 46, 0.16);
        }

        .notice-info {
            color: #7c4a1d;
            background: rgba(181, 93, 56, 0.08);
            border: 1px solid rgba(181, 93, 56, 0.18);
        }

        .upload-box {
            margin-top: 24px;
            padding: 24px;
            border: 1.5px dashed rgba(181, 93, 56, 0.28);
            background: var(--panel-strong);
            border-radius: 22px;
        }

        .field-label {
            display: block;
            margin-bottom: 12px;
            font-weight: 600;
        }

        .file-input {
            width: 100%;
            padding: 18px;
            border: 1px solid rgba(87, 64, 39, 0.18);
            border-radius: 16px;
            background: #fff;
            color: var(--text);
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-top: 18px;
            flex-wrap: wrap;
        }

        .hint {
            font-size: 13px;
            color: var(--muted);
        }

        .button {
            appearance: none;
            border: 0;
            border-radius: 999px;
            padding: 14px 24px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #fffaf4;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
            box-shadow: 0 18px 30px rgba(181, 93, 56, 0.24);
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .stack {
            display: grid;
            gap: 18px;
        }

        .mini-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 20px;
        }

        .mini-card {
            padding: 16px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--line);
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

        .uploads {
            margin-top: 20px;
        }

        .upload-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 16px;
            align-items: center;
            padding: 16px 18px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--line);
        }

        .upload-row strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .upload-row span {
            color: var(--muted);
            font-size: 13px;
        }

        .status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 96px;
            padding: 9px 12px;
            border-radius: 999px;
            text-transform: capitalize;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }

        .status-pending,
        .status-processing {
            color: var(--warning);
            background: rgba(166, 106, 22, 0.1);
        }

        .status-completed {
            color: var(--success);
            background: rgba(31, 122, 80, 0.1);
        }

        .status-failed {
            color: var(--danger);
            background: rgba(176, 61, 46, 0.1);
        }

        .empty {
            padding: 24px;
            text-align: center;
            color: var(--muted);
            background: #fff;
            border: 1px dashed var(--line);
            border-radius: 20px;
        }

        .toast-stack {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 30;
            display: grid;
            gap: 12px;
            width: min(360px, calc(100% - 32px));
        }

        .toast {
            padding: 16px 18px;
            border-radius: 18px;
            color: #fffaf4;
            box-shadow: 0 18px 40px rgba(58, 35, 18, 0.18);
        }

        .toast strong {
            display: block;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .toast span {
            display: block;
            font-size: 13px;
            line-height: 1.5;
            color: rgba(255, 250, 244, 0.82);
        }

        .toast-success {
            background: linear-gradient(135deg, #1f7a50, #145338);
        }

        .toast-error {
            background: linear-gradient(135deg, #b03d2e, #7a291e);
        }

        .toast-info {
            background: linear-gradient(135deg, #8d4323, #60301a);
        }

        @media (max-width: 920px) {
            .hero-grid,
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .shell {
                width: min(100% - 20px, 100%);
                margin: 18px auto 28px;
            }

            .hero,
            .card {
                padding: 22px;
                border-radius: 22px;
            }

            .stats,
            .mini-grid {
                grid-template-columns: 1fr;
            }

            .upload-row {
                grid-template-columns: 1fr;
            }

            .status {
                justify-self: start;
            }
        }
    </style>
</head>
<body>
    <main class="shell">
        <section class="hero">
            <div class="hero-grid">
                <div>
                    <div class="eyebrow">Shopify Import Console</div>
                    <h1>Bulk product imports with queue-backed processing.</h1>
                    <p>Upload Shopify-ready CSV files, push them through the queue, and keep the import trail visible from the application database.</p>
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
                        <span>Failed jobs</span>
                        <strong data-stat="failed">0</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid">
            <div class="card">
                <h2>Upload CSV</h2>
                <p>Use a Shopify-style CSV with product titles, descriptions, and variant pricing. Large files are queued for background processing after upload.</p>

                @if (session('success'))
                    <div class="notice notice-success">{{ session('success') }}</div>
                @endif

                <div class="notice notice-info" id="queueNotice">
                    Imports are queued in the background. Keep the queue worker running and this page will update status automatically.
                </div>

                @if ($errors->any())
                    <div class="notice notice-error">
                        {{ $errors->first('file') ?? 'The upload could not be processed.' }}
                    </div>
                @endif

                <form method="POST" action="/upload" enctype="multipart/form-data">
                    @csrf

                    <div class="upload-box">
                        <label class="field-label" for="file">Select CSV file</label>
                        <input class="file-input" id="file" type="file" name="file" accept=".csv,.txt" required>

                        <div class="actions">
                            <div class="hint">Accepted: `.csv`, `.txt` up to 50 MB</div>
                            <button class="button" type="submit">Start Import</button>
                        </div>
                    </div>
                </form>

                <div class="mini-grid">
                    <div class="mini-card">
                        <strong>Queue-safe flow</strong>
                        <span>Uploads are stored first and processed asynchronously to avoid blocking the request.</span>
                    </div>
                    <div class="mini-card">
                        <strong>Large file ready</strong>
                        <span>The importer reads CSV rows as a stream instead of loading the whole file into memory.</span>
                    </div>
                    <div class="mini-card">
                        <strong>Shopify mapping</strong>
                        <span>The parser supports Shopify-style headers such as `Title`, `Body HTML`, and `Variant Price`.</span>
                    </div>
                    <div class="mini-card">
                        <strong>Import visibility</strong>
                        <span>Upload, product, and import log tables keep the run traceable after submission.</span>
                    </div>
                </div>
            </div>

            <aside class="card">
                <h3>Recent Uploads</h3>
                <p>Latest import attempts and their current job status.</p>

                <div class="uploads" id="recentUploads">
                    <div class="empty">Loading current uploads...</div>
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
            failed: document.querySelector('[data-stat="failed"]'),
        };

        const recentUploads = document.getElementById('recentUploads');
        const queueNotice = document.getElementById('queueNotice');
        const toastStack = document.getElementById('toastStack');
        const seenNotifications = new Set(JSON.parse(localStorage.getItem('seen_upload_notifications') || '[]'));
        let lastTrackedStatus = null;

        function formatNumber(value) {
            return new Intl.NumberFormat().format(value || 0);
        }

        function renderUploads(items) {
            if (!items.length) {
                recentUploads.innerHTML = '<div class="empty">No uploads yet.</div>';
                return;
            }

            recentUploads.innerHTML = items.map((upload) => `
                <div class="upload-row">
                    <div>
                        <strong>${escapeHtml(upload.file_name)}</strong>
                        <span>#${upload.id} · ${escapeHtml(upload.created_at || '')}</span>
                    </div>
                    <div class="status status-${escapeHtml(upload.status)}">${escapeHtml(upload.status_label || upload.status)}</div>
                </div>
            `).join('');
        }

        function renderQueueNotice(items) {
            const processing = items.filter((upload) => upload.status === 'processing').length;
            const pending = items.filter((upload) => upload.status === 'pending').length;

            if (processing > 0) {
                queueNotice.textContent = `Import worker is active. ${processing} upload${processing > 1 ? 's are' : ' is'} processing right now.`;
                return;
            }

            if (pending > 0) {
                queueNotice.textContent = `Import${pending > 1 ? 's are' : ' is'} queued and waiting for the background worker.`;
                return;
            }

            queueNotice.textContent = 'Background import queue is idle. New uploads will be picked up automatically by the worker.';
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
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
                pushToast('success', 'Import completed', `${upload.file_name} finished successfully.`);
            }

            if (upload.status === 'failed') {
                pushToast('error', 'Import failed', `${upload.file_name} finished with errors.`);
            }

            if ('Notification' in window && Notification.permission === 'granted') {
                const title = upload.status === 'completed' ? 'Import completed' : 'Import failed';
                const body = upload.status === 'completed'
                    ? `${upload.file_name} has finished processing.`
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

            if (tracked.status !== lastTrackedStatus) {
                lastTrackedStatus = tracked.status;
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

                const uploads = payload.recent_uploads || [];

                renderUploads(uploads);
                renderQueueNotice(uploads);
                trackUploadCompletion(uploads);
            } catch (error) {
                recentUploads.innerHTML = '<div class="empty">Could not load current uploads.</div>';
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
