<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Image Migration Tool - TechnoPark</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .stats {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .stat-item:last-child { border-bottom: none; }
        .stat-label { color: #666; font-weight: 500; }
        .stat-value {
            color: #667eea;
            font-weight: 700;
            font-size: 18px;
        }
        .total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #667eea;
        }
        .total .stat-value {
            font-size: 24px;
        }
        .password-section {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .buttons {
            display: flex;
            gap: 15px;
        }
        button {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-dry-run {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #dee2e6;
        }
        .btn-dry-run:hover {
            background: #e9ecef;
        }
        .btn-migrate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-migrate:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .results {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        .results.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .results.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .results h3 {
            margin-bottom: 15px;
        }
        .result-item {
            padding: 8px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .result-item:last-child { border-bottom: none; }
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .warning strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Image Migration Tool</h1>
        <p class="subtitle">Rename images to structured format (product_123_1.jpg)</p>

        <div class="stats">
            <div class="stat-item">
                <span class="stat-label">Profile Images</span>
                <span class="stat-value">{{ $stats['profiles_to_migrate'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Product Images</span>
                <span class="stat-value">{{ $stats['products_to_migrate'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Project Images</span>
                <span class="stat-value">{{ $stats['projects_to_migrate'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Service Images</span>
                <span class="stat-value">{{ $stats['services_to_migrate'] }}</span>
            </div>
            <div class="stat-item total">
                <span class="stat-label">Total Images to Migrate</span>
                <span class="stat-value">{{ $stats['total'] }}</span>
            </div>
        </div>

        @if($stats['total'] > 0)
            <div class="warning">
                <strong>⚠️ Important:</strong>
                Run DRY RUN first to see what will be renamed without making changes.
            </div>

            <div class="password-section">
                <label for="password">{{ __('Migration Password') }}</label>
                <input type="password" id="password" placeholder="{{ __('Enter migration password') }}">
            </div>

            <div class="buttons">
                <button class="btn-dry-run" onclick="runMigration(true)">
                    🔍 Dry Run (Preview)
                </button>
                <button class="btn-migrate" onclick="runMigration(false)">
                    ✅ Run Migration
                </button>
            </div>

            <div class="loading" id="loading">
                <div class="spinner"></div>
                <p>Processing migration...</p>
            </div>

            <div class="results" id="results"></div>
        @else
            <div class="results success" style="display: block;">
                <h3>✅ All images already migrated!</h3>
                <p>All images are already using structured naming format.</p>
            </div>
        @endif
    </div>

    <script>
        async function runMigration(dryRun) {
            const password = document.getElementById('password').value;
            const loading = document.getElementById('loading');
            const results = document.getElementById('results');
            const buttons = document.querySelectorAll('button');

            if (!password) {
                alert('Please enter the migration password');
                return;
            }

            // Disable buttons and show loading
            buttons.forEach(btn => btn.disabled = true);
            loading.style.display = 'block';
            results.style.display = 'none';

            try {
                const response = await fetch('{{ route("admin.image-migration.migrate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        password: password,
                        dry_run: dryRun
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Migration failed');
                }

                // Show results
                results.className = 'results success';
                results.style.display = 'block';

                let html = `<h3>${dryRun ? '🔍 Dry Run Results' : '✅ Migration Complete'}</h3>`;
                html += `<p style="margin-bottom: 15px;"><strong>Total Renamed:</strong> ${data.total_renamed} images</p>`;

                for (const [type, result] of Object.entries(data.results)) {
                    html += `<div class="result-item">`;
                    html += `<strong>${type.charAt(0).toUpperCase() + type.slice(1)}:</strong> ${result.renamed} images`;
                    if (result.errors && result.errors.length > 0) {
                        html += ` (${result.errors.length} errors)`;
                    }
                    html += `</div>`;
                }

                results.innerHTML = html;

                if (!dryRun && data.total_renamed > 0) {
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }

            } catch (error) {
                results.className = 'results error';
                results.style.display = 'block';
                results.innerHTML = `<h3>❌ Error</h3><p>${error.message}</p>`;
            } finally {
                loading.style.display = 'none';
                buttons.forEach(btn => btn.disabled = false);
            }
        }
    </script>
</body>
</html>
