<?php
$page = $_GET['page'] ?? 'about.html';
$mode = $_GET['mode'] ?? 'vulnerable';

// Przygotowanie plików tekstowych do testów (symulacja podstron)
if (!file_exists('about.html')) file_put_contents('about.html', 'To jest zwykła strona O nas.');
if (!file_exists('contact.html')) file_put_contents('contact.html', 'To jest strona Kontaktowa.');

$content = "";
$error = "";

// --- LOGIKA PODATNA ---
if ($mode == 'vulnerable') {
    // KATASTROFA: Serwer bezkrytycznie ufa tekstowi z $_GET['page']
    // i próbuje otworzyć dowolną ścieżkę, jaką poda użytkownik.
    $content = @file_get_contents($page);
    if ($content === false) $error = "Błąd: Nie można odnaleźć pliku: " . htmlspecialchars($page);
}

// --- LOGIKA BEZPIECZNA ---
if ($mode == 'secure') {
    // OBRONA: Biała lista (Whitelisting)
    // Pozwalamy tylko na konkretne, znane nam pliki.
    $allowed_pages = ['about.html', 'contact.html'];
    
    if (in_array($page, $allowed_pages)) {
        $content = file_get_contents($page);
    } else {
        $error = "ODMOWA DOSTĘPU: Próba wczytania nieautoryzowanego pliku!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Path Traversal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .file-display { background: #272822; color: #f8f8f2; padding: 20px; border-radius: 5px; font-family: monospace; min-height: 100px; white-space: pre-wrap; border: 1px solid #444; }
        .url-preview { background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.9rem; }
        .url-param { color: #dc3545; font-weight: bold; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Path Traversal</h1>
        <p class="lead text-muted">Przeglądanie plików serwera poprzez manipulację ścieżką dostępu.</p>
        <div class="btn-group shadow-sm">
            <a href="?page=about.html&mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?page=about.html&mode=secure" class="btn <?= $mode == 'secure' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: DEMO -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-file-earmark-code"></i> Czytnik podstron
                </div>
                <div class="card-body">
                    <nav class="nav nav-pills mb-4">
                        <a class="nav-link <?= $page == 'about.html' ? 'active' : '' ?>" href="?page=about.html&mode=<?= $mode ?>">O nas</a>
                        <a class="nav-link <?= $page == 'contact.html' ? 'active' : '' ?>" href="?page=contact.html&mode=<?= $mode ?>">Kontakt</a>
                    </nav>

                    <div class="mb-4">
                        <small class="text-muted">Aktualny parametr w URL:</small>
                        <div class="url-preview">
                            ?page=<span class="url-param"><?= htmlspecialchars($page) ?></span>
                        </div>
                    </div>

                    <h6>Zawartość pliku:</h6>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> <?= $error ?></div>
                    <?php endif; ?>
                    
                    <div class="file-display">
                        <?= $content ? htmlspecialchars($content) : "brak treści" ?>
                    </div>
                </div>
            </div>

            <!-- ZADANIA -->
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fst-italic">Misja dla ucznia:</div>
                <div class="card-body">
                    <ol class="small">
                        <li><strong>Standardowe użycie:</strong> Przełączaj między "O nas" a "Kontakt". Zobacz, jak zmienia się zawartość.</li>
                        <li><strong>Atak LFI (Local File Inclusion):</strong> W trybie podatnym spróbuj odczytać plik z innego folderu. Dopisz w URL: <br><code>?page=../../../../etc/passwd</code> (Plik z użytkownikami Linuxa).</li>
                        <li><strong>Odczyt kodu źródłowego:</strong> Spróbuj odczytać plik, w którym teraz jesteś, wpisując: <br><code>?page=index.php</code> (lub nazwę tego pliku).</li>
                        <li><strong>Test obrony:</strong> Przełącz na tryb bezpieczny i spróbuj wykonać te same ataki.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="traversalAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Co to jest Path Traversal?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            Atak polega na wykorzystaniu znaków <code>../</code> (kropka-kropka-ukośnik), które w systemach operacyjnych oznaczają **"wyjdź jeden folder w górę"**. 
                            Jeśli aplikacja nie filtruje tych znaków, haker może "wędrować" po dysku twardym serwera tak samo, jak robi to w konsoli lub menedżerze plików.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Analiza kodu (Podatność)
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <pre class="bg-light p-2 border"><code>$p = $_GET['page'];
echo file_get_contents($p);</code></pre>
                            Programista założył, że użytkownik wpisze tylko <code>about.html</code>. 
                            Haker jednak wpisuje <code>../../config.php</code>. Serwer interpretuje to jako: "idź dwa foldery wyżej i otwórz plik z hasłami do bazy".
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak się zabezpieczyć?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Biała lista (Whitelisting):</strong> Najlepsza metoda. Pozwalaj tylko na pliki z tablicy `['about.html', 'contact.html']`.</li>
                                <li><strong>Funkcja basename():</strong> Funkcja <code>basename($page)</code> odcina wszystkie ścieżki i foldery, zostawiając samą nazwę pliku (np. zamienia `../../etc/passwd` na `passwd`).</li>
                                <li><strong>Realpath check:</strong> Sprawdź, czy ścieżka wynikowa (wygenerowana przez <code>realpath()</code>) zaczyna się od folderu Twojej aplikacji.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>