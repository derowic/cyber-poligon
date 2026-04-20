<?php
session_start();

// Symulacja zalogowanego admina
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = "admin@szkola.pl";
}

// Inicjalizacja tokena CSRF (tylko raz na sesję)
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mode = $_GET['mode'] ?? 'vulnerable';
$message = "";
$error = "";

// Obsługa zmiany emaila (Akcja na serwerze)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_email'])) {
    
    if ($mode === 'secure') {
        // WERYFIKACJA TOKENA
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['csrf_token']) {
            $error = "ATAK CSRF ZABLOKOWANY: Nieprawidłowy lub brakujący token bezpieczeństwa!";
        } else {
            $_SESSION['email'] = $_POST['new_email'];
            $message = "Sukces (Tryb bezpieczny): Email zmieniony na " . htmlspecialchars($_SESSION['email']);
        }
    } else {
        // TRYB PODATNY - brak sprawdzenia tokena
        $_SESSION['email'] = $_POST['new_email'];
        $message = "Sukces (Tryb podatny): Email zmieniony na " . htmlspecialchars($_SESSION['email']);
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: CSRF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .attacker-card { background-color: #fff3f3; border: 2px dashed #dc3545; }
        .token-view { font-family: monospace; background: #e9ecef; padding: 5px; border-radius: 4px; font-size: 0.85rem; word-break: break-all; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: CSRF</h1>
        <p class="lead text-muted">Cross-Site Request Forgery – czyli jak zmusić użytkownika do akcji, której nie planował.</p>
        <div class="btn-group shadow-sm">
            <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?mode=secure" class="btn <?= $mode == 'secure' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: TWOJA APLIKACJA -->
        <div class="col-lg-6">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-person-circle"></i> Panel Ustawień Użytkownika
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2">
                        <small>Jesteś zalogowany jako:</small><br>
                        <strong><?= $_SESSION['email'] ?></strong>
                    </div>

                    <?php if ($message): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= $message ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-shield-slash"></i> <?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" action="?mode=<?= $mode ?>" class="p-3 border rounded">
                        <h6>Zmień adres e-mail:</h6>
                        <div class="mb-3">
                            <input type="email" name="new_email" class="form-control" placeholder="nowy@email.pl" required>
                        </div>
                        
                        <?php if ($mode == 'secure'): ?>
                            <div class="mb-3">
                                <small class="text-muted">Token CSRF (Ukryte pole):</small>
                                <div class="token-view"><?= $_SESSION['csrf_token'] ?></div>
                                <input type="hidden" name="token" value="<?= $_SESSION['csrf_token'] ?>">
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary w-100">Zapisz ustawienia</button>
                    </form>

                    <div class="mt-4 p-3 bg-light rounded">
                        <h6><i class="bi bi-info-square"></i> Jak to działa?</h6>
                        <p class="small text-muted">Przeglądarka do każdego żądania wysyłanego do tej strony automatycznie dołącza Twoje <b>Ciasteczko Sesyjne</b>. Serwer dzięki temu "wie", że to Ty.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: STRONA ATAKUJĄCEGO -->
        <div class="col-lg-6">
            <div class="card shadow h-100 attacker-card">
                <div class="card-header bg-danger text-white fw-bold">
                    <i class="bi bi-incognito"></i> Strona Atakującego (hacker.com)
                </div>
                <div class="card-body">
                    <p>Haker przygotował stronę, która udaje niewinną treść (np. "Wygrałeś nagrodę!").</p>
                    
                    <div class="text-center p-4 bg-white border rounded shadow-sm mb-4">
                        <h4 class="text-primary">🎁 GRATULACJE!</h4>
                        <p>Kliknij poniżej, aby odebrać darmowy dostęp do Netflix!</p>
                        
                        <!-- FORMULARZ ATAKU -->
                        <form action="?mode=<?= $mode ?>" method="POST" target="_self">
                            <input type="hidden" name="new_email" value="hacker@przejalem_konto.pl">
                            <button type="submit" class="btn btn-warning btn-lg shadow">
                                ODBIERZ NAGRODĘ
                            </button>
                        </form>
                    </div>

                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Analiza ataku:</strong> Ten żółty przycisk to tak naprawdę formularz, który wysyła żądanie <code>POST</code> do Twojej aplikacji. Ponieważ Twoja przeglądarka "pamięta" sesję, serwer uzna to żądanie za autentyczne (w trybie podatnym).
                    </div>

                    <hr>
                    <h6>Kod ataku (ukryty na stronie hakera):</h6>
                    <pre class="bg-dark text-white p-2 small rounded"><code>&lt;form action="twoja-strona.pl/zmien-email" method="POST"&gt;
  &lt;input type="hidden" name="new_email" value="hacker@mail.pl"&gt;
&lt;/form&gt;
&lt;script&gt;document.forms[0].submit();&lt;/script&gt;</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- SEKCJA EDUKACYJNA -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card border-dark">
                <div class="card-header bg-dark text-white">Analiza Dydaktyczna</div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h5><i class="bi bi-question-diamond text-danger"></i> Problem</h5>
                            <p class="small">Przeglądarka wysyła ciasteczka sesyjne do <code>twoja-strona.pl</code> niezależnie od tego, czy żądanie pochodzi z Twojej strony, czy ze złośliwej strony <code>hacker.com</code>.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="bi bi-key text-success"></i> Rozwiązanie</h5>
                            <p class="small">Do każdego formularza dodajemy unikalny, trudny do odgadnięcia <b>Token CSRF</b>. Haker nie zna tego tokena, więc nie może go dołączyć do swojego złośliwego formularza.</p>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="bi bi-clipboard-check text-primary"></i> Zadanie</h5>
                            <ol class="small text-start">
                                <li>W trybie <b>podatnym</b> kliknij żółty przycisk "Odbierz nagrodę". Zauważ, że Twój email w panelu po lewej się zmienił!</li>
                                <li>Przełącz na tryb <b>bezpieczny</b> i spróbuj ponownie. Zobaczysz błąd CSRF.</li>
                            </ol>
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