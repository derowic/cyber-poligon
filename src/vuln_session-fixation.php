<?php
session_start();

// --- OBSŁUGA LOGOWANIA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === 'Haslo123!') {
        $_SESSION['user'] = $_POST['username'];

        // KLUCZOWY MOMENT DLA BEZPIECZEŃSTWA
        if ($_POST['action'] === 'login_safe') {
            session_regenerate_id(true);   // To zabezpieczenie niszczy stary ID i tworzy nowy
        }
        header("Location: ?mode=" . ($_POST['action'] === 'login_safe' ? 'safe' : 'vulnerable'));
        exit;
    }
}

// --- LOGIKA POMOCNICZA ---
$logged_in = isset($_SESSION['user']);
$username = $_SESSION['user'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';

// Reset sesji (do testów dla uczniów)
if (isset($_GET['reset'])) {
    session_destroy();
    session_start();
    header("Location: ?mode=$mode");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Session Fixation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .session-info { background: #272822; color: #a6e22e; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9rem; border: 1px solid #444; }
        .highlight { color: #f92672; font-weight: bold; }
        .secure-highlight { color: #a6e22e; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Session Fixation</h1>
        <p class="lead text-muted">Przejęcie konta poprzez narzucenie ofierze identyfikatora sesji.</p>
        <div class="btn-group shadow-sm">
            <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?mode=safe" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: TWOJA PRZEGLĄDARKA -->
        <div class="col-lg-6">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-browser-chrome"></i> Status Twojej Sesji
                </div>
                <div class="card-body">
                    <div class="session-info mb-4">
                        <small class="text-muted text-uppercase">Aktualny PHPSESSID:</small><br>
                        <span class="<?= $logged_in ? 'text-warning' : 'text-success' ?>"><?= session_id() ?></span>
                    </div>

                    <?php if (!$logged_in): ?>
                        <div class="p-4 bg-white border rounded shadow-sm">
                            <h6 class="mb-3">Zaloguj się do systemu:</h6>
                            <form method="POST">
                                <input type="hidden" name="action" value="<?= $mode === 'safe' ? 'login_safe' : 'login_vuln' ?>">
                                <div class="mb-2">
                                    <input type="text" name="username" value="janek123" class="form-control" readonly>
                                </div>
                                <div class="mb-3">
                                    <input type="password" name="password" placeholder="Hasło (Haslo123!)" class="form-control">
                                </div>
                                <button type="submit" class="btn <?= $mode === 'safe' ? 'btn-success' : 'btn-danger' ?> w-100 fw-bold">
                                    ZALOGUJ SIĘ
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success text-center border-2">
                            <i class="bi bi-check-circle-fill"></i> Zalogowano jako <strong><?= htmlspecialchars($username) ?></strong>!
                        </div>
                        <div class="text-center">
                            <a href="?reset=1&mode=<?= $mode ?>" class="btn btn-outline-secondary btn-sm">Wyloguj i zresetuj sesję</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ANALIZA KODU -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white small">Co dzieje się po stronie serwera?</div>
                <div class="card-body">
                    <pre class="mb-0" style="color: #61dafb; font-size: 0.85rem;"><code>if (haslo_poprawne) {
  $_SESSION['user'] = $username;
  <?php if ($mode == 'vulnerable'): ?>
  <span class="highlight">// Luka: Serwer kontynuuje używanie 
  // starego ID sesji, które nadał haker!</span>
  <?php else: ?>
  <span class="secure-highlight">session_regenerate_id(true); 
  // Bezpiecznie: Stary ID sesji zostaje 
  // zniszczony, generujemy nowy!</span>
  <?php endif; ?>
}</code></pre>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA DLA UCZNIA -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white"><i class="bi bi-info-circle"></i> Jak wykonać ten atak?</div>
                <div class="card-body small">
                    <p>Aby przeprowadzić atak Session Fixation, haker musi "narzucić" Ci swój numer sesji.</p>
                    <ol>
                        <li>Otwórz to laboratorium w <b>drugiej przeglądarce</b> (lub trybie Incognito). To będzie "Przeglądarka Hakera".</li>
                        <li>Skopiuj <b>PHPSESSID</b> z Przeglądarki Hakera.</li>
                        <li>Wróć tutaj (Przeglądarka Ofiary) i użyj DevTools (F12) → Application → Cookies, aby <b>podmienić</b> swoje PHPSESSID na to od hakera.</li>
                        <li>Teraz <b>zaloguj się</b> jako Ofiara w trybie podatnym.</li>
                        <li>Odśwież stronę w Przeglądarce Hakera. Jeśli zobaczysz napis "Zalogowano", oznacza to, że haker przejął Twoje konto, bo ID sesji się nie zmieniło!</li>
                    </ol>
                </div>
            </div>

            <div class="accordion shadow-sm" id="theoryAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Dlaczego to działa? (Teoria)
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <p>Większość serwerów nadaje ID sesji każdemu, kto wejdzie na stronę (nawet niezalogowanemu). W ataku <b>Session Fixation</b> haker wchodzi na stronę, pobiera ID sesji, a następnie wysyła ofierze link z tym samym ID (lub podrzuca je w inny sposób).</p>
                            <p>Jeśli serwer nie wygeneruje nowego ID po zalogowaniu, haker (który zna to stare ID) ma teraz "otwartą bramę" do konta ofiary.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Jak się zabezpieczyć?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Regeneracja ID:</strong> Zawsze używaj <code>session_regenerate_id(true)</code> natychmiast po poprawnej weryfikacji hasła.</li>
                                <li><strong>Strict Mode:</strong> W ustawieniach PHP włącz <code>session.use_strict_mode = 1</code> (serwer nie będzie akceptował ID sesji, których sam nie stworzył).</li>
                                <li><strong>Atrybuty Cookie:</strong> Używaj <code>HttpOnly</code> (blokada odczytu przez JS) oraz <code>Secure</code> (tylko HTTPS).</li>
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