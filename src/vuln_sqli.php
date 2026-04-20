<?php
// Połączenie z bazą
$conn = new mysqli("db", "root", "root_password", "lab_db");

$search = $_GET['search'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';

// Symulacja tego, jak wygląda zapytanie SQL (dla celów edukacyjnych)
$simulated_sql = "";

if ($mode == 'vulnerable') {
    $simulated_sql = "SELECT username, email FROM users WHERE username = '$search'";
    if ($search != '') {
        $result = $conn->query($simulated_sql);
    }
} else {
    $simulated_sql = "SELECT username, email FROM users WHERE username = ?";
    if ($search != '') {
        $stmt = $conn->prepare($simulated_sql);
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Laboratorium SQL Injection</title>
    <style>
        .sql-box { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; }
        .highlight { color: #f92672; font-weight: bold; }
        .secure-mode { border-left: 5px solid #198754; }
        .vulnerable-mode { border-left: 5px solid #dc3545; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="display-5">Laboratorium: SQL Injection (SQLi)</h1>
            <p class="lead">Zrozum, jak błędy w kodzie pozwalają przejąć kontrolę nad bazą danych.</p>
            <hr>
        </div>
    </div>

    <div class="row">
        <!-- LEWA KOLUMNA: PANEL INTERAKTYWNY -->
        <div class="col-lg-6">
            <div class="card shadow-sm mb-4 <?= $mode == 'secure' ? 'secure-mode' : 'vulnerable-mode' ?>">
                <div class="card-body">
                    <h5 class="card-title">Wyszukiwarka użytkowników</h5>
                    <form method="GET" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Wpisz login..." value="<?= htmlspecialchars($search) ?>">
                            <button name="mode" value="vulnerable" class="btn btn-danger">Tryb podatny</button>
                            <button name="mode" value="secure" class="btn btn-success">Tryb bezpieczny</button>
                        </div>
                    </form>

                    <h6>Zapytanie wysłane do bazy:</h6>
                    <div class="sql-box mb-3">
                        <?php 
                        if ($mode == 'vulnerable') {
                            // Kolorowanie wstrzykniętego kodu dla celów edukacyjnych
                            $display_sql = str_replace($search, "<span class='highlight'>$search</span>", $simulated_sql);
                            echo $display_sql;
                        } else {
                            echo htmlspecialchars($simulated_sql) . " <span class='text-muted'>(Parametr: $search)</span>";
                        }
                        ?>
                    </div>

                    <h6>Wyniki z bazy:</h6>
                    <table class="table table-bordered table-hover bg-white">
                        <thead class="table-dark">
                            <tr><th>Username</th><th>Email / Secret</th></tr>
                        </thead>
                        <tbody>
                            <?php if (isset($result) && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['email'] ?? $row['secret_info'] ?? 'brak danych') ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php elseif ($search != ''): ?>
                                <tr><td colspan="2" class="text-center">Brak wyników</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- INSTRUKCJA DLA UCZNIA -->
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">Zadania do wykonania</div>
                <div class="card-body">
                    <ol>
                        <li><strong>Ominięcie filtrów:</strong> W trybie podatnym wpisz <code>' OR 1=1 -- </code>. Zauważ, że zobaczysz wszystkich użytkowników, bo warunek 1=1 jest zawsze prawdziwy.</li>
                        <li class="mt-2"><strong>Wyciąganie ukrytych danych:</strong> Wpisz <code>' UNION SELECT username, secret_info FROM users -- </code>. Dzięki temu "dokleisz" do wyników dane z innej kolumny, których normalnie nie powinieneś widzieć.</li>
                        <li class="mt-2"><strong>Test bezpieczeństwa:</strong> Spróbuj tych samych haseł w "Trybie bezpiecznym". Dlaczego tym razem nie działają?</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: TEORIA -->
        <div class="col-lg-6">
            <div class="accordion" id="theoryAccordion">
                
                <!-- CO TO JEST -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                            Czym jest SQL Injection?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <p>To atak polegający na <strong>zmianie struktury zapytania SQL</strong> poprzez przemycenie komend w polach formularza. Jeśli aplikacja ufa użytkownikowi i bezpośrednio wstawia jego tekst do zapytania, haker może dopisać własne instrukcje.</p>
                            <span class="badge bg-danger">Skutki:</span> Wyciek haseł, usunięcie bazy danych, logowanie bez hasła.
                        </div>
                    </div>
                </div>

                <!-- ANALIZA KODU -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                            Analiza kodu: Dlaczego to jest podatne?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <p>W trybie podatnym używamy <strong>konkatenacji stringów</strong>:</p>
                            <pre class="bg-light p-2"><code>$sql = "... WHERE user = '$search'";</code></pre>
                            <p>Jeśli wpiszesz <code>' OR '1'='1</code>, zmienna <code>$search</code> zamknie pierwszy cudzysłów i otworzy nową logikę. Programista myślał, że <code>$search</code> to tylko tekst, a stał się on częścią kodu.</p>
                        </div>
                    </div>
                </div>

                <!-- JAK ZABEZPIECZYĆ -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                            Jak się bronić? (Prepared Statements)
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse">
                        <div class="accordion-body">
                            <p>Najlepszą metodą są <strong>Zapytania Przygotowane (Prepared Statements)</strong>.</p>
                            <ol>
                                <li>Najpierw wysyłamy do bazy sam szkielet zapytania: <code>WHERE user = ?</code>.</li>
                                <li>Baza "kompiluje" to zapytanie i wie, że w miejsce <code>?</code> trafi tylko czysty tekst.</li>
                                <li>Dopiero potem dosyłamy dane użytkownika.</li>
                            </ol>
                            <p>Nawet jeśli dane zawierają komendy SQL, zostaną potraktowane jak zwykły napis (string).</p>
                            <pre class="bg-light p-2"><code>$stmt = $conn->prepare("SELECT...");
$stmt->bind_param("s", $search); // "s" oznacza string</code></pre>
                        </div>
                    </div>
                </div>

            </div> <!-- koniec accordion -->
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>