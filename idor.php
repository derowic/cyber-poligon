<?php
session_start();

// Symulacja bazy danych użytkowników (w pamięci)
$users = [
    1 => ['id' => 1, 'username' => 'admin',     'email' => 'admin@przyklad.pl',     'role' => 'administrator', 'balance' => 9999],
    2 => ['id' => 2, 'username' => 'janek123',  'email' => 'janek@przyklad.pl',     'role' => 'user',          'balance' => 150],
    3 => ['id' => 3, 'username' => 'ania456',   'email' => 'ania@przyklad.pl',      'role' => 'user',          'balance' => 850],
    4 => ['id' => 4, 'username' => 'testuser',  'email' => 'test@przyklad.pl',     'role' => 'user',          'balance' => 320]
];

// Aktualnie "zalogowany" użytkownik (dla demo przyjmujemy użytkownika o ID 2)
$current_user_id = 2;
$current_user = $users[$current_user_id];

// Pobranie ID z URL lub formularza
$requested_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

$viewed_user = $users[$requested_id] ?? null;

$message = '';
$action = $_GET['action'] ?? '';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo IDOR - Insecure Direct Object Reference</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; background: #fff3f3; }
        .safe-card { border-left: 6px solid #28a745; background: #f0fff0; }
        .highlight { font-weight: bold; color: #dc3545; background: #fff3cd; padding: 2px 6px; border-radius: 4px; }
        .safe-highlight { font-weight: bold; color: #28a745; background: #d4edda; padding: 2px 6px; border-radius: 4px; }
        .user-card { background: white; border: 1px solid #ddd; border-radius: 8px; padding: 15px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Demonstracja IDOR (Insecure Direct Object Reference)</h1>
        <p class="lead text-muted">Lewa strona = podatna • Prawa strona = zabezpieczona (sprawdzenie uprawnień)</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">← SQL Injection</a>
            <a href="xss.php" class="btn btn-outline-secondary">← XSS</a>
            <a href="csrf.php" class="btn btn-outline-secondary">← CSRF</a>
        </div>
    </div>

    <div class="row g-5">
        <!-- ==================== WERSJA PODATNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow vuln-card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-bug"></i> Wersja podatna na IDOR</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Aplikacja pozwala zmienić ID w adresie URL i zobaczyć dane innego użytkownika.</p>

                    <div class="mb-4">
                        <strong>Twój aktualny profil:</strong> <?= htmlspecialchars($current_user['username']) ?> (ID: <?= $current_user_id ?>)
                    </div>

                    <form method="GET" class="input-group mb-3">
                        <input type="hidden" name="action" value="view">
                        <input type="number" name="id" class="form-control" placeholder="Podaj ID użytkownika" value="<?= $requested_id ?>">
                        <button class="btn btn-danger">Pokaż profil (podatnie)</button>
                    </form>

                    <?php if ($viewed_user): ?>
                        <div class="user-card">
                            <h5>Profil użytkownika ID: <?= $viewed_user['id'] ?></h5>
                            <p><strong>Login:</strong> <?= htmlspecialchars($viewed_user['username']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($viewed_user['email']) ?></p>
                            <p><strong>Rola:</strong> <span class="badge bg-warning"><?= htmlspecialchars($viewed_user['role']) ?></span></p>
                            <p><strong>Saldo konta:</strong> <?= $viewed_user['balance'] ?> zł</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">Użytkownik o podanym ID nie istnieje.</div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (podatny):</h6>
                    <pre class="bg-white p-3 border"><code>$requested_id = (int)$_GET['id'];

// <span class="highlight">Brak sprawdzenia czy to dane aktualnego użytkownika!</span>
$viewed_user = $users[$requested_id] ?? null;</code></pre>

                    <small class="text-danger">
                        Spróbuj zmienić <code>?id=1</code> lub <code>?id=3</code> w adresie URL
                    </small>
                </div>
            </div>
        </div>

        <!-- ==================== WERSJA BEZPIECZNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow safe-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Wersja zabezpieczona</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Serwer zawsze sprawdza, czy użytkownik ma prawo zobaczyć/edytować te dane.</p>

                    <div class="mb-4">
                        <strong>Twój aktualny profil:</strong> <?= htmlspecialchars($current_user['username']) ?> (ID: <?= $current_user_id ?>)
                    </div>

                    <form method="GET" class="input-group mb-3">
                        <input type="hidden" name="action" value="view">
                        <input type="number" name="id" class="form-control" placeholder="Podaj ID użytkownika" value="<?= $requested_id ?>">
                        <button class="btn btn-success">Pokaż profil (bezpiecznie)</button>
                    </form>

                    <?php
                    $safe_viewed_user = null;
                    if ($requested_id === $current_user_id || $current_user['role'] === 'administrator') {
                        $safe_viewed_user = $users[$requested_id] ?? null;
                    }
                    ?>

                    <?php if ($safe_viewed_user): ?>
                        <div class="user-card">
                            <h5>Profil użytkownika ID: <?= $safe_viewed_user['id'] ?></h5>
                            <p><strong>Login:</strong> <?= htmlspecialchars($safe_viewed_user['username']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($safe_viewed_user['email']) ?></p>
                            <p><strong>Rola:</strong> <span class="badge bg-success"><?= htmlspecialchars($safe_viewed_user['role']) ?></span></p>
                            <p><strong>Saldo konta:</strong> <?= $safe_viewed_user['balance'] ?> zł</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger">
                            Nie masz uprawnień do przeglądania profilu użytkownika ID <?= $requested_id ?>.
                        </div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (bezpieczny):</h6>
                    <pre class="bg-white p-3 border"><code>$requested_id = (int)$_GET['id'];

// <span class="safe-highlight">Zabezpieczenie – zawsze sprawdzamy uprawnienia</span>
if ($requested_id === $current_user_id || $current_user['role'] === 'administrator') {
    $viewed_user = $users[$requested_id] ?? null;
} else {
    // dostęp zabroniony
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-5">
        <strong>Jak testować atak IDOR?</strong><br>
        Na lewej stronie (podatnej) zmień parametr <code>id=</code> w pasku adresu na inny numer (np. <code>?id=1</code>).  
        Powinieneś zobaczyć dane administratora lub innych użytkowników, do których nie powinieneś mieć dostępu.
    </div>

    <div class="text-center mt-5 small text-muted">
        Demo IDOR • PHP 8.3 + Bootstrap 5<br>
        <a href="index.php">← SQL Injection</a> | 
        <a href="xss.php">← XSS</a> | 
        <a href="csrf.php">← CSRF</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>