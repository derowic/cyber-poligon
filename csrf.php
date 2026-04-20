<?php
session_start();

// Generowanie tokena CSRF (raz na sesję)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];

$message = '';
$action_performed = false;
$input_value = $_POST['username'] ?? 'janek123';

// Obsługa formularza
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';

    if (isset($_POST['action_type'])) {
        $action_type = $_POST['action_type'];

        // Wersja podatna – ignoruje token
        if ($action_type === 'vulnerable') {
            $message = "✅ Akcja wykonana (wersja podatna). Nazwa użytkownika zmieniona na: <strong>" . htmlspecialchars($input_value) . "</strong>";
            $action_performed = true;
        }

        // Wersja bezpieczna – wymaga poprawnego tokena
        if ($action_type === 'safe') {
            if (hash_equals($csrf_token, $submitted_token)) {
                $message = "✅ Akcja wykonana bezpiecznie (CSRF token poprawny). Nazwa zmieniona na: <strong>" . htmlspecialchars($input_value) . "</strong>";
                $action_performed = true;
            } else {
                $message = "❌ Błąd CSRF! Token niepoprawny lub brakujący. Akcja zablokowana.";
            }
        }
    }
    echo "<div style = 'background-color:red;'>".$message."</div>";
}



// Kod ataku do wyświetlenia w instrukcji
$poc_code = "<!DOCTYPE html>
<html lang='pl'>
<head>
    <meta charset='UTF-8'>
    <title>Atak CSRF - PoC</title>
</head>
<body>
    <h1>😈 Atak CSRF w toku...</h1>
    <form id='csrfForm' action='http://localhost:8080/csrf.php' method='POST'>
        <input type='hidden' name='action_type' value='vulnerable'>
        <input type='hidden' name='username' value='hacker123'>
    </form>
    <script>
        document.getElementById('csrfForm').submit();
    </script>
</body>
</html>";
?>

<?php
$poc_code2 = "<input type='hidden' name='action_type' value='safe'>";
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo CSRF Protection</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Prism.js dla pięknego kodu -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .vuln-card { border-top: 5px solid #dc3545; }
        .safe-card { border-top: 5px solid #198754; }
        pre[class*="language-"] { font-size: 0.85em; border-radius: 8px; margin: 0; }
        .result-box { min-height: 60px; display: flex; align-items: center; justify-content: center; font-weight: 500; }
        .instruction-step { background: #fff; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 10px; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- Nagłówek -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-dark"><i class="bi bi-shield-lock-fill"></i> Laboratorium CSRF</h1>
        <p class="lead text-secondary">Cross-Site Request Forgery (CSRF) - Demonstracja i Obrona</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← SQL Injection</a>
            <a href="xss.php" class="btn btn-outline-secondary btn-sm">← XSS</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- ==================== WERSJA PODATNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm vuln-card">
                <div class="card-body">
                    <h4 class="card-title text-danger mb-3"><i class="bi bi-unlock"></i> Wersja Podatna</h4>
                    <p class="text-muted small">Serwer akceptuje każde żądanie POST bez weryfikacji pochodzenia.</p>

                    <form method="POST" class="p-3 border rounded bg-white mb-3">
                        <input type="hidden" name="action_type" value="vulnerable">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nowa nazwa użytkownika</label>
                            <input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($input_value) ?>">
                        </div>
                        <button type="submit" class="btn btn-danger btn-sm w-100">Zmień nazwę (podatnie)</button>
                    </form>

                    <?php if ($message && isset($_POST['action_type']) && $_POST['action_type'] === 'vulnerable'): ?>
                        <div class="alert alert-danger result-box mb-3 small"><?= $message ?></div>
                    <?php endif; ?>

                    <p class="fw-bold small mb-1 mt-4 text-secondary text-uppercase">Brak zabezpieczeń w kodzie:</p>
                    <pre class="language-php"><code>&lt;?php
// Po stronie serwera:
if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    \$db->updateUser(\$_POST['username']); 
    // BRAK TOKENA!
}
?&gt;</code></pre>
                </div>
            </div>
        </div>

        <!-- ==================== WERSJA BEZPIECZNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm safe-card">
                <div class="card-body">
                    <h4 class="card-title text-success mb-3"><i class="bi bi-shield-check"></i> Wersja Bezpieczna</h4>
                    <p class="text-muted small">Serwer wymaga unikalnego, tajnego tokena z sesji użytkownika.</p>

                    <form method="POST" class="p-3 border rounded bg-white mb-3">
                        <input type="hidden" name="action_type" value="safe">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nowa nazwa użytkownika</label>
                            <input type="text" name="username" class="form-control form-control-sm" value="<?= htmlspecialchars($input_value) ?>">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100">Zmień nazwę (bezpiecznie)</button>
                    </form>

                    <?php if ($message && isset($_POST['action_type']) && $_POST['action_type'] === 'safe'): ?>
                        <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-warning' ?> result-box mb-3 small">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>

                    <p class="fw-bold small mb-1 mt-4 text-secondary text-uppercase">Zastosowany mechanizm:</p>
                    <pre class="language-php"><code>&lt;?php
// Weryfikacja tokena:
if (hash_equals(\$_SESSION['csrf_token'], \$_POST['csrf_token'])) {
    // Akcja dozwolona
} else {
    die("Błąd CSRF!");
}
?&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKCJA INSTRUKCJI ATAKU -->
    <div class="mt-5 p-4 bg-white border rounded shadow-sm">
        <h3 class="mb-4 text-dark"><i class="bi bi-info-circle-fill text-warning"></i> Jak przetestować atak?</h3>
        
        <div class="row">
            <div class="col-md-5">
                <div class="instruction-step">
                    <span class="badge bg-warning text-dark mb-2">Krok 1</span>
                    <p class="small mb-0">Skopiuj poniższy kod i zapisz go jako plik <strong><code>atak.html</code></strong> na swoim komputerze (poza serwerem).</p>
                </div>
                <div class="instruction-step">
                    <span class="badge bg-warning text-dark mb-2">Krok 2</span>
                    <p class="small mb-0">Upewnij się, że jesteś zalogowany w tym demo (odśwież stronę <code>csrf.php</code>).</p>
                </div>
                <div class="instruction-step">
                    <span class="badge bg-warning text-dark mb-2">Krok 3</span>
                    <p class="small mb-0">Otwórz plik <code>atak.html</code> w nowej karcie przeglądarki. Zobaczysz, jak formularz wysyła się sam.</p>
                </div>
                <div class="instruction-step">
                    <span class="badge bg-warning text-dark mb-2">Krok 4</span>
                    <p class="small mb-0">Otwarcie pliku <code>atak.html</code> przeniesie cię na http://localhost:8080/csrf.php, teraz w polach input masz hacker123</p>
                </div>
                <div class="instruction-step">
                    <span class="badge bg-warning text-dark mb-2">Krok 5</span>
                    <p class="small mb-0">W pliku <code>atak.html</code> zmień na</p>
                </div>
            </div>
            <div class="col-md-7">
                <p class="fw-bold small text-secondary text-uppercase mb-1">Kod pliku atak.html (Proof of Concept):</p>
                <pre class="language-html"><code><?= htmlspecialchars($poc_code) ?></code></pre>
                <p class="fw-bold small text-secondary text-uppercase mb-1" style="margin-top:10px">W pliku <code>atak.html</code> zmień z <b>vulnerable</b> na <b>safe</b>  i uruchom</p>
                <pre class="language-html" ><code><?= htmlspecialchars($poc_code) ?></code></pre>
            </div>
        </div>
    </div>

    <footer class="text-center mt-5 py-4 border-top text-muted small">
        Demo Bezpieczeństwa Aplikacji • PHP 8.3 • Prism.js • Bootstrap 5
    </footer>
</div>

<!-- Skrypty JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<!-- Obsługa języków Prism -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-markup.min.js"></script>

</body>
</html>