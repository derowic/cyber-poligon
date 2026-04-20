<?php
// Open Redirect Lab Logic
$target_url = $_GET['url'] ?? '';
$action = $_GET['action'] ?? '';
$error = "";

if ($action === 'vulnerable' && !empty($target_url)) {
    // --- LOGIKA PODATNA ---
    // Serwer bezkrytycznie ufa temu, co jest w parametrze 'url'
    header("Location: " . $target_url);
    exit;
}

if ($action === 'safe' && !empty($target_url)) {
    // --- LOGIKA BEZPIECZNA ---
    $allowed_domains = ['localhost', '127.0.0.1', 'example.com', 'twoja-szkola.pl'];
    
    // Wyciągamy hosta z podanego URL
    $parsed = parse_url($target_url);
    $host = $parsed['host'] ?? '';

    if (in_array($host, $allowed_domains) || empty($host)) {
        // Jeśli host jest na białej liście lub jest to ścieżka relatywna (np. /index.php)
        header("Location: " . $target_url);
        exit;
    } else {
        $error = "Zablokowano: Domena <strong>" . htmlspecialchars($host) . "</strong> nie znajduje się na białej liście zaufanych witryn.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Open Redirect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .url-preview { background: #f8f9fa; border: 1px solid #ddd; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; }
        .url-param { color: #dc3545; font-weight: bold; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Open Redirect</h1>
        <p class="lead text-muted">Przekierowania URL – potężne narzędzie w rękach phishera.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm mb-4"><i class="bi bi-shield-exclamation"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: DEMO -->
        <div class="col-lg-6">
            <!-- WERSJA PODATNA -->
            <div class="card shadow-sm mb-4 vuln-card">
                <div class="card-header bg-white fw-bold"><i class="bi bi-bug text-danger"></i> Tryb: Podatny</div>
                <div class="card-body">
                    <p class="small text-muted">Kliknij poniższe linki, aby zobaczyć, jak aplikacja reaguje na różne parametry URL.</p>
                    
                    <div class="d-grid gap-2 mb-4">
                        <a href="?action=vulnerable&url=https://www.google.com" class="btn btn-outline-danger btn-sm text-start">
                            <i class="bi bi-link-45deg"></i> Przekieruj na Google.com
                        </a>
                        <a href="?action=vulnerable&url=https://evil-phishing-site.com/login" class="btn btn-outline-danger btn-sm text-start text-truncate">
                            <i class="bi bi-link-45deg"></i> Przekieruj na złośliwą stronę (Phishing)
                        </a>
                    </div>

                    <h6>Co widzi użytkownik w linku?</h6>
                    <div class="url-preview small mb-3">
                        https://twoja-strona.pl/redirect.php?url=<span class="url-param">https://evil.com</span>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <small>Kod: <code>header("Location: " . $_GET['url']);</code></small>
                </div>
            </div>

            <!-- WERSJA BEZPIECZNA -->
            <div class="card shadow-sm safe-card">
                <div class="card-header bg-white fw-bold text-success"><i class="bi bi-shield-check"></i> Tryb: Bezpieczny (Biała Lista)</div>
                <div class="card-body">
                    <p class="small text-muted">W tym trybie serwer sprawdza, czy domena docelowa jest zaufana.</p>
                    <div class="d-grid gap-2">
                        <a href="?action=safe&url=https://example.com" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-check2-circle"></i> Przekieruj na example.com (Dozwolone)
                        </a>
                        <a href="?action=safe&url=https://evil.com" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-x-circle"></i> Przekieruj na evil.com (Zablokowane)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-6">
            <div class="accordion shadow-sm" id="eduAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Co to jest Open Redirect?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            Podatność występuje, gdy aplikacja przyjmuje adres URL od użytkownika i przekierowuje go tam bez żadnej weryfikacji. 
                            Hakerzy wykorzystują to, aby linki do ich złośliwych stron wyglądały na bezpieczne, ponieważ zaczynają się od adresu <strong>zaufanej domeny</strong> (np. Twojego banku lub szkoły).
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Dlaczego to jest groźne?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            Głównym zagrożeniem jest <strong>Phishing</strong>. 
                            Użytkownik widzi link: <br><code>https://facebook.com/l.php?u=https://hacker.com</code><br>
                            Ufa domenie <em>facebook.com</em>, klika w link, a trafia na stronę, która wyłudza hasło. Open Redirect sprawia, że zaufanie do marki zostaje użyte jako "tarcza" dla ataku.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak się bronić?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Biała lista (Allow-list):</strong> Pozwalaj na przekierowania tylko do domeny własnej i zaufanych partnerów.</li>
                                <li><strong>Przekierowania relatywne:</strong> Jeśli to możliwe, używaj tylko ścieżek lokalnych (np. <code>/login.php</code> zamiast pełnego URL).</li>
                                <li><strong>Strona pośrednia:</strong> Wyświetlaj komunikat: <em>"Opuszczasz naszą stronę i przechodzisz do zewnętrznego serwisu. Czy na pewno?"</em>.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="bi bi-lightbulb"></i> Zadanie dla ucznia:</h6>
                    <ol class="small ps-3 mb-0">
                        <li>Użyj trybu podatnego. Zauważ, jak zmienia się adres w przeglądarce po kliknięciu.</li>
                        <li>Spróbuj ręcznie zmienić parametr <code>url=</code> w pasku adresu na <code>https://wikipedia.org</code>.</li>
                        <li>Przetestuj tryb bezpieczny. Spróbuj "oszukać" filtr, wpisując domenę, której nie ma na liście.</li>
                        <li><strong>Dla ambitnych:</strong> Spróbuj użyć adresu relatywnego w trybie bezpiecznym, np. <code>?url=index.php</code>. Czy działa? Dlaczego?</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>