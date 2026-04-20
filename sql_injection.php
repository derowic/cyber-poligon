<?php
$conn = new mysqli("db", "root", "root_password", "lab_db");
if ($conn->connect_error) {
    die("Błąd połączenia z bazą: " . $conn->connect_error);
}

$vulnerable_results = null;
$secure_results = null;
$search_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_input = $_POST['search'] ?? '';

    if (isset($_POST['action']) && $_POST['action'] === 'vulnerable') {
        // === WERSJA PODATNA ===
        $sql = "SELECT * FROM users WHERE username LIKE '%$search_input%'";
        $result = $conn->query($sql);
        $vulnerable_results = $result ? $result->fetch_all(MYSQLI_ASSOC) : ['error' => $conn->error];
    }

    if (isset($_POST['action']) && $_POST['action'] === 'secure') {
        // === WERSJA BEZPIECZNA ===
        $stmt = $conn->prepare("SELECT * FROM users WHERE username LIKE ?");
        $param = "%$search_input%";
        $stmt->bind_param("s", $param);
        $stmt->execute();
        $result = $stmt->get_result();
        $secure_results = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo SQL Injection • PHP + Bootstrap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-code { background: #fff3f3; border-left: 5px solid #dc3545; }
        .secure-code { background: #f0fff0; border-left: 5px solid #28a745; }
        .highlight { font-weight: bold; color: #dc3545; background: yellow; padding: 2px 4px; border-radius: 3px; }
        .safe-highlight { font-weight: bold; color: #28a745; background: #d4edda; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Demonstracja ataku SQL Injection</h1>
        <p class="lead">Lewa strona = podatna • Prawa strona = bezpieczna (prepared statements)</p>
    </div>

    <div class="row g-5">
        <!-- ==================== LEWA KOLUMNA - PODATNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-bug"></i> Wersja podatna na SQL Injection</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Zapytanie budowane przez konkatenację stringa – klasyczna luka.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="vulnerable">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Wpisz nazwę użytkownika" value="<?= htmlspecialchars($search_input) ?>">
                            <button class="btn btn-danger">Szukaj (podatnie)</button>
                        </div>
                    </form>

                    <?php if ($vulnerable_results !== null): ?>
                        <?php if (isset($vulnerable_results['error'])): ?>
                            <div class="alert alert-danger">Błąd zapytania: <?= htmlspecialchars($vulnerable_results['error']) ?></div>
                        <?php else: ?>
                            <h6>Wyniki (<?= count($vulnerable_results) ?>):</h6>
                            <?php if (empty($vulnerable_results)): ?>
                                <p class="text-muted">Brak wyników</p>
                            <?php else: ?>
                                <table class="table table-sm table-hover">
                                    <thead><tr><th>ID</th><th>Username</th><th>Email</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($vulnerable_results as $row): ?>
                                        <tr><td><?= $row['id'] ?></td><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['email']) ?></td></tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Kod podatny -->
                    <h6 class="mt-4">Kod źródłowy (wersja podatna):</h6>
                    <pre class="vuln-code p-3"><code>&lt;?php
$search = $_POST['search'] ?? '';

// <span class="highlight">Luka tutaj – konkatenacja inputu użytkownika bezpośrednio do zapytania</span>
$sql = "SELECT * FROM users WHERE username LIKE '%<span class="highlight">$search</span>%'";
$result = $conn-&gt;query($sql);
?&gt;</code></pre>
                    <small class="text-danger">Atak: wpisz <code>' OR '1'='1</code> lub <code>' OR '1'='1 -- </code></small>
                </div>
            </div>
        </div>

        <!-- ==================== PRAWA KOLUMNA - BEZPIECZNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Wersja zabezpieczona</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Używa <strong>prepared statements + bind_param</strong> – parametry są traktowane jako dane, a nie kod SQL.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="secure">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Wpisz nazwę użytkownika" value="<?= htmlspecialchars($search_input) ?>">
                            <button class="btn btn-success">Szukaj (bezpiecznie)</button>
                        </div>
                    </form>

                    <?php if ($secure_results !== null): ?>
                        <h6>Wyniki (<?= count($secure_results) ?>):</h6>
                        <?php if (empty($secure_results)): ?>
                            <p class="text-muted">Brak wyników</p>
                        <?php else: ?>
                            <table class="table table-sm table-hover">
                                <thead><tr><th>ID</th><th>Username</th><th>Email</th></tr></thead>
                                <tbody>
                                <?php foreach ($secure_results as $row): ?>
                                    <tr><td><?= $row['id'] ?></td><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['email']) ?></td></tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Kod bezpieczny -->
                    <h6 class="mt-4">Kod źródłowy (wersja bezpieczna):</h6>
                    <pre class="secure-code p-3"><code>&lt;?php
$search = $_POST['search'] ?? '';

// <span class="safe-highlight">Zabezpieczenie: prepared statement + bind_param</span>
$stmt = $conn-&gt;prepare("SELECT * FROM users WHERE username LIKE ?");
$param = "%" . $search . "%";
$stmt-&gt;bind_param("s", $param);
$stmt-&gt;execute();
$result = $stmt-&gt;get_result();
?&gt;</code></pre>
                    <small class="text-success">Nawet wpisanie <code>' OR '1'='1</code> nie zadziała – jest traktowane jako zwykły tekst.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 text-muted small">
        Projekt uruchomiony w Dockerze • PHP 8.3 + MySQL 8 • Bootstrap 5<br>
        <strong>Jak uruchomić?</strong> W folderze projektu wpisz: <code>docker compose up -d</code><br>
        Otwórz przeglądarkę: <a href="http://localhost:8080" target="_blank">http://localhost:8080</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>