<?php
session_start();

// ==================== KONFIGURACJA LABU ====================
$max_attempts = 5;           
$lockout_time = 60;          
$correct_password = "Haslo123!";

$username = $_POST['username'] ?? 'janek123';
$message = '';
$mode = $_POST['action'] ?? $_GET['mode'] ?? 'vulnerable';

// LOGIKA POMOCNICZA: Resetowanie prób dla uczniów
if (isset($_GET['reset'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    unset($_SESSION['brute_' . $ip . '_' . $username]);
    header("Location: ?mode=safe");
    exit;
}

// --- TRYB PODATNY ---
if ($mode === 'vulnerable' && isset($_POST['password'])) {
    if ($_POST['password'] === $correct_password) {
        $message = "<div class='alert alert-success fw-bold'>✅ ZALOGOWANO! Witaj " . htmlspecialchars($username) . "</div>";
    } else {
        $message = "<div class='alert alert-danger'>❌ Błędne hasło. (Brak limitu prób)</div>";
    }
}

// --- TRYB BEZPIECZNY ---
if ($mode === 'safe' && isset($_POST['password'])) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $key = 'brute_' . $ip . '_' . $username;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['attempts' => 0, 'last_attempt' => time()];
    }

    $data = &$_SESSION[$key];

    // Sprawdzenie czy blokada jest aktywna
    if ($data['attempts'] >= $max_attempts && (time() - $data['last_attempt']) < $lockout_time) {
        $remaining = $lockout_time - (time() - $data['last_attempt']);
        $message = "<div class='alert alert-dark text-warning fw-bold'>⛔ BLOKADA! Spróbuj za $remaining sek.</div>";
    } else {
        // Reset po upływie czasu blokady
        if ((time() - $data['last_attempt']) >= $lockout_time) {
            $data['attempts'] = 0;
        }

        if ($_POST['password'] === $correct_password) {
            $message = "<div class='alert alert-success fw-bold'>✅ Zalogowano bezpiecznie!</div>";
            $data['attempts'] = 0; 
        } else {
            $data['attempts']++;
            $data['last_attempt'] = time();
            $left = $max_attempts - $data['attempts'];
            
            if ($data['attempts'] >= $max_attempts) {
                $message = "<div class='alert alert-danger fw-bold'>⛔ Konto zablokowane na $lockout_time sek.</div>";
            } else {
                $message = "<div class='alert alert-warning'>❌ Błędne hasło. Pozostało prób: $left</div>";
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
    <title>Laboratorium: Rate Limiting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .code-view { background: #1e1e1e; color: #fff; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; }
        .highlight { color: #f92672; font-weight: bold; }
        .secure-highlight { color: #a6e22e; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Rate Limiting</h1>
        <p class="lead text-muted">Zabezpieczenie przed atakami "siłowymi" (Brute Force).</p>
        <div class="btn-group shadow-sm">
            <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?mode=safe" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: DEMO -->
        <div class="col-lg-6">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-shield-lock"></i> Panel Logowania (<?= $mode == 'safe' ? 'Chroniony' : 'Niezabezpieczony' ?>)
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="<?= $mode ?>">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Login:</label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>">
                        </div>
                        <div class="mb-3">
                            <label class="small fw-bold text-muted">Hasło (Poprawne: Haslo123!):</label>
                            <input type="password" name="password" class="form-control" placeholder="Wpisz cokolwiek...">
                        </div>
                        <button type="submit" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-danger' ?> w-100 fw-bold">
                            ZALOGUJ SIĘ
                        </button>
                    </form>

                    <div class="mt-3 text-center">
                        <?= $message ?>
                        <?php if ($mode == 'safe' && strpos($message, '⛔') !== false): ?>
                            <a href="?reset=1" class="btn btn-link btn-sm">Zresetuj blokadę (tylko do testów)</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ANALIZA KODU -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white small">Co widzi serwer?</div>
                <div class="card-body">
                    <div class="code-view">
                        <?php if ($mode == 'vulnerable'): ?>
                            // Tryb: Podatny<br>
                            if ($password === $correct) {<br>
                            &nbsp;&nbsp;return "Witaj!";<br>
                            } else {<br>
                            &nbsp;&nbsp;<span class="highlight">// ERROR: Brak licznika prób!</span><br>
                            &nbsp;&nbsp;return "Błędne hasło";<br>
                            }
                        <?php else: ?>
                            // Tryb: Bezpieczny (Rate Limiting)<br>
                            $attempts = $_SESSION['attempts'];<br>
                            if ($attempts >= 5) {<br>
                            &nbsp;&nbsp;<span class="secure-highlight">die("Blokada na 60 sekund");</span><br>
                            }<br>
                            if ($password !== $correct) {<br>
                            &nbsp;&nbsp;<span class="secure-highlight">$attempts++;</span><br>
                            }
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: TEORIA -->
        <div class="col-lg-6">
            <div class="accordion shadow-sm" id="theoryAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Co to jest Rate Limiting?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            To mechanizm <strong>ograniczania liczby akcji</strong>, jakie użytkownik (lub bot) może wykonać w określonym czasie. W kontekście logowania jest to "bezpiecznik", który uniemożliwia hakerowi automatyczne sprawdzenie milionów haseł w minutę.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Dlaczego Brute Force działa?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            Atakujący używają narzędzi takich jak <b>Burp Suite</b> lub skryptów w Pythonie. Bez ochrony, serwer WWW przetwarza każde żądanie w ułamku sekundy. <br><br>
                            <strong>Matematyka ataku:</strong><br>
                            - Brak limitu: 1000 haseł/sek → 1 000 000 prób w 16 minut.<br>
                            - Rate Limiting (5 prób/min): 1 000 000 prób zajmie... <strong>38 lat!</strong>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak się zabezpieczyć w produkcji?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Blokada po IP + Login:</strong> Nie blokuj całego IP (bo wielu użytkowników może być za jednym NAT-em), ale kombinację IP+Login.</li>
                                <li><strong>Progressive Delay:</strong> Każda kolejna błędna próba wydłuża czas oczekiwania (np. 1s, 2s, 4s, 8s...).</li>
                                <li><strong>CAPTCHA:</strong> Wyświetlaj Google reCAPTCHA po 3 nieudanych próbach.</li>
                                <li><strong>MFA:</strong> Logowanie dwuetapowe (SMS/Kod) sprawia, że nawet poznanie hasła nie pozwala hakerowi wejść na konto.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="bi bi-lightbulb"></i> Zadanie dla ucznia:</h6>
                    <ol class="small ps-3 mb-0">
                        <li>W <b>Trybie podatnym</b> spróbuj "bombardować" przycisk logowania złym hasłem. Zauważ, że serwer nigdy nie mówi "dość".</li>
                        <li>Przełącz na <b>Tryb bezpieczny</b>. Wpisz błędne hasło 5 razy.</li>
                        <li>Zauważ, że po 5. próbie system odmawia posłuszeństwa i nakłada karę czasową.</li>
                        <li>Spójrz na kod: Zwróć uwagę, że w trybie bezpiecznym musimy pamiętać czas ostatniej próby (<code>last_attempt</code>), aby wiedzieć, kiedy zdjąć blokadę.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>