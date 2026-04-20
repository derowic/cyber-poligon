<?php
// Command Injection Demo
$command_input = $_POST['command'] ?? '';
$vulnerable_output = '';
$safe_output = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['action']) && $_POST['action'] === 'vulnerable') {
        // === WERSJA PODATNA ===
        $cmd = "ping -c 3 " . $command_input;           // ← podatność tutaj!
        $vulnerable_output = shell_exec($cmd);
        
        if ($vulnerable_output === null) {
            $vulnerable_output = "Błąd wykonania polecenia.";
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'safe') {
        // === WERSJA BEZPIECZNA ===
        // whitelist dozwolonych znaków + escapeshellarg
        if (preg_match('/^[a-zA-Z0-9.-]+$/', $command_input)) {
            $safe_cmd = "ping -c 3 " . escapeshellarg($command_input);
            $safe_output = shell_exec($safe_cmd);
            
            if ($safe_output === null) {
                $safe_output = "Błąd wykonania polecenia.";
            }
        } else {
            $safe_output = "Nieprawidłowe znaki w adresie IP / hostname. Dozwolone tylko litery, cyfry, kropka i myślnik.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Command Injection (OS Command Injection)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; background: #fff3f3; }
        .safe-card { border-left: 6px solid #28a745; background: #f0fff0; }
        .highlight { font-weight: bold; color: #dc3545; background: #fff3cd; padding: 2px 6px; border-radius: 4px; }
        .safe-highlight { font-weight: bold; color: #28a745; background: #d4edda; padding: 2px 6px; border-radius: 4px; }
        .output-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            font-family: monospace;
            white-space: pre-wrap;
            min-height: 180px;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Demonstracja Command Injection</h1>
        <p class="lead text-muted">Lewa strona = podatna • Prawa strona = zabezpieczona</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">← SQL Injection</a>
            <a href="xss.php" class="btn btn-outline-secondary">← XSS</a>
            <a href="csrf.php" class="btn btn-outline-secondary">← CSRF</a>
            <a href="idor.php" class="btn btn-outline-secondary">← IDOR</a>
        </div>
    </div>

    <div class="row g-5">
        <!-- ==================== WERSJA PODATNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow vuln-card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-bug"></i> Wersja podatna na Command Injection</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Polecenie systemowe budowane przez konkatenację inputu użytkownika.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="vulnerable">
                        <div class="input-group">
                            <input type="text" name="command" class="form-control" 
                                   placeholder="Wpisz adres IP lub hostname (np. 8.8.8.8)" 
                                   value="<?= htmlspecialchars($command_input) ?>">
                            <button class="btn btn-danger">Wykonaj ping</button>
                        </div>
                    </form>

                    <?php if ($vulnerable_output): ?>
                        <div class="output-box">
                            <strong>Wynik polecenia (podatny):</strong><br>
                            <?= htmlspecialchars($vulnerable_output) ?>
                        </div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (podatny):</h6>
                    <pre class="bg-white p-3 border"><code>$cmd = "ping -c 3 " . <span class="highlight">$command_input</span>;
$vulnerable_output = shell_exec($cmd);</code></pre>

                    <div class="alert alert-danger small mt-3">
                        <strong>Przykładowe ataki do przetestowania:</strong><br>
                        • <code>8.8.8.8; ls -la</code><br>
                        • <code>8.8.8.8 &amp;&amp; whoami</code><br>
                        • <code>8.8.8.8; cat /etc/passwd</code><br>
                        • <code>127.0.0.1; id</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== WERSJA BEZPIECZNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow safe-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Wersja zabezpieczona</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Input jest walidowany + używamy <code>escapeshellarg()</code>.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="safe">
                        <div class="input-group">
                            <input type="text" name="command" class="form-control" 
                                   placeholder="Wpisz adres IP lub hostname" 
                                   value="<?= htmlspecialchars($command_input) ?>">
                            <button class="btn btn-success">Wykonaj ping</button>
                        </div>
                    </form>

                    <?php if ($safe_output): ?>
                        <div class="output-box">
                            <strong>Wynik polecenia (bezpieczny):</strong><br>
                            <?= htmlspecialchars($safe_output) ?>
                        </div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (bezpieczny):</h6>
                    <pre class="bg-white p-3 border"><code>if (preg_match('/^[a-zA-Z0-9.-]+$/', $command_input)) {
    $safe_cmd = "ping -c 3 " . <span class="safe-highlight">escapeshellarg($command_input)</span>;
    $safe_output = shell_exec($safe_cmd);
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Instrukcje dla uczniów -->
    <div class="mt-5">
        <h3 class="text-center mb-4">Instrukcja testowania ataku Command Injection dla uczniów</h3>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <strong>Jak przetestować atak (wersja podatna)</strong>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li>Otwórz stronę <strong>Command Injection</strong></li>
                            <li>W lewej kolumnie wpisz normalny adres, np. <code>8.8.8.8</code> i kliknij „Wykonaj ping”</li>
                            <li>Teraz wpisz atak:</li>
                            <ul>
                                <li><code>8.8.8.8; whoami</code></li>
                                <li><code>8.8.8.8; ls -la</code></li>
                                <li><code>8.8.8.8; cat /etc/passwd</code></li>
                                <li><code>127.0.0.1; id</code></li>
                            </ul>
                            <li>Zaobserwuj, że oprócz wyniku ping pojawia się wynik innych poleceń systemowych.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <strong>Dlaczego wersja bezpieczna działa?</strong>
                    </div>
                    <div class="card-body">
                        <p>W bezpiecznej wersji stosujemy dwie warstwy ochrony:</p>
                        <ul>
                            <li><strong>Walidacja regex</strong> – pozwala tylko na dozwolone znaki (litery, cyfry, kropka, myślnik)</li>
                            <li><strong>escapeshellarg()</strong> – traktuje całą wartość jako jeden argument, a nie część polecenia</li>
                        </ul>
                        <p class="mt-3"><strong>Zadanie dla uczniów:</strong><br>
                        Spróbuj obejść zabezpieczenie w prawej kolumnie. Czy się uda?</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 small text-muted">
        Demo OS Command Injection • PHP 8.3 + Bootstrap 5<br>
        <strong>Uwaga:</strong> Demo działa tylko w środowisku Linux (Docker)
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>