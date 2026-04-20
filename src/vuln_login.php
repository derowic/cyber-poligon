<?php
session_start();

// Resetowanie prób (pomocne dla uczniów)
if (isset($_GET['reset'])) {
    $_SESSION['attempts'] = 0;
    header("Location: ?mode=" . ($_GET['mode'] ?? 'vulnerable'));
    exit;
}

$message = "";
$mode = $_REQUEST['mode'] ?? 'vulnerable';

// Dane testowe
$valid_user = "admin";
$valid_pass = "admin123"; 

if (isset($_POST['login_action'])) {
    $user = $_POST['user'] ?? '';
    $pass = $_POST['pass'] ?? '';

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        // Brak opóźnień, brak limitów. Serwer odpowiada tak szybko, jak potrafi.
        if ($user === $valid_user && $pass === $valid_pass) {
            $message = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Zalogowano! Hasło to: <b>$valid_pass</b></div>";
        } else {
            $message = "<div class='alert alert-danger'><i class='bi bi-x-circle'></i> Błędne hasło!</div>";
        }
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // 1. Blokada konta (Rate Limiting)
        if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= 5) {
            $message = "<div class='alert alert-warning'><i class='bi bi-hourglass-split'></i> Zbyt wiele prób! Sesja zablokowana. <a href='?reset=1&mode=secure' class='alert-link'>Resetuj</a></div>";
        } else {
            // 2. Sztuczne spowolnienie (Throttling) - każda próba trwa 1 sekundę
            sleep(1); 

            if ($user === $valid_user && $pass === $valid_pass) {
                $_SESSION['attempts'] = 0;
                $message = "<div class='alert alert-success'>Zalogowano bezpiecznie!</div>";
            } else {
                $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
                $message = "<div class='alert alert-danger'>Błąd! Próba: " . $_SESSION['attempts'] . "/5</div>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Brute Force</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .logic-view { background: #1e1e1e; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Brute Force</h1>
        <p class="lead text-muted">Złamanie hasła poprzez automatyczne sprawdzanie tysięcy kombinacji.</p>
        <div class="btn-group shadow-sm">
            <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?mode=safe" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: LOGOWANIE -->
        <div class="col-lg-5">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold">Panel Logowania</div>
                <div class="card-body">
                    <form method="POST" action="?mode=<?= $mode ?>">
                        <div class="mb-2">
                            <label class="small fw-bold">Użytkownik:</label>
                            <input type="text" name="user" class="form-control" value="admin" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold">Hasło:</label>
                            <input type="password" name="pass" class="form-control" placeholder="Wpisz hasło...">
                        </div>
                        <button type="submit" name="login_action" class="btn btn-primary w-100 fw-bold">ZALOGUJ SIĘ</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <?= $message ?>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-white small">Podpowiedź (Słownik hasła)</div>
                <div class="card-body">
                    <p class="small text-muted mb-1 text-center">Popularne hasła do testu:</p>
                    <div class="d-flex flex-wrap justify-content-center gap-1">
                        <span class="badge bg-light text-dark border">123456</span>
                        <span class="badge bg-light text-dark border">password</span>
                        <span class="badge bg-light text-dark border">qwerty</span>
                        <span class="badge bg-light text-dark border">admin123</span>
                        <span class="badge bg-light text-dark border">letmein1</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: INSTRUKCJA ATAKU -->
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <i class="bi bi-terminal"></i> Misja: Użyj Burp Suite Intruder
                </div>
                <div class="card-body">
                    <ol class="small">
                        <li>Uruchom <b>Burp Suite</b> i włącz <b>Intercept</b>.</li>
                        <li>Wpisz dowolne hasło w formularzu obok i kliknij "Zaloguj".</li>
                        <li>W Burp: Kliknij Prawym → <b>Send to Intruder</b>.</li>
                        <li>W zakładce <b>Positions</b>: Zaznacz wartość hasła i kliknij <b>Add §</b>.</li>
                        <li>W zakładce <b>Payloads</b>: Wpisz listę haseł z podpowiedzi poniżej.</li>
                        <li>Kliknij <b>Start Attack</b>.</li>
                        <li><b>Analiza:</b> Znajdź rekord, który ma inną <b>Length</b> (długość odpowiedzi) lub <b>Status</b>. To jest Twoje hasło!</li>
                    </ol>
                    
                    <hr>
                    <h6>Kod serwera (Analiza):</h6>
                    <div class="logic-view">
                        <?php if ($mode == 'vulnerable'): ?>
                            // Tryb podatny<br>
                            if ($pass === $db_pass) {<br>
                            &nbsp;&nbsp;return "Zalogowano";<br>
                            }
                            <p class="text-danger mt-2 small">// Brak limitu prób! Haker może wysłać 1000 haseł na sekundę.</p>
                        <?php else: ?>
                            // Tryb bezpieczny<br>
                            <span class="text-success">sleep(1);</span> // Throttling (Spowolnienie)<br>
                            if ($attempts >= 5) {<br>
                            &nbsp;&nbsp;<span class="text-success">die("Blokada konta");</span><br>
                            }
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKCJA EDUKACYJNA -->
    <div class="row mt-4">
        <div class="col-md-4 text-center">
            <h5>Dlaczego to działa?</h5>
            <p class="small text-muted">Komputery są niesamowicie szybkie. Proste hasło "admin123" może zostać sprawdzone w ułamku sekundy. Jeśli serwer pozwala na nielimitowane próby, każde słabe hasło zostanie w końcu złamane.</p>
        </div>
        <div class="col-md-4 text-center">
            <h5>Co to jest Throttling?</h5>
            <p class="small text-muted">To sztuczne opóźnienie odpowiedzi (np. o 1 sekundę). Dla człowieka to niezauważalne, ale dla bota sprawdzającego milion haseł, czas ataku wydłuża się z minut do... lat.</p>
        </div>
        <div class="col-md-4 text-center">
            <h5>Jak się zabezpieczyć?</h5>
            <p class="small text-muted">Blokuj konto po kilku próbach, używaj <b>CAPTCHA</b>, aby odróżnić ludzi od botów, i zawsze wprowadzaj <b>MFA</b> (Logowanie dwuetapowe).</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>