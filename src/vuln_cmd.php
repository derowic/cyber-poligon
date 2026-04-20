<?php
$output = "";
$mode = $_POST['mode'] ?? 'vulnerable';
$ip = $_POST['ip'] ?? '';
$simulated_command = "";

if (isset($_POST['ip']) && $_POST['ip'] !== '') {
    $target = $_POST['ip'];

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        $simulated_command = "ping -c 3 " . $target;
        // KATASTROFA: Bezpośrednie przekazanie zmiennej do powłoki systemowej
        $output = shell_exec($simulated_command);
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // 1. Walidacja: Czy to na pewno adres IP?
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            // 2. Escaping: Nawet jeśli walidacja zawiedzie, escapeshellarg uniemożliwi wyjście z komendy
            $safe_target = escapeshellarg($target);
            $simulated_command = "ping -c 3 " . $safe_target;
            $output = shell_exec($simulated_command);
        } else {
            $simulated_command = "ping -c 3 [ZABLOKOWANO]";
            $output = "BŁĄD: Wprowadzono nieprawidłowy adres IP. System zabezpieczeń zatrzymał wykonanie.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: OS Command Injection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .console { background: #272822; color: #a6e22e; padding: 15px; border-radius: 5px; font-family: 'Courier New', Courier, monospace; min-height: 100px; white-space: pre-wrap; }
        .command-view { background: #1e1e1e; color: #ffffff; padding: 10px; border-radius: 4px; font-family: monospace; border: 1px solid #444; }
        .highlight { color: #f92672; font-weight: bold; text-decoration: underline; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: OS Command Injection</h1>
        <p class="lead text-muted">Przejmowanie kontroli nad serwerem poprzez wstrzykiwanie komend systemowych.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL TESTOWY -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Sieciowe narzędzie diagnostyczne</h5>
                    
                    <form method="POST" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white">ping -c 3</span>
                            <input type="text" name="ip" class="form-control" placeholder="Wpisz IP (np. 8.8.8.8)" value="<?= htmlspecialchars($ip) ?>">
                            <button name="mode" value="vulnerable" class="btn btn-danger">Uruchom (Podatny)</button>
                            <button name="mode" value="secure" class="btn btn-success">Uruchom (Bezpieczny)</button>
                        </div>
                    </form>

                    <h6>Pełna komenda wykonana na serwerze:</h6>
                    <div class="command-view mb-4">
                        <?php 
                        if ($simulated_command) {
                            $display = htmlspecialchars($simulated_command);
                            if ($mode == 'vulnerable' && $ip !== '') {
                                // Podświetlamy to, co wpisał użytkownik, jeśli jest "groźne"
                                $display = str_replace(htmlspecialchars($ip), "<span class='highlight'>" . htmlspecialchars($ip) . "</span>", $display);
                            }
                            echo "$ " . $display;
                        } else {
                            echo "<span class='text-muted small'>Czekam na dane...</span>";
                        }
                        ?>
                    </div>

                    <h6>Wynik systemowy (STDOUT):</h6>
                    <div class="console">
                        <?= $output ? htmlspecialchars($output) : "Brak danych do wyświetlenia." ?>
                    </div>
                </div>
            </div>

            <!-- INSTRUKCJA -->
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white fst-italic">Zadania dla ucznia:</div>
                <div class="card-body">
                    <ol class="small">
                        <li><strong>Standardowe użycie:</strong> Wpisz <code>8.8.8.8</code> i sprawdź, jak działa program.</li>
                        <li><strong>Wstrzykiwanie:</strong> Użyj średnika <code>;</code> aby zakończyć ping i dopisać nową komendę. Wpisz: <code>8.8.8.8 ; id</code></li>
                        <li><strong>Listowanie plików:</strong> Wyświetl zawartość katalogu serwera: <code>8.8.8.8 ; ls -la</code></li>
                        <li><strong>Pliki wrażliwe:</strong> Spróbuj odczytać plik z użytkownikami: <code>8.8.8.8 ; cat /etc/passwd</code></li>
                        <li><strong>Logika logiczna:</strong> Spróbuj użyć operatora <code>&&</code> lub potoku <code>|</code>.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- PANEL EDUKACYJNY -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="eduAccordion">
                
                <!-- DLACZEGO TO DZIAŁA -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Dlaczego to jest niebezpieczne?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            <p>Funkcje takie jak <code>shell_exec()</code>, <code>system()</code> czy <code>exec()</code> w PHP przekazują tekst bezpośrednio do powłoki systemu (Bash/CMD).</p>
                            <p>W systemach Linux średnik <code>;</code> oddziela niezależne komendy. Jeśli wpiszesz <code>ping ; ls</code>, system wykona oba polecenia jedno po drugim.</p>
                            <span class="badge bg-danger">Skutek:</span> Atakujący zyskuje uprawnienia takie same, jakie ma użytkownik uruchamiający serwer WWW (np. <code>www-data</code>).
                        </div>
                    </div>
                </div>

                <!-- ANALIZA KODU -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Analiza kodu: PHP
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <strong>Kod podatny:</strong>
                            <pre class="bg-light p-2 mt-2"><code>shell_exec("ping " . $target);</code></pre>
                            <p>To zwykłe łączenie tekstów. System nie wie, gdzie kończy się adres IP, a zaczyna złośliwa komenda.</p>
                            <hr>
                            <strong>Kod bezpieczny:</strong>
                            <pre class="bg-light p-2 mt-2"><code>if (filter_var($ip, FILTER_VALIDATE_IP)) {
   $safe = escapeshellarg($ip);
   shell_exec("ping " . $safe);
}</code></pre>
                        </div>
                    </div>
                </div>

                <!-- JAK SIĘ BRONIĆ -->
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak zapobiegać Command Injection?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Unikaj wywołań systemowych:</strong> Jeśli istnieje funkcja w PHP do danego zadania, użyj jej zamiast <code>shell_exec</code>.</li>
                                <li><strong>Walidacja danych:</strong> Zawsze sprawdzaj, czy dane wejściowe pasują do oczekiwanego wzorca (np. tylko adres IP).</li>
                                <li><strong>Używaj <code>escapeshellarg()</code>:</strong> Funkcja ta otacza argument cudzysłowami i neutralizuje znaki specjalne, dzięki czemu system potraktuje wszystko jako jeden bezpieczny tekst.</li>
                                <li><strong>Zasada najmniejszych uprawnień:</strong> Serwer WWW nie powinien mieć dostępu do plików systemowych ani uprawnień administratora (root).</li>
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