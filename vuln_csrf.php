<?php
session_start();
// Symulacja zalogowanego admina
if (!isset($_SESSION['email'])) {
    $_SESSION['email'] = "admin@szkola.pl";
}

$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'vulnerable';
$message = "";

// Akcja zmiany e-maila
if (isset($_POST['new_email'])) {
    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['csrf_token']) {
            die("<div class='alert alert-danger'>BŁĄD CSRF: Nieprawidłowy token! Atak zablokowany.</div>");
        }
    }
    
    $_SESSION['email'] = $_POST['new_email'];
    $message = "Adres e-mail został pomyślnie zmieniony na: " . $_SESSION['email'];
}

// Generowanie tokena dla trybu bezpiecznego
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>CSRF Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 09: Cross-Site Request Forgery (CSRF)</h2>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card p-4 shadow">
                <h5>Ustawienia Konta</h5>
                <p>Twój aktualny email: <strong><?php echo $_SESSION['email']; ?></strong></p>
                <hr>
                <form method="POST">
                    <label>Zmień email:</label>
                    <input type="email" name="new_email" class="form-control mb-3" placeholder="nowy@email.pl">
                    
                    <?php if ($mode == 'secure'): ?>
                        <input type="hidden" name="token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="mode" value="secure">
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                </form>
                <?php if ($message): ?>
                    <div class="alert alert-success mt-3"><?php echo $message; ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4 border-danger bg-white">
                <h5 class="text-danger">Symulacja ataku (Strona Hackera)</h5>
                <p>Wyobraź sobie, że haker wysyła Ci ten link. Kliknięcie w przycisk poniżej symuluje wejście na złośliwą stronę <code>hacker.com</code>.</p>
                
                <!-- Ten formularz udaje zewnętrzną stronę, która atakuje naszą aplikację -->
                <form id="attackerForm" action="vuln_csrf.php" method="POST" target="_blank">
                    <input type="hidden" name="new_email" value="hacker@przejalem_twoje_konto.pl">
                    <button type="submit" class="btn btn-danger w-100">
                        KLIKNIJ MNIE (Złośliwy link w e-mailu)
                    </button>
                </form>
                <p class="small mt-2 text-muted text-center">Formularz wyśle żądanie zmiany maila bez Twojej wiedzy!</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>