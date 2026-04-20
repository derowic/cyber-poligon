<?php
$message = "";
$mode = $_POST['mode'] ?? 'vulnerable';
$upload_dir = "uploads/";

// Tworzymy folder uploads, jeśli nie istnieje i nadajemy uprawnienia
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $file_name = basename($_FILES["fileToUpload"]["name"]);
    $target_file = $upload_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        // KATASTROFA: Serwer ufa użytkownikowi. Nie sprawdza co to za plik ani jakie ma rozszerzenie.
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message = "<div class='alert alert-danger'>
                <i class='bi bi-exclamation-triangle'></i> 
                <strong>Plik wrzucony (TRYB PODATNY)!</strong><br>
                Ścieżka: <a href='$target_file' target='_blank'>$target_file</a>
            </div>";
        } else {
            $message = "<div class='alert alert-warning'>Błąd przesyłania.</div>";
        }
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // 1. Sprawdzamy czy to naprawdę obraz (MIME-type)
        $check = @getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        // 2. Biała lista rozszerzeń
        $allowed = ["jpg", "png", "jpeg", "gif"];
        
        if($check !== false && in_array($imageFileType, $allowed)) {
            // 3. Zmiana nazwy na losowy hash (zapobiega nadpisywaniu i ułatwia ukrycie skryptów)
            $new_name = $upload_dir . md5(time() . rand()) . "." . $imageFileType;
            
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $new_name)) {
                $message = "<div class='alert alert-success'>
                    <i class='bi bi-shield-check'></i> 
                    <strong>Plik wrzucony bezpiecznie!</strong><br>
                    Serwer sprawdził typ pliku i zmienił jego nazwę.
                </div>";
            }
        } else {
            $message = "<div class='alert alert-dark text-danger fw-bold'>BŁĄD: Dozwolone tylko pliki graficzne (JPG, PNG, GIF)!</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Insecure File Upload</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .code-box { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 0.85rem; }
        .highlight { color: #f92672; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: File Upload</h1>
        <p class="lead text-muted">Przesyłanie plików – od zdjęcia profilowego do przejęcia serwera.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- PANEL FORMULARZA -->
        <div class="col-lg-6">
            <div class="card shadow <?= $mode == 'secure' ? 'safe-card' : 'vuln-card' ?> mb-4">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-cloud-arrow-up"></i> Prześlij swój awatar
                </div>
                <div class="card-body">
                    <form action="" method="post" enctype="multipart/form-data" class="p-3 border rounded bg-white">
                        <div class="mb-3">
                            <input type="file" name="fileToUpload" class="form-control" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="mode" value="vulnerable" class="btn btn-danger fw-bold">Wyślij (Tryb Podatny)</button>
                            <button type="submit" name="mode" value="secure" class="btn btn-success fw-bold">Wyślij (Tryb Bezpieczny)</button>
                        </div>
                    </form>

                    <div class="mt-4">
                        <?= $message ?>
                    </div>
                </div>
            </div>

            <!-- ANALIZA KODU -->
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white small text-uppercase">Analiza Logiki PHP</div>
                <div class="card-body">
                    <div class="code-box">
                        <?php if ($mode == 'vulnerable'): ?>
                            <span class="text-muted">// Tryb Podatny</span><br>
                            $target = "uploads/" . $_FILES["file"]["name"];<br>
                            <span class="highlight">move_uploaded_file($_FILES["tmp"], $target);</span>
                            <p class="mt-2 small text-danger">// Brak sprawdzenia rozszerzenia! Hacker może wrzucić .php</p>
                        <?php else: ?>
                            <span class="text-muted">// Tryb Bezpieczny</span><br>
                            if (<span class="text-success">in_array($ext, $allowed)</span>) {<br>
                            &nbsp;&nbsp;$new_name = <span class="text-success">md5(...) . $ext;</span><br>
                            &nbsp;&nbsp;move_uploaded_file(..., $new_name);<br>
                            }
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: INSTRUKCJA I ZADANIA -->
        <div class="col-lg-6">
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white">Zadania dla ucznia</div>
                <div class="card-body small">
                    <h6>Krok 1: Normalne użycie</h6>
                    <p>Wgraj dowolne zdjęcie (np. .jpg). Zobaczysz, że serwer je zapisał i możesz je wyświetlić.</p>
                    
                    <h6>Krok 2: Przygotowanie "Web Shella"</h6>
                    <p>Stwórz na pulpicie plik <code>hack.php</code> i wklej do niego ten kod:</p>
                    <div class="bg-dark text-warning p-2 rounded mb-2">
                        <code>&lt;?php system($_GET['cmd']); ?&gt;</code>
                    </div>
                    <p class="text-muted fst-italic">Ten kod pozwala wykonać dowolną komendę w systemie Linux poprzez parametr 'cmd' w adresie URL.</p>

                    <h6>Krok 3: Atak</h6>
                    <p>Wgraj plik <code>hack.php</code> w <b>Trybie Podatnym</b>. Kliknij w link, który się pojawi.</p>

                    <h6>Krok 4: Przejęcie kontroli</h6>
                    <p>Dopisz do adresu URL w przeglądarce: <code>?cmd=id</code> (pokaże kim jesteś) lub <code>?cmd=ls -la ..</code> (pokaże pliki na serwerze).</p>
                </div>
            </div>

            <div class="accordion shadow-sm" id="eduAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Dlaczego to działa? (Wyjaśnienie)
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            Serwer WWW (Apache/Nginx) jest skonfigurowany tak, by pliki z rozszerzeniem <code>.php</code> traktować jako kod do wykonania, a nie jako zwykły obrazek. Jeśli pozwolisz hakerowi umieścić taki plik w folderze dostępnym z internetu, serwer posłusznie wykona zawarte w nim instrukcje. 
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Jak się zabezpieczyć?
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Biała lista rozszerzeń:</strong> Dopuszczaj tylko JPG, PNG, GIF. Nigdy nie blokuj tylko "czarnej listy" (PHP), bo haker użyje np. <code>.php5</code> lub <code>.phtml</code>.</li>
                                <li><strong>Sprawdzaj MIME-type:</strong> Używaj funkcji takich jak <code>getimagesize()</code> lub <code>finfo</code>, by sprawdzić czy plik naprawdę jest obrazem.</li>
                                <li><strong>Zmieniaj nazwy plików:</strong> Nie używaj nazwy podanej przez użytkownika. Generuj losowe nazwy (np. MD5), aby haker nie mógł łatwo wywołać swojego skryptu.</li>
                                <li><strong>Blokuj wykonywanie:</strong> Skonfiguruj serwer tak, by w folderze <code>/uploads</code> wyłączone było wykonywanie skryptów PHP.</li>
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