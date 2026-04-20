<?php
// CORS Demo - Główny plik labu
$mode = $_GET['mode'] ?? 'vulnerable';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: CORS Misconfiguration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .header-view { background: #1e1e1e; color: #61dafb; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.9rem; }
        .http-key { color: #f92672; }
        .http-val { color: #e6db74; }
        .api-console { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; min-height: 80px; }
        .origin-badge { font-size: 0.8rem; background: #eee; padding: 2px 5px; border-radius: 3px; border: 1px solid #ccc; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: CORS Misconfiguration</h1>
        <p class="lead text-muted">Zrozum, jak błędna konfiguracja nagłówków pozwala obcym stronm kraść dane Twoich użytkowników.</p>
        <div class="btn-group">
            <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
            <a href="?mode=safe" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: SYMULACJA -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-body">
                    <h5 class="card-title">Symulator Ataku Międzywitrynowego</h5>
                    <p class="small text-muted">Wyobraź sobie, że jesteś na stronie <code>zlosliwa-strona.pl</code> i próbujesz pobrać dane z <code>twoja-bankowosc.pl</code>.</p>
                    
                    <div class="p-3 border rounded bg-white mb-4">
                        <h6>Atakujący (JS Fetch):</h6>
                        <div class="mb-3">
                            <code>Origin: <span class="origin-badge text-danger">https://evil-hacker.com</span></code>
                        </div>
                        <button onclick="simulateCORSAttack()" class="btn btn-primary w-100">
                            Wykonaj żądanie Cross-Origin (JS Fetch)
                        </button>
                    </div>

                    <h6>Odpowiedź serwera (Nagłówki HTTP):</h6>
                    <div class="header-view mb-4">
                        <div>HTTP/1.1 200 OK</div>
                        <div><span class="http-key">Content-Type:</span> <span class="http-val">application/json</span></div>
                        <?php if ($mode == 'vulnerable'): ?>
                            <div><span class="http-key">Access-Control-Allow-Origin:</span> <span class="http-val text-warning">*</span></div>
                            <div><span class="http-key">Access-Control-Allow-Credentials:</span> <span class="http-val">true</span></div>
                        <?php else: ?>
                            <div><span class="http-key">Access-Control-Allow-Origin:</span> <span class="http-val">https://zaufany-partner.pl</span></div>
                        <?php endif; ?>
                    </div>

                    <h6>Wynik w przeglądarce (Dane):</h6>
                    <div id="api-result" class="api-console">
                        Czekam na wykonanie żądania...
                    </div>
                </div>
            </div>

            <!-- INSTRUKCJA -->
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">Zadania dla ucznia:</div>
                <div class="card-body small">
                    <ol>
                        <li><strong>Tryb Podatny:</strong> Kliknij przycisk. Zauważ, że dane (np. sekretny klucz) zostały pobrane, mimo że origin jest "złośliwy". Stało się tak przez gwiazdkę <code>*</code> w nagłówku.</li>
                        <li><strong>Tryb Bezpieczny:</strong> Przełącz tryb i spróbuj ponownie. Zobaczysz błąd CORS – przeglądarka zablokowała odczyt danych, bo origin się nie zgadza.</li>
                        <li><strong>F12:</strong> Otwórz narzędzia deweloperskie, przejdź do zakładki <b>Console</b>. Tam zobaczysz czerwony błąd wygenerowany przez mechanizm bezpieczeństwa przeglądarki.</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="corsAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Czym jest CORS?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            CORS (Cross-Origin Resource Sharing) to mechanizm, który mówi przeglądarce: <em>"Hej, ufam tej innej stronie, pozwól jej odczytać moje dane"</em>. 
                            Domyślnie przeglądarki stosują <strong>SOP (Same-Origin Policy)</strong>, która zabrania stronie A czytać dane ze strony B. CORS to jedyny sposób, by to "rozluźnić".
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Gdzie leży błąd (Misconfiguration)?
                        </button>
                    
                        <div class="accordion-body small">
                            Hakerzy szukają dwóch błędów:
                            <ul>
                                <li><strong>Gwiazdka (Wildcard):</strong> <code>Allow-Origin: *</code> pozwala każdej stronie w internecie na kradzież danych.</li>
                                <li><strong>Refleksyjny Origin:</strong> Serwer bierze nagłówek <code>Origin</code> od klienta i bez sprawdzenia wstawia go do odpowiedzi. To tak, jakby pytać złodzieja: "Czy Cię wpuścić?" a on odpowiada "Tak", a Ty mówisz "Ok, wchodź".</li>
                            </ul>
                        </div>
                 
                </div>

                <div class="accordion-item">
                   
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Bezpieczny kod (PHP)
                        </button>
                  
                  
                        <div class="accordion-body small">
                            <pre class="bg-light p-2 border"><code>$allowed = ['https://moj-sklep.pl'];
$origin = $_SERVER['HTTP_ORIGIN'];

if (in_array($origin, $allowed)) {
    header("Access-Control-Allow-Origin: $origin");
}</code></pre>
                            Nigdy nie używaj <code>*</code> jeśli API zwraca prywatne dane użytkowników!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function simulateCORSAttack() {
    const resultDiv = document.getElementById('api-result');
    const mode = "<?= $mode ?>";
    
    resultDiv.innerHTML = "Wysyłanie żądania...";
    resultDiv.style.color = "#f8f8f2";

    // Symulacja działania przeglądarki
    setTimeout(() => {
        if (mode === 'vulnerable') {
            resultDiv.innerHTML = "✅ SUKCES: Pobrano dane!\n\n{ \n  \"user\": \"admin\", \n  \"secret_token\": \"KRACH_GIEŁDOWY_2025\" \n}";
            resultDiv.style.color = "#a6e22e";
        } else {
            resultDiv.innerHTML = "❌ BŁĄD CORS: Przeglądarka zablokowała dostęp!\n\nReason: Origin 'https://evil-hacker.com' is not allowed by Access-Control-Allow-Origin.";
            resultDiv.style.color = "#f92672";
            console.error("Access to fetch at 'api.bank.pl' from origin 'https://evil-hacker.com' has been blocked by CORS policy.");
        }
    }, 1000);
}
</script>

</body>
</html>