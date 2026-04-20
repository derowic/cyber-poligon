<?php
$mode = $_GET['mode'] ?? 'vulnerable';

if ($mode === 'safe') {
    header("X-Frame-Options: DENY");
    header("Content-Security-Policy: frame-ancestors 'none'");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Ofiara Clickjacking</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 40px; background: #f0f0f0; }
        .btn-real {
            padding: 15px 30px;
            font-size: 1.3rem;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h3>Twoja ważna akcja</h3>
    <p>Jeśli to widzisz — strona jest załadowana normalnie.</p>
    <button class="btn-real" onclick="alert('Akcja wykonana! (np. hasło zostało zmienione / przelew wysłany)')">
        Potwierdź akcję
    </button>
    <p class="mt-4 text-muted small">To jest prawdziwa strona docelowa ataku</p>
</body>
</html>