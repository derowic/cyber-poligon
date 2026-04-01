<?php
$output = "";
$mode = $_POST['mode'] ?? 'vulnerable';

if (isset($_POST['ip'])) {
    $target = $_POST['ip'];

    // --- LOGIKA PODATNA ---
    if ($mode == 'vulnerable') {
        // KATASTROFA: Łączymy komendę ping z tym, co wpisał użytkownik
        // Przykład ataku: 8.8.8.8 ; ls -la /etc/
        $output = shell_exec("ping -c 3 " . $target);
    }

    // --- LOGIKA BEZPIECZNA ---
    if ($mode == 'secure') {
        // Filtrujemy dane - dopuszczamy tylko cyfry i kropki (adres IP)
        if (filter_var($target, FILTER_VALIDATE_IP)) {
            $output = shell_exec("ping -c 3 " . escapeshellarg($target));
        } else {
            $output = "BŁĄD: To nie jest poprawny adres IP!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Command Injection Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 08: OS Command Injection</h2>
    
    <div class="card p-4 shadow">
        <h5>Sieciowe narzędzie diagnostyczne (Ping)</h5>
        <form method="POST">
            <div class="input-group mb-3">
                <input type="text" name="ip" class="form-control" placeholder="Wpisz IP (np. 8.8.8.8)">
                <button name="mode" value="vulnerable" class="btn btn-danger">Ping (Podatny)</button>
                <button name="mode" value="secure" class="btn btn-success">Ping (Bezpieczny)</button>
            </div>
        </form>
        
        <?php if ($output): ?>
            <pre class="bg-dark text-white p-3 rounded mt-3"><?php echo htmlspecialchars($output); ?></pre>
        <?php endif; ?>
    </div>

    <div class="mt-4 alert alert-info">
        <h5>Misja: Przejmij powłokę serwera</h5>
        <ol>
            <li>Wpisz <code>8.8.8.8</code> – zobaczysz standardowy wynik ping.</li>
            <li>Użyj separatora komend Linuxa <code>;</code> lub <code>&&</code> aby dopisać własne polecenie.</li>
            <li>Payload: <code>8.8.8.8 ; cat /etc/passwd</code></li>
            <li>Zadanie dodatkowe: Sprawdź listę zainstalowanych pakietów używając <code>; dpkg -l</code></li>
        </ol>
    </div>
</div>
</body>
</html>