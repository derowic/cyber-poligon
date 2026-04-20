<?php
// Reflected XSS Demo – czysta strona tylko z XSS

$xss_input = $_POST['xss_input'] ?? '';
$vuln_output = null;
$safe_output = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'vulnerable') {
            $vuln_output = $xss_input;                    // ← podatność XSS
        }
        if ($_POST['action'] === 'safe') {
            $safe_output = htmlspecialchars($xss_input, ENT_QUOTES, 'UTF-8'); // ← ochrona
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Reflected XSS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card {
            border-left: 6px solid #dc3545;
            background: #fff3f3;
        }
        .safe-card {
            border-left: 6px solid #28a745;
            background: #f0fff0;
        }
        .highlight {
            font-weight: bold;
            color: #dc3545;
            background: #fff3cd;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .safe-highlight {
            font-weight: bold;
            color: #28a745;
            background: #d4edda;
            padding: 2px 6px;
            border-radius: 4px;
        }
        .output-box {
            min-height: 120px;
            padding: 20px;
            border: 2px dashed #6c757d;
            border-radius: 8px;
            background: white;
            font-size: 1.1rem;
        }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Demonstracja Reflected XSS</h1>
        <p class="lead text-muted">Lewa strona = podatna • Prawa strona = zabezpieczona</p>
        <a href="index.php" class="btn btn-outline-secondary mt-2">
            ← Wróć do demo SQL Injection
        </a>
    </div>

    <div class="row g-5">
        <!-- ==================== XSS PODATNY ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow vuln-card">
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0"><i class="bi bi-bug"></i> Wersja podatna na XSS</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Dane z formularza są wyświetlane bez żadnego escapowania.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="vulnerable">
                        <div class="input-group">
                            <input type="text" name="xss_input" class="form-control" 
                                   placeholder="Wpisz tekst (np. komentarz)" 
                                   value="<?= htmlspecialchars($xss_input) ?>">
                            <button class="btn btn-danger">Wyświetl (niebezpiecznie)</button>
                        </div>
                    </form>

                    <div class="output-box">
                        <strong>Wynik (bez ochrony):</strong><br><br>
                        <?php if ($vuln_output !== null): ?>
                            <?= $vuln_output ?>   <!-- ← tutaj wykonuje się XSS -->
                        <?php endif; ?>
                    </div>

                    <h6 class="mt-4">Kod źródłowy (podatny):</h6>
                    <pre class="bg-white p-3 border"><code>&lt;?php
// <span class="highlight">Brak zabezpieczenia – dane trafiają bezpośrednio do HTML</span>
echo $xss_input;
?&gt;</code></pre>

                    <div class="alert alert-danger small">
                        <strong>Przykładowe payloady do testowania:</strong><br>
                        • &lt;script&gt;alert('XSS')&lt;/script&gt;<br>
                        • &lt;img src=x onerror=alert(1)&gt;<br>
                        • &lt;svg onload=alert(1)&gt;
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== XSS BEZPIECZNY ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow safe-card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-check"></i> Wersja zabezpieczona</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Dane są escapowane za pomocą <code>htmlspecialchars()</code>.</p>

                    <form method="POST" class="mb-4">
                        <input type="hidden" name="action" value="safe">
                        <div class="input-group">
                            <input type="text" name="xss_input" class="form-control" 
                                   placeholder="Wpisz tekst (np. komentarz)" 
                                   value="<?= htmlspecialchars($xss_input) ?>">
                            <button class="btn btn-success">Wyświetl (bezpiecznie)</button>
                        </div>
                    </form>

                    <div class="output-box">
                        <strong>Wynik (zabezpieczony):</strong><br><br>
                        <?php if ($safe_output !== null): ?>
                            <?= $safe_output ?>
                        <?php endif; ?>
                    </div>

                    <h6 class="mt-4">Kod źródłowy (bezpieczny):</h6>
                    <pre class="bg-white p-3 border"><code>&lt;?php
// <span class="safe-highlight">Zabezpieczenie: htmlspecialchars z ENT_QUOTES</span>
echo htmlspecialchars($xss_input, ENT_QUOTES, 'UTF-8');
?&gt;</code></pre>

                    <div class="alert alert-success small">
                        Nawet jeśli ktoś wpisze kod JavaScript, zostanie on wyświetlony jako zwykły tekst.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="text-center mt-5 small text-muted">
        Demo Reflected XSS • PHP 8.3 + Bootstrap 5<br>
        <a href="index.php">← Demo SQL Injection</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>