<?php
session_start();
$message = "";
$mode = $_POST['mode'] ?? 'vulnerable';

// Dane testowe (w rzeczywistości byłyby w bazie)
$valid_user = "admin";
$valid_pass = "admin123"; // Bardzo słabe hasło

if (isset($_POST['login'])) {
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        // Brak opóźnienia, brak blokady konta - idealne pod Brute Force
        if ($user === $valid_user && $pass === $valid_pass) {
            $message = "<div class='alert alert-success'>Zalogowano jako Administrator!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Błędne hasło!</div>";
        }
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // Symulacja blokady czasowej (Rate Limiting)
        if (isset($_SESSION['attempts']) && $_SESSION['attempts'] >= 3) {
            $message = "<div class='alert alert-warning'>Zbyt wiele prób! Odczekaj 30 sekund.</div>";
        } else {
            if ($user === $valid_user && $pass === $valid_pass) {
                $_SESSION['attempts'] = 0;
                $message = "<div class='alert alert-success'>Zalogowano bezpiecznie!</div>";
            } else {
                $_SESSION['attempts'] = ($_SESSION['attempts'] ?? 0) + 1;
                $message = "<div class='alert alert-danger'>Błędne hasło! Próba: " . $_SESSION['attempts'] . "/3</div>";
                sleep(1); // Sztuczne spowolnienie serwera (anty-automat)
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Brute Force Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 06: Brute Force & Authentication</h2>
    
    <div class="row">
        <div class="col-md-5">
            <div class="card p-4 shadow">
                <h5>Panel Logowania</h5>
                <form method="POST">
                    <input type="text" name="user" class="form-control mb-2" placeholder="Użytkownik" required>
                    <input type="password" name="pass" class="form-control mb-3" placeholder="Hasło" required>
                    <button type="submit" name="login" value="vulnerable" class="btn btn-danger w-100 mb-2">Loguj (Podatny)</button>
                    <button type="submit" name="login" value="secure" class="btn btn-success w-100">Loguj (Bezpieczny)</button>
                </form>
                <div class="mt-3"><?php echo $message; ?></div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="alert alert-info border-0 shadow-sm">
                <h5>Misja: Złam hasło admina</h5>
                <p>Użytkownik to: <b>admin</b></p>
                <p>Masz listę najpopularniejszych haseł. Nie wpisuj ich ręcznie! Użyj narzędzia.</p>
                <ul>
                    <li>Użyj <b>Burp Suite -> Intruder</b></li>
                    <li>Ustaw atak na parametr <code>pass</code>.</li>
                    <li>Załaduj listę (np. 123456, password, admin123, qwerty).</li>
                    <li>Uruchom atak i znajdź hasło (sprawdzając <b>Length</b> odpowiedzi).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
</body>
</html>