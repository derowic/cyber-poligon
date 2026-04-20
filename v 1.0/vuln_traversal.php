<?php
$page = $_GET['page'] ?? 'about.html';
$mode = $_GET['mode'] ?? 'vulnerable';

// Przygotowanie plików tekstowych do testów (symulacja podstron)
if (!file_exists('about.html')) file_put_contents('about.html', 'To jest zwykła strona O nas.');
if (!file_exists('contact.html')) file_put_contents('contact.html', 'To jest strona Kontaktowa.');

// --- LOGIKA PODATNA ---
if ($mode == 'vulnerable') {
    // Bezpośrednie wczytanie pliku podanego w URL
    $content = @file_get_contents($page);
}

// --- LOGIKA BEZPIECZNA ---
if ($mode == 'secure') {
    // Whitelisting - pozwalamy tylko na konkretne pliki
    $allowed_pages = ['about.html', 'contact.html'];
    if (in_array($page, $allowed_pages)) {
        $content = file_get_contents($page);
    } else {
        $content = "BŁĄD: Nie masz uprawnień do tego pliku!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Path Traversal Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 05: Path Traversal / LFI</h2>
    
    <nav class="nav nav-pills mb-4">
        <a class="nav-link" href="?page=about.html&mode=<?php echo $mode; ?>">O nas</a>
        <a class="nav-link" href="?page=contact.html&mode=<?php echo $mode; ?>">Kontakt</a>
    </nav>

    <div class="card p-4 shadow-sm bg-white">
        <h5>Treść wczytanego pliku:</h5>
        <hr>
        <pre><?php echo htmlspecialchars($content); ?></pre>
    </div>

    <div class="mt-5 border-top pt-3">
        <h5>Misja dla ucznia:</h5>
        <ol>
            <li>Klikaj w linki powyżej i patrz na URL (parametr <code>page=...</code>).</li>
            <li>W trybie podatnym spróbuj "wyjść" z folderu i odczytać plik z hasłami systemowymi Linuxa.</li>
            <li>Wpisz w URL: <code>?page=../../../../etc/passwd</code></li>
            <li>Spróbuj odczytać plik konfiguracyjny swojej aplikacji: <code>?page=index.php</code></li>
        </ol>
    </div>
</div>
</body>
</html>