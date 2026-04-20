<?php
$conn = new mysqli("db", "root", "root_password", "lab_db");
$user_id = $_GET['id'] ?? 2;
$mode = $_GET['mode'] ?? 'vulnerable';

// Symulacja zalogowanego użytkownika (np. z sesji)
$logged_in_user_id = 2; 

$result = null;
$error = "";

// --- LOGIKA PODATNA ---
if ($mode == 'vulnerable') {
    // BŁĄD: Pobieramy dane DOWOLNEGO użytkownika, bo ufamy ID z adresu URL
    $query = "SELECT username, email, secret_info FROM users WHERE id = $user_id";
    $result = $conn->query($query);
}

// --- LOGIKA BEZPIECZNA ---
if ($mode == 'secure') {
    // POPRAWNIE: Sprawdzamy, czy żądane ID należy do zalogowanego użytkownika
    if ($user_id != $logged_in_user_id) {
        $error = "ODMOWA DOSTĘPU: Próbujesz wyświetlić profil o ID: $user_id, ale Twoje ID to $logged_in_user_id!";
    } else {
        $stmt = $conn->prepare("SELECT username, email, secret_info FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}

$user = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: IDOR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .url-bar { background: #e9ecef; padding: 8px 15px; border-radius: 20px; font-family: monospace; font-size: 0.9rem; border: 1px solid #ced4da; }
        .url-param { color: #dc3545; font-weight: bold; text-decoration: underline; }
        .secret-data { background: #fff5f5; border: 1px dashed #dc3545; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: IDOR</h1>
        <p class="lead text-muted">Insecure Direct Object Reference – manipulacja identyfikatorami w celu kradzieży danych.</p>
        <div class="btn-group shadow-sm">
            <a href="?id=<?= $user_id ?>&mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?id=<?= $user_id ?>&mode=secure" class="btn <?= $mode == 'secure' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4 text-center mb-4">
        <div class="col-12">
            <div class="alert alert-info d-inline-block shadow-sm">
                <i class="bi bi-person-check"></i> Status sesji: Jesteś zalogowany jako <b>Jan Kowalski (ID: 2)</b>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL APLIKACJI -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-shield-lock"></i> Widok Profilu
                </div>
                <div class="card-body">
                    <!-- SYMULACJA PASKA ADRESU -->
                    <div class="mb-4 text-start">
                        <small class="text-muted">Pasek adresu przeglądarki:</small>
                        <div class="url-bar">
                            https://twoja-strona.pl/profil.php?id=<span class="url-param"><?= htmlspecialchars($user_id) ?></span>&mode=<?= $mode ?>
                        </div>
                    </div>

                    <div class="text-start p-4 border rounded bg-white shadow-sm">
                        <?php if ($error): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-exclamation-octagon text-danger display-1"></i>
                                <h4 class="text-danger mt-3"><?= $error ?></h4>
                                <p class="text-muted">System wykrył próbę dostępu do nieautoryzowanych danych.</p>
                            </div>
                        <?php elseif ($user): ?>
                            <h4 class="mb-3">Informacje o profilu (ID: <?= htmlspecialchars($user_id) ?>)</h4>
                            <p><strong>Użytkownik:</strong> <?= htmlspecialchars($user['username']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            
                            <div class="secret-data mt-4">
                                <label class="text-danger fw-bold small"><i class="bi bi-eye-slash"></i> Dane prywatne (tylko dla właściciela):</label>
                                <p class="mb-0 text-dark"><?= htmlspecialchars($user['secret_info']) ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary">Nie znaleziono użytkownika o takim ID.</div>
                        <?php endif; ?>
                    </div>

                    <div class="mt-4">
                        <a href="?id=2&mode=<?= $mode ?>" class="btn btn-primary">Mój profil (ID: 2)</a>
                        <a href="?id=1&mode=<?= $mode ?>" class="btn btn-danger">Profil Admina (ID: 1)</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL ANALIZY -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="idorAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Co to jest IDOR?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            <strong>IDOR</strong> występuje, gdy aplikacja udostępnia zasób (profil, dokument, wiadomość) na podstawie identyfikatora (np. <code>id=123</code>) dostarczonego przez użytkownika i <strong>nie sprawdza</strong>, czy ten użytkownik ma prawo do tego zasobu.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Analiza podatności
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            W trybie podatnym serwer wykonuje zapytanie:<br>
                            <code>SELECT ... WHERE id = $_GET['id']</code><br><br>
                            Zauważ, że brakuje tu warunku: <br>
                            <code>AND user_id = $logged_in_user</code>.<br><br>
                            Dlatego zmieniając cyfrę w adresie URL, możesz przeglądać dane dowolnej osoby w systemie.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak się bronić?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ol>
                                <li><strong>Zawsze weryfikuj własność:</strong> Sprawdzaj, czy zalogowany użytkownik jest właścicielem żądanego ID.</li>
                                <li><strong>Używaj UUID:</strong> Zamiast prostych liczb (1, 2, 3), używaj długich, losowych identyfikatorów (np. <code>550e8400-e29b...</code>), których nie da się łatwo zgadnąć.</li>
                                <li><strong>Kontrola dostępu:</strong> Zastosuj centralny system uprawnień (ACL/RBAC).</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 shadow-sm border-warning">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="bi bi-lightbulb"></i> Zadanie dla ucznia:</h6>
                    <ul class="small ps-3 mb-0">
                        <li>Będąc w trybie podatnym, kliknij w "Profil Admina". Zauważ, że widzisz jego prywatne dane.</li>
                        <li>Spójrz na pasek adresu URL (symulację pod napisem "Widok Profilu").</li>
                        <li>Ręcznie zmień w przeglądarce <code>id=1</code> na <code>id=3</code>. Czy istnieje taki użytkownik?</li>
                        <li>Przełącz na tryb bezpieczny i zobacz, jak system reaguje na próbę "podglądania" Admina.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>