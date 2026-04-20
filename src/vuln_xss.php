<?php
$name = $_GET['name'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Cross-Site Scripting (XSS)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .html-view { background: #1e1e1e; color: #a6e22e; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; border: 1px solid #444; }
        .highlight-code { color: #f92672; font-weight: bold; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Reflected XSS</h1>
        <p class="lead text-muted">Cross-Site Scripting – kiedy dane stają się kodem wykonywalnym.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL TESTOWY -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-chat-dots"></i> System Powitalny
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 mb-4">
                        <div class="col-sm-7">
                            <input type="text" name="name" class="form-control" placeholder="Wpisz swoje imię..." value="<?= htmlspecialchars($name) ?>">
                        </div>
                        <div class="col-sm-5 d-grid gap-2 d-md-block">
                            <button name="mode" value="vulnerable" class="btn btn-danger btn-sm">Wyślij (Podatny)</button>
                            <button name="mode" value="secure" class="btn btn-success btn-sm">Wyślij (Bezpieczny)</button>
                        </div>
                    </form>

                    <div class="p-4 border rounded bg-white shadow-sm text-center">
                        <?php if ($name !== ''): ?>
                            <small class="text-muted text-uppercase d-block mb-2">Wynik wyświetlony na stronie:</small>
                            <?php if ($mode === 'vulnerable'): ?>
                                <div class="fs-3">Witaj, <span class="fw-bold"><?php echo $name; ?></span>!</div>
                            <?php else: ?>
                                <div class="fs-3">Witaj, <span class="fw-bold"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>!</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted italic">Czekam na imię...</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PORÓWNANIE KODU HTML -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white small">Co widzi przeglądarka w kodzie źródłowym (Ctrl+U)?</div>
                <div class="card-body">
                    <div class="html-view">
                        <?php if ($name !== ''): ?>
                            <?php if ($mode === 'vulnerable'): ?>
                                &lt;div&gt;Witaj, <span class="highlight-code"><?= $name ?></span>!&lt;/div&gt;
                            <?php else: ?>
                                &lt;div&gt;Witaj, <span class="text-info"><?= htmlspecialchars($name) ?></span>!&lt;/div&gt;
                            <?php endif; ?>
                        <?php else: ?>
                            &lt;div&gt;Witaj, ...!&lt;/div&gt;
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL ANALIZY -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="xssAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Co to jest XSS?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            <strong>Cross-Site Scripting (XSS)</strong> to podatność, która pozwala napastnikowi na wstrzyknięcie złośliwego kodu (zazwyczaj JavaScript) do strony oglądanej przez innego użytkownika. 
                            W wersji <b>Reflected</b> skrypt jest "odbijany" od serwera – przesyłasz go w parametrze URL, a serwer bez sprawdzenia wstawia go do kodu strony.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Dlaczego to działa? (Mechanizm)
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            Przeglądarka internetowa nie wie, czy dany tekst pochodzi od programisty, czy od hakera. Jeśli w kodzie HTML znajdą się znaki <code>&lt;script&gt;</code>, przeglądarka po prostu wykona to, co jest w środku. <br><br>
                            Haker może w ten sposób ukraść ciasteczka sesyjne (<code>alert(document.cookie)</code>), przekierować użytkownika na inną stronę lub podmienić treść witryny.
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
                            Najskuteczniejszą metodą jest <strong>Escaping</strong> (neutralizacja znaków specjalnych). 
                            W PHP używamy funkcji <code>htmlspecialchars()</code>. <br><br>
                            Zamienia ona znaki:
                            <ul>
                                <li><code><</code> na <code>&amp;lt;</code></li>
                                <li><code>></code> na <code>&amp;gt;</code></li>
                            </ul>
                            Dzięki temu przeglądarka nie widzi ich jako tagów HTML, lecz jako zwykły tekst do wyświetlenia.
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="bi bi-lightbulb"></i> Zadania dla ucznia:</h6>
                    <ul class="small ps-3 mb-0">
                        <li><strong>Atak 1:</strong> W trybie podatnym wpisz: <br><code>&lt;script&gt;alert('XSS!')&lt;/script&gt;</code></li>
                        <li><strong>Atak 2 (Stylizacja):</strong> Spróbuj zmienić kolor tła: <br><code>&lt;style&gt;body{background:red !important;}&lt;/style&gt;</code></li>
                        <li><strong>Porównanie:</strong> Wykonaj Atak 1 w trybie bezpiecznym i spójrz na okno "Co widzi przeglądarka". Zauważ różnicę w interpretacji znaków.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>