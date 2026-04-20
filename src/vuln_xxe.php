<?php
// XXE Lab Logic
$xml_input = $_POST['xml'] ?? '';
$vulnerable_result = '';
$safe_result = '';
$mode = $_POST['action'] ?? 'vulnerable';

// Przykładowy payload, który uczeń może przetestować
$example_xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE data [
  <!ENTITY xxe SYSTEM "file:///etc/passwd">
]>
<user>
    <name>Jan Kowalski</name>
    <secret>&xxe;</secret>
</user>
XML;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $xml = $_POST['xml'] ?? '';

    // --- TRYB PODATNY ---
    if ($mode === 'vulnerable') {
        // KATASTROFA: Włączamy obsługę encji zewnętrznych
        libxml_disable_entity_loader(false); 
        $dom = new DOMDocument();
        
        // LIBXML_NOENT - zamienia encje na tekst (to tu dzieje się "magia" ataku)
        // LIBXML_DTDLOAD - pozwala na ładowanie definicji DTD z zewnętrznych źródeł
        if (@$dom->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD)) {
            $vulnerable_result = $dom->saveXML();
        } else {
            $vulnerable_result = "Błąd parsowania XML.";
        }
    }

    // --- TRYB BEZPIECZNY ---
    if ($mode === 'safe') {
        // OBRONA: Całkowite wyłączenie ładowania encji zewnętrznych
        libxml_disable_entity_loader(true);
        $dom = new DOMDocument();
        if (@$dom->loadXML($xml, LIBXML_NOENT)) {
            $safe_result = $dom->saveXML();
        } else {
            $safe_result = "XML odrzucony przez zabezpieczenia.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: XXE Injection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .xml-editor { font-family: monospace; background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; width: 100%; height: 200px; border: none; }
        .result-view { background: #e9ecef; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; border: 1px solid #ccc; white-space: pre-wrap; }
        .code-snippet { background: #1e1e1e; color: #61dafb; padding: 10px; border-radius: 5px; font-size: 0.8rem; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: XXE Injection</h1>
        <p class="lead text-muted">Wykorzystanie parsera XML do odczytu plików systemowych.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL TESTOWY -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-code-slash"></i> Edytor XML
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="small fw-bold text-muted text-uppercase">Wklej swój kod XML:</label>
                            <textarea name="xml" class="xml-editor" placeholder="Tu wpisz XML..."><?= htmlspecialchars($xml_input ?: $example_xml) ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button name="action" value="vulnerable" class="btn btn-danger w-100 fw-bold">Przetwórz (Podatnie)</button>
                            <button name="action" value="safe" class="btn btn-success w-100 fw-bold">Przetwórz (Bezpiecznie)</button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <h6>Wynik przetworzenia (Wyjście):</h6>
                        <div class="result-view">
                            <?php 
                            if ($vulnerable_result) echo htmlspecialchars($vulnerable_result);
                            elseif ($safe_result) echo htmlspecialchars($safe_result);
                            else echo "Czekam na dane...";
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PANEL ANALIZY -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="xxeAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Czym jest XXE?
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            <strong>XXE (XML External Entity)</strong> to atak na aplikację, która parsuje pliki XML. Haker przesyła w sekcji <code>DOCTYPE</code> tzw. "encję zewnętrzną", która wskazuje na plik na dysku serwera (np. <code>/etc/passwd</code>). Jeśli parser jest źle skonfigurowany, wczyta treść tego pliku i wyświetli go użytkownikowi.
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
                            Problem leży w konfiguracji biblioteki <code>libxml</code> w PHP. 
                            W wersji podatnej używamy:
                            <div class="code-snippet my-2">
                                libxml_disable_entity_loader(false);<br>
                                $dom->loadXML($xml, LIBXML_NOENT);
                            </div>
                            Parametr <code>LIBXML_NOENT</code> (No Entities) paradoksalnie nakazuje parserowi <strong>podstawić</strong> treść encji w miejsce jej wystąpienia.
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
                                <li><strong>Wyłącz encje:</strong> Najważniejsza linia obrony to <code>libxml_disable_entity_loader(true)</code>.</li>
                                <li><strong>Aktualizuj PHP:</strong> Od wersji PHP 8.0 encje zewnętrzne są domyślnie wyłączone.</li>
                                <li><strong>Używaj JSON:</strong> Jeśli nie potrzebujesz XML, używaj formatu JSON, który nie obsługuje encji i jest znacznie bezpieczniejszy.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-warning shadow-sm">
                <div class="card-body">
                    <h6 class="text-warning fw-bold"><i class="bi bi-lightbulb"></i> Zadanie dla ucznia:</h6>
                    <ol class="small ps-3 mb-0">
                        <li>Uruchom domyślny kod w <b>trybie podatnym</b>. Zauważ, że w miejscu adresu email pojawiła się treść pliku <code>/etc/passwd</code>.</li>
                        <li>Zmień ścieżkę w <code>SYSTEM "file:///..."</code> na <code>file:///proc/version</code>. Co teraz widzisz?</li>
                        <li>Spróbuj wykonać to samo w <b>trybie bezpiecznym</b>. Zauważ, że encja <code>&amp;xxe;</code> nie została rozpakowana lub parser zgłosił błąd.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>