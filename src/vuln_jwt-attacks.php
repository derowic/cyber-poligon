<?php
// JWT Lab Logic
$secret = "supertajnehaslo123";           // słabe hasło (podatne na brute-force)
$strong_secret = 'K4#9x$mP2!vL8nQ7wZxYcT5bN9pR2vJ8'; // silne hasło

$payload = [
    'user_id' => 5,
    'username' => 'student_lab',
    'role' => 'user',
    'exp' => time() + 3600
];

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function generate_jwt($payload, $secret, $alg = 'HS256') {
    // 1. Create Header
    $header = json_encode(['alg' => $alg, 'typ' => 'JWT']);
    $header_b64 = base64url_encode($header);
    
    // 2. Create Payload
    $payload_b64 = base64url_encode(json_encode($payload));
    
    // 3. Create Signature
    if (strtolower($alg) === 'none') {
        $signature_b64 = "";
    } else {
        // Map JWT alg names to PHP hash names
        $hmac_alg = ($alg === 'HS512') ? 'sha512' : 'sha256'; 
        $signature = hash_hmac($hmac_alg, "$header_b64.$payload_b64", $secret, true);
        $signature_b64 = base64url_encode($signature);
    }
    
    return "$header_b64.$payload_b64.$signature_b64";
}
$vulnerable_jwt = generate_jwt($payload, $secret, 'HS256');
$none_jwt = generate_jwt(array_merge($payload, ['role' => 'admin']), '', 'none');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Ataki na JWT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        .jwt-token { word-break: break-all; font-family: monospace; background: #f8f9fa; padding: 10px; border-radius: 5px; border: 1px solid #ddd; font-size: 0.9rem; }
        .jwt-header { color: #fb015b; font-weight: bold; }
        .jwt-payload { color: #d63aff; font-weight: bold; }
        .jwt-signature { color: #00b9f1; font-weight: bold; }
        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold">Laboratorium: Bezpieczeństwo JWT</h1>
        <p class="lead text-muted">JSON Web Token – jak błędy w implementacji pozwalają na eskalację uprawnień.</p>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">← Powrót do menu</a>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: TWOJE TOKENY -->
        <div class="col-lg-7">
            <div class="card shadow vuln-card mb-4">
                <div class="card-header bg-white fw-bold">Twoje Tokeny Sesyjne</div>
                <div class="card-body">
                    <h6>1. Prawidłowy token użytkownika (HS256):</h6>
                    <div class="jwt-token mb-3">
                        <?php 
                        $parts = explode('.', $vulnerable_jwt);
                        echo "<span class='jwt-header'>{$parts[0]}</span>.<span class='jwt-payload'>{$parts[1]}</span>.<span class='jwt-signature'>{$parts[2]}</span>";
                        ?>
                    </div>

                    <h6>2. Zmanipulowany token (Algorytm "none"):</h6>
                    <div class="jwt-token mb-3">
                        <?php 
                        $parts_none = explode('.', $none_jwt);
                        echo "<span class='jwt-header'>{$parts_none[0]}</span>.<span class='jwt-payload'>{$parts_none[1]}</span>.<span class='jwt-signature text-muted'>[BRAK PODPISU]</span>";
                        ?>
                    </div>
                    
                    <div class="alert alert-danger small mt-3">
                        <i class="bi bi-exclamation-triangle"></i> 
                        W wersji podatnej serwer ufa nagłówkowi <code>"alg": "none"</code> i nie sprawdza podpisu, co pozwala zmienić rolę na <b>admin</b>.
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-danger" onclick="alert('Zalogowano jako ADMIN (Atak None Alg)')">Testuj Atak "None"</button>
                    </div>
                </div>
            </div>

            <div class="card shadow safe-card">
                <div class="card-header bg-white fw-bold text-success">Jak wygląda bezpieczny token?</div>
                <div class="card-body">
                    <p class="small text-muted">Bezpieczny token używa silnego klucza, którego nie da się odgadnąć metodą brute-force.</p>
                    <div class="jwt-token">
                        <?php 
                        $safe_jwt = generate_jwt($payload, $strong_secret);
                        $parts_safe = explode('.', $safe_jwt);
                        echo "<span class='jwt-header'>{$parts_safe[0]}</span>.<span class='jwt-payload'>{$parts_safe[1]}</span>.<span class='jwt-signature'>{$parts_safe[2]}</span>";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-5">
            <div class="accordion shadow-sm" id="jwtAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#c1">
                            Anatomia JWT
                        </button>
                    </h2>
                    <div id="c1" class="accordion-collapse collapse show">
                        <div class="accordion-body small">
                            JWT składa się z 3 części oddzielonych kropkami:
                            <ul>
                                <li><span class="jwt-header">Nagłówek:</span> Typ tokena i algorytm (np. HS256).</li>
                                <li><span class="jwt-payload">Ładunek (Payload):</span> Dane użytkownika (ID, login, rola).</li>
                                <li><span class="jwt-signature">Podpis:</span> Służy do weryfikacji, czy dane nie zostały zmienione.</li>
                            </ul>
                            <strong>Ważne:</strong> Każdy może odkodować dane (to tylko Base64), ale nikt nie powinien móc ich zmienić bez znajomości klucza!
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c2">
                            Dlaczego to działa? (Ataki)
                        </button>
                    </h2>
                    <div id="c2" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <p><strong>1. Algorytm "none":</strong> Niektóre biblioteki akceptują tokeny bez podpisu, jeśli w nagłówku ustawimy <code>alg: none</code>. To tak, jakbyś sam wypisał sobie legitymację i powiedział "nie sprawdzaj pieczątki".</p>
                            <p><strong>2. Słaby klucz (Brute-force):</strong> Jeśli klucz to np. <code>secret123</code>, haker może w kilka sekund odgadnąć go na swoim komputerze i zacząć podpisywać własne tokeny jako admin.</p>
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#c3">
                            Jak zabezpieczyć?
                        </button>
                    </h2>
                    <div id="c3" class="accordion-collapse collapse">
                        <div class="accordion-body small">
                            <ul>
                                <li><strong>Nigdy</strong> nie ufaj polu <code>alg</code> z nagłówka. Wymuszaj konkretny algorytm w kodzie serwera.</li>
                                <li>Używaj <strong>bardzo silnych kluczy</strong> (minimum 32 losowe znaki).</li>
                                <li>Stosuj biblioteki, które są odporne na znane ataki.</li>
                                <li>Zawsze sprawdzaj datę wygaśnięcia <code>exp</code>.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4 border-info shadow-sm">
                <div class="card-body">
                    <h6 class="text-info fw-bold"><i class="bi bi-lightbulb"></i> Zadanie dla ucznia:</h6>
                    <ol class="small ps-3">
                        <li>Wejdź na <a href="https://jwt.io" target="_blank">jwt.io</a>.</li>
                        <li>Wklej pierwszy token (ten z HS256).</li>
                        <li>Zmień w sekcji Payload <code>"role": "user"</code> na <code>"role": "admin"</code>.</li>
                        <li>Zauważ, że podpis (Signature) stał się nieprawidłowy (Invalid Signature), bo nie znasz klucza.</li>
                        <li>Spróbuj teraz wkleić token "none" i zobacz, jak wygląda jego struktura.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>