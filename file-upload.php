<?php
// Insecure File Upload Demo
$upload_dir = __DIR__ . '/uploads/';
$vulnerable_message = '';
$safe_message = '';
$uploaded_files = [];

// Tworzenie katalogu uploads jeśli nie istnieje
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Lista plików w uploads (do podglądu)
$files = glob($upload_dir . '*');
foreach ($files as $file) {
    if (is_file($file)) {
        $uploaded_files[] = basename($file);
    }
}

// ==================== OBSŁUGA UPLOADU ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file'];
    $filename = $file['name'];
    $tmp_name = $file['tmp_name'];
    $error = $file['error'];

    if ($error !== UPLOAD_ERR_OK) {
        $vulnerable_message = $safe_message = "Błąd podczas uploadu pliku.";
    } else {

        // WERSJA PODATNA - prawie bez żadnych sprawdzeń
        if (isset($_POST['action']) && $_POST['action'] === 'vulnerable') {
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($tmp_name, $destination)) {
                $vulnerable_message = "✅ Plik <strong>" . htmlspecialchars($filename) . "</strong> został przesłany (wersja podatna).";
                // Dodajemy link do pliku
                $vulnerable_message .= "<br><a href='uploads/" . htmlspecialchars($filename) . "' target='_blank'>Otwórz plik</a>";
            } else {
                $vulnerable_message = "❌ Nie udało się zapisać pliku.";
            }
        }

        // WERSJA BEZPIECZNA - z walidacją
        if (isset($_POST['action']) && $_POST['action'] === 'safe') {
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
            $file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            $max_size = 2 * 1024 * 1024; // 2 MB

            if (!in_array($file_ext, $allowed_extensions)) {
                $safe_message = "❌ Niedozwolony typ pliku. Dozwolone tylko: " . implode(', ', $allowed_extensions);
            } 
            elseif ($file['size'] > $max_size) {
                $safe_message = "❌ Plik jest za duży. Maksymalny rozmiar: 2 MB.";
            }
            elseif (!in_array(mime_content_type($tmp_name), ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'])) {
                $safe_message = "❌ Nieprawidłowy typ MIME pliku.";
            }
            else {
                // Bezpieczna nazwa pliku
                $new_filename = uniqid('file_') . '.' . $file_ext;
                $destination = $upload_dir . $new_filename;

                if (move_uploaded_file($tmp_name, $destination)) {
                    $safe_message = "✅ Plik został przesłany bezpiecznie jako <strong>" . htmlspecialchars($new_filename) . "</strong>.";
                    $safe_message .= "<br><a href='uploads/" . htmlspecialchars($new_filename) . "' target='_blank'>Otwórz plik</a>";
                } else {
                    $safe_message = "❌ Nie udało się zapisać pliku.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Insecure File Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; background: #fff3f3; }
        .safe-card { border-left: 6px solid #28a745; background: #f0fff0; }
        .highlight { font-weight: bold; color: #dc3545; background: #fff3cd; padding: 2px 6px; border-radius: 4px; }
        .safe-highlight { font-weight: bold; color: #28a745; background: #d4edda; padding: 2px 6px; border-radius: 4px; }
        .output-box { padding: 15px; border-radius: 8px; min-height: 80px; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Demonstracja Insecure File Upload</h1>
        <p class="lead text-muted">Lewa strona = podatna • Prawa strona = zabezpieczona</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">← SQL</a>
            <a href="xss.php" class="btn btn-outline-secondary">← XSS</a>
            <a href="csrf.php" class="btn btn-outline-secondary">← CSRF</a>
            <a href="idor.php" class="btn btn-outline-secondary">← IDOR</a>
            <a href="command-injection.php" class="btn btn-outline-secondary">← Command Injection</a>
        </div>
    </div>

    <div class="row g-5">
        <!-- ==================== WERSJA PODATNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow vuln-card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="bi bi-bug"></i> Wersja podatna na Insecure File Upload</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Akceptuje prawie każdy plik bez sprawdzania rozszerzenia ani zawartości.</p>

                    <form method="POST" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="action" value="vulnerable">
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Prześlij plik (podatnie)</button>
                    </form>

                    <?php if ($vulnerable_message): ?>
                        <div class="output-box alert alert-info"><?= $vulnerable_message ?></div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (podatny):</h6>
                    <pre class="bg-white p-3 border"><code>$destination = $upload_dir . <span class="highlight">$filename</span>;
move_uploaded_file($tmp_name, $destination);</code></pre>

                    <div class="alert alert-danger small">
                        <strong>Możliwe ataki:</strong><br>
                        • Upload pliku <code>shell.php</code> z kodem PHP<br>
                        • Plik z rozszerzeniem <code>.php.jpg</code><br>
                        • Plik z treścią <code>&lt;?php system($_GET['cmd']); ?&gt;</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== WERSJA BEZPIECZNA ==================== -->
        <div class="col-lg-6">
            <div class="card h-100 shadow safe-card">
                <div class="card-header bg-success text-white">
                    <h4><i class="bi bi-shield-check"></i> Wersja zabezpieczona</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted">Sprawdza rozszerzenie, rozmiar i typ MIME + generuje nową nazwę pliku.</p>

                    <form method="POST" enctype="multipart/form-data" class="mb-4">
                        <input type="hidden" name="action" value="safe">
                        <div class="mb-3">
                            <input type="file" name="file" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Prześlij plik (bezpiecznie)</button>
                    </form>

                    <?php if ($safe_message): ?>
                        <div class="output-box alert alert-success"><?= $safe_message ?></div>
                    <?php endif; ?>

                    <h6 class="mt-4">Kod źródłowy (bezpieczny):</h6>
                    <pre class="bg-white p-3 border"><code>$allowed = ['jpg','jpeg','png','gif','pdf'];
$file_ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($file_ext, $allowed) || 
    $file['size'] > 2*1024*1024 || 
    !in_array(mime_content_type($tmp_name), [...])) {
    // odrzuć
} else {
    $new_name = uniqid('file_') . '.' . $file_ext;
    move_uploaded_file($tmp_name, $upload_dir . $new_name);
}</code></pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista przesłanych plików -->
    <?php if (!empty($uploaded_files)): ?>
    <div class="mt-5">
        <h5>Przesłane pliki (w katalogu uploads):</h5>
        <div class="list-group">
            <?php foreach ($uploaded_files as $f): ?>
                <a href="uploads/<?= htmlspecialchars($f) ?>" target="_blank" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars($f) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Instrukcja dla uczniów -->
    <div class="mt-5 alert alert-warning">
        <h5><i class="bi bi-lightbulb"></i> Instrukcja testowania dla uczniów</h5>
        <ol>
            <li>Na lewej stronie (podatnej) spróbuj przesłać plik z rozszerzeniem <code>.php</code> zawierający kod:
                <pre class="bg-light p-2">&lt;?php system($_GET['cmd']); ?&gt;</pre>
            </li>
            <li>Po uploadzie otwórz plik przez link i dodaj parametr <code>?cmd=ls</code> lub <code>?cmd=id</code></li>
            <li>Sprawdź, czy możesz wykonać polecenia systemowe (Remote Code Execution)</li>
            <li>Na prawej stronie (bezpiecznej) spróbuj przesłać ten sam plik – powinien zostać odrzucony</li>
            <li>Przetestuj też pliki z podwójnym rozszerzeniem: <code>shell.php.jpg</code>, <code>shell.jpg.php</code></li>
        </ol>
        <strong>Uwaga bezpieczeństwa:</strong> W środowisku produkcyjnym nigdy nie umieszczaj katalogu uploads w miejscu dostępnym przez web server bez dodatkowych zabezpieczeń (np. .htaccess lub oddzielny serwer plików).
    </div>

    <div class="text-center mt-5 small text-muted">
        Demo Insecure File Upload • PHP 8.3 + Bootstrap 5
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>