<?php
$password_input = $_POST['password'] ?? 'Student2025';

// Generowanie różnych typów skrótów
$plain_text = $password_input;
$md5_hash = md5($password_input);
$sha1_hash = sha1($password_input);
$argon2_hash =password_hash($password, PASSWORD_ARGON2ID);

// Sprawdzenie, czy użytkownik testuje hasło
$test_password = $_POST['test_password'] ?? '';
$is_correct = false;
if ($test_password !== '') {
    $is_correct = password_verify($test_password, $argon2_hash);
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Bezpieczne Haszowanie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .method-bad { border-left: 6px solid #dc3545; }
        .method-old { border-left: 6px solid #ffc107; }
        .method-good { border-left: 6px solid #198754; }
        .hash-box { font-family: 'Courier New', monospace; background: #f8f9fa; padding: 10px; border-radius: 4px; word-break: break-all; border: 1px solid #dee2e6; }
        .badge-status { font-size: 0.75rem; vertical-align: middle; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <!-- NAGŁÓWEK -->
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Przechowywanie haseł</h1>
        <p class="lead text-muted">Dowiedz się, dlaczego "skrót" (hash) to nie to samo co "szyfrowanie".</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: GENERATOR -->
        <div class="col-lg-7">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Interaktywny Generator Skrótów</h5>
                    <form method="POST" class="mb-4">
                        <label class="form-label small fw-bold">Wpisz testowe hasło:</label>
                        <div class="input-group">
                            <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($password_input) ?>" placeholder="Twoje hasło">
                            <button class="btn btn-primary">Generuj skróty</button>
                        </div>
                    </form>

                    <!-- TABELA PORÓWNAWCZA -->
                    <div class="space-y-3">
                        <!-- PLAIN TEXT -->
                        <div class="card mb-3 method-bad shadow-sm">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Tekst jawny (Plain Text)</span>
                                    <span class="badge bg-danger">KATASTROFA</span>
                                </div>
                                <div class="hash-box mt-2"><?= htmlspecialchars($plain_text) ?></div>
                            </div>
                        </div>

                        <!-- MD5 -->
                        <div class="card mb-3 method-old shadow-sm">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">MD5</span>
                                    <span class="badge bg-warning text-dark">NIEUŻYWAĆ</span>
                                </div>
                                <div class="hash-box mt-2 text-muted"><?= $md5_hash ?></div>
                                <small class="text-muted">Zawsze taki sam dla tego samego hasła. Łatwy do "odwrócenia".</small>
                            </div>
                        </div>

                        <!-- ARGON2 -->
                        <div class="card mb-3 method-good shadow-sm">
                            <div class="card-body py-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Argon2id (Standard 2024+)</span>
                                    <span class="badge bg-success">BEZPIECZNE</span>
                                </div>
                                <div class="hash-box mt-2 fw-bold"><?= $argon2_hash ?></div>
                                <small class="text-muted">Zawiera unikalną sól. Odśwież stronę – zobaczysz, że hash za każdym razem wygląda inaczej!</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SYMULATOR LOGOWANIA -->
            <div class="card shadow-sm bg-dark text-white">
                <div class="card-body">
                    <h6><i class="bi bi-shield-lock"></i> Symulator weryfikacji hasła</h6>
                    <p class="small text-muted">Sprawdź, czy Twoje wpisane hasło pasuje do wygenerowanego skrótu Argon2.</p>
                    <form method="POST" class="row g-2">
                        <input type="hidden" name="password" value="<?= htmlspecialchars($password_input) ?>">
                        <div class="col-8">
                            <input type="text" name="test_password" class="form-control form-control-sm" placeholder="Potwierdź hasło">
                        </div>
                        <div class="col-4">
                            <button class="btn btn-light btn-sm w-100">Weryfikuj</button>
                        </div>
                    </form>
                    <?php if ($test_password !== ''): ?>
                        <div class="mt-2 small fw-bold <?= $is_correct ? 'text-success' : 'text-danger' ?>">
                            <?= $is_correct ? '✅ Hasło poprawne! password_verify() zwróciło TRUE.' : '❌ Błędne hasło! password_verify() zwróciło FALSE.' ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: TEORIA -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-primary mb-4">
                <div class="card-header bg-primary text-white">Dlaczego MD5 jest złe?</div>
                <div class="card-body small">
                    <p>Skróty typu MD5 i SHA1 są <strong>zbyt szybkie</strong>. Nowoczesny komputer może sprawdzić miliardy takich haseł na sekundę.</p>
                    <h6>Misja "Crack":</h6>
                    <ol>
                        <li>Skopiuj hash MD5 widoczny po lewej.</li>
                        <li>Wejdź na <a href="https://crackstation.net/" target="_blank">CrackStation.net</a>.</li>
                        <li>Wklej hash i zobacz, jak szybko Twoje hasło zostało znalezione w bazie tzw. "tęczowych tablic" (Rainbow Tables).</li>
                    </ol>
                </div>
            </div>

            <div class="card shadow-sm border-success">
                <div class="card-header bg-success text-white">Dlaczego Argon2 jest dobre?</div>
                <div class="card-body small">
                    <ul>
                        <li><strong>Sól (Salt):</strong> Automatycznie dodaje losowy ciąg znaków do każdego hasła. Dzięki temu dwóch użytkowników z hasłem "12345" ma zupełnie inne hashe w bazie.</li>
                        <li><strong>Koszt (Cost):</strong> Argon2 jest celowo "powolny". Złamanie jednego hasła trwa ułamki sekund dla serwera, ale dla hakera próbującego miliardy kombinacji staje się to niewykonalne.</li>
                        <li><strong>Odporność na GPU:</strong> Został zaprojektowany tak, by hakerzy nie mogli używać kart graficznych do przyspieszania ataku.</li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white border rounded">
                <h6>Kod PHP (Jak to robić dobrze):</h6>
                <pre class="bg-light p-2 border rounded small"><code>// 1. Zapisywanie do bazy
$hash = password_hash($password, PASSWORD_ARGON2ID);

// 2. Logowanie
if (password_verify($input, $hash)) {
    echo "Zalogowano!";
}</code></pre>
            </div>
        </div>
    </div>
</div>

</body>
</html>