<?php
// SSRF Lab Logic
$url_input = $_POST['url'] ?? 'https://example.com';
$vulnerable_result = '';
$safe_result = '';
$vulnerable_error = '';
$safe_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $target_url = $_POST['url'] ?? '';

    // --- TRYB PODATNY ---
    if (isset($_POST['action']) && $_POST['action'] === 'vulnerable') {
        if (filter_var($target_url, FILTER_VALIDATE_URL)) {
            $context = stream_context_create(['http' => ['timeout' => 3]]);
            // BŁĄD: Serwer bezkrytycznie pobiera zawartość z każdego URL
            $vulnerable_result = @file_get_contents($target_url, false, $context);
            if ($vulnerable_result === false) $vulnerable_error = "Błąd połączenia.";
        } else {
            $vulnerable_error = "Nieprawidłowy URL.";
        }
    }

    // --- TRYB BEZPIECZNY ---
    if (isset($_POST['action']) && $_POST['action'] === 'safe') {
        $allowed_domains = ['example.com', 'google.com'];
        $parsed = parse_url($target_url);
        $host = $parsed['host'] ?? '';

        // OBRONA: Biała lista domen i blokada adresów IP (np. localhost)
        if (in_array($host, $allowed_domains)) {
            $context = stream_context_create(['http' => ['timeout' => 3]]);
            $safe_result = @file_get_contents($target_url, false, $context);
        } else {
            $safe_error = "❌ SSRF ZABLOKOWANY: Domena '$host' nie jest na białej liście!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: SSRF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .result-box { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; max-height: 250px; overflow: auto; font-size: 0.85rem; }
        .code-view { background: #1e1e1e; color: #fff; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.8rem; }
        .highlight { color: #f92672; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: SSRF</h1>
        <p class="lead text-muted">Server-Side Request Forgery – kiedy Twój serwer staje się narzędziem ataku.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: DEMO -->
        <div class="col-lg-7">
            <!-- WERSJA PODATNA -->
            <div class="card shadow vuln-card mb-4">
                <div class="card-header bg-white fw-bold"><i class="bi bi-bug text-danger"></i> Pobieracz zawartości (Tryb Podatny)</div>
                <div class="card-body">
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="vulnerable">
                        <div class="input-group">
                            <input type="text" name="url" class="form-control" value="<?= htmlspecialchars($url_input) ?>">
                            <button class="btn btn-danger">Pobierz</button>
                        </div>
                    </form>
                    <?php if ($vulnerable_result): ?>
                        <div class="result-box"><?= htmlspecialchars(substr($vulnerable_result, 0, 1000)) ?></div>
                    <?php endif; ?>
                    <?php if ($vulnerable_error): ?>
                        <div class="alert alert-danger py-2 mt-2"><?= $vulnerable_error ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- WERSJA BEZPIECZNA -->
            <div class="card shadow safe-card">
                <div class="card-header bg-white fw-bold text-success"><i class="bi bi-shield-check"></i> Pobieracz zawartości (Tryb Bezpieczny)</div>
                <div class="card-body">
                    <form method="POST" class="mb-3">
                        <input type="hidden" name="action" value="safe">
                        <div class="input-group">
                            <input type="text" name="url" class="form-control" value="<?= htmlspecialchars($url_input) ?>">
                            <button class="btn btn-success">Pobierz</button>
                        </div>
                    </form>
                    <?php if ($safe_result): ?>
                        <div class="result-box"><?= htmlspecialchars(substr($safe_result, 0, 1000)) ?></div>
                    <?php endif; ?>
                    <?php if ($safe_error): ?>
                        <div class="alert alert-danger py-2 mt-2"><?= $safe_error ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white small">Analiza techniczna</div>
                <div class="card-body">
                    <h6>Kod źródłowy (Podatny):</h6>
                    <div class="code-view mb-3">
                        $url = $_POST['url'];<br>
                        echo <span class="highlight">file_get_contents($url);</span>
                    </div>
                    <p class="small text-muted">Serwer wykonuje żądanie <b>z wnętrza</b> Twojej sieci. Dla innych usług w sieci lokalnej (jak baza danych czy API) to żądanie wygląda na zaufane.</p>
                </div>
            </div>

            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fst-italic">Zadania dla ucznia:</div>
                <div class="card-body small">
                    <ol>
                        <li><strong>Atak na localhost:</strong> W trybie podatnym wpisz <code>http://localhost</code> lub <code>http://127.0.0.1</code>. Czy serwer pobrał własną stronę główną?</li>
                        <li><strong>Skanowanie portów:</strong> Spróbuj <code>http://127.0.0.1:3306</code> (MySQL). Jeśli dostaniesz inny błąd niż przy porcie 1234, oznacza to, że port jest otwarty.</li>
                        <li><strong>Atak na chmurę:</strong> Jeśli serwer jest w AWS, spróbuj: <br><code>http://169.254.169.254/latest/meta-data/</code>. Możesz tam znaleźć klucze dostępowe!</li>
                        <li><strong>Obrona:</strong> Przetestuj te same adresy w trybie bezpiecznym. Zauważ, że tylko "example.com" i "google.com" są dozwolone.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>