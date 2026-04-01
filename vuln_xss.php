<?php
$name = $_GET['name'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>XSS Lab</title>
</head>
<body >
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 02: Cross-Site Scripting (Reflected)</h2>

    <div class="card p-4 mb-4">
        <h5>Wpisz swoje imię, aby system mógł Cię powitać:</h5>
        <form method="GET" class="row g-3">
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="Twoje imię...">
            </div>
            <div class="col-auto">
                <button name="mode" value="vulnerable" class="btn btn-danger">Wyślij (Tryb podatny)</button>
                <button name="mode" value="secure" class="btn btn-success">Wyślij (Tryb bezpieczny)</button>
            </div>
        </form>
    </div>

    <div class="alert alert-info shadow">
        <h4>Wynik działania:</h4>
        <hr>
        <?php if ($name !== ''): ?>
            <?php if ($mode === 'vulnerable'): ?>
                <!-- PODATNOŚĆ: Wyświetlamy zmienną bezpośrednio -->
                <div class="fs-3">Witaj, <?php echo $name; ?>!</div>
            <?php else: ?>
                <!-- ZABEZPIECZENIE: Używamy htmlspecialchars -->
                <div class="fs-3">Witaj, <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>!</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="mt-5 border-top pt-3">
        <h5>Zadania dla ucznia:</h5>
        <ol>
            <li>Wpisz zwykłe imię (np. Adam) – oba tryby działają tak samo.</li>
            <li>Wstrzyknij skrypt (Tryb podatny): <code>&lt;script&gt;alert('Hacked!');&lt;/script&gt;</code></li>
            <li>Spróbuj zmienić wygląd strony: <code>&lt;style&gt;body { background: red !important; }&lt;/style&gt;</code></li>
            <li>Zobacz źródło strony (Ctrl+U) w obu trybach i porównaj, jak przeglądarka widzi znaki <code>&lt;</code> oraz <code>&gt;</code>.</li>
        </ol>
    </div>
</div>
</body>
</html>