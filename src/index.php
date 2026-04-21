<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Cyber-Poligon | Szkoła IT</title>
    <style>
        .card:hover { transform: scale(1.02); transition: 0.3s; }
        /* Ujednolicenie przycisków */
        .btn-action { width: 100%; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 border-bottom d-inline-block">Poligon Cyberbezpieczeństwa</h1>
            <p class="lead mt-3">Wybierz moduł szkoleniowy i przetestuj podatności.</p>
        </div>

        <!-- Instrukcja -->
        <div class="d-flex justify-content-center mb-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">0. Instrukcja</h5>
                        <p class="card-text">Wprowadzenie do platformy i zasady bezpiecznego testowania.</p>
                        <a href="instrukcje.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Moduł SQLi -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">01. SQL Injection</h5>
                        <p class="card-text">Atak na bazę danych. Naucz się wyciągać ukryte rekordy i omijać logowanie.</p>
                        <a href="vuln_sqli.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Moduł XSS -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">02. Cross-Site Scripting (XSS)</h5>
                        <p class="card-text">Wstrzykiwanie złośliwego kodu JS. Naucz się przejmować sesje użytkowników.</p>
                        <a href="vuln_xss.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Moduł IFU -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">03. Insecure File Upload (IFU)</h5>
                        <p class="card-text">Jedna z najgroźniejszych luk, prowadząca do zdalnego wykonywania kodu (RCE).</p>
                        <a href="vuln_upload.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Moduł IDOR -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">04. IDOR</h5>
                        <p class="card-text">Dostęp do danych innych osób poprzez prostą zmianę ID w adresie URL.</p>
                        <a href="vuln_idor.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Path Traversal -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">05. Path Traversal</h5>
                        <p class="card-text">Odczytywanie plików serwera (np. /etc/passwd) przez błędy w ścieżkach.</p>
                        <a href="vuln_traversal.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Brute Force -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">06. Brute Force</h5>
                        <p class="card-text">Automatyczne łamanie haseł przy użyciu słowników i narzędzia Burp Suite.</p>
                        <a href="vuln_login.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Hashing -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">07. Hashing & Storage</h5>
                        <p class="card-text">Dlaczego MD5 to przeszłość i jak bezpiecznie trzymać hasła w bazie.</p>
                        <a href="vuln_hashing.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Command Injection -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">08. Command Injection</h5>
                        <p class="card-text">Wykonywanie komend systemowych Linuxa bezpośrednio z poziomu strony www.</p>
                        <a href="vuln_cmd.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- CSRF -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">09. CSRF Attack</h5>
                        <p class="card-text">Przejęcie akcji użytkownika (np. zmiana hasła) przez złośliwe strony.</p>
                        <a href="vuln_csrf.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

             <!-- XXE -->
             <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">10. XML External Entity (XXE)</h5>
                        <p class="card-text">Atak na parsery XML pozwalający na odczyt plików i skanowanie portów.</p>
                        <a href="vuln_xxe.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

             <!-- Session Fixation -->
             <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">11. Session Fixation</h5>
                        <p class="card-text">Wymuszanie identyfikatora sesji na ofierze w celu późniejszego przejęcia konta.</p>
                        <a href="vuln_session-fixation.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

             <!-- SSRF -->
             <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">12. Server-Side Request Forgery</h5>
                        <p class="card-text">Zmuszanie serwera do wysyłania żądań do wewnętrznej infrastruktury.</p>
                        <a href="vuln_ssrf.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Rate Limiting -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">13. Rate Limiting</h5>
                        <p class="card-text">Brak ograniczeń liczby żądań, co pozwala na ataki DoS i Brute Force.</p>
                        <a href="vuln_rate-limiting.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

             <!-- Open Redirect -->
             <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">14. Open Redirect</h5>
                        <p class="card-text">Wykorzystanie zaufanej domeny do przekierowania użytkownika na złośliwą stronę.</p>
                        <a href="vuln_open-redirect.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

              <!-- CORS -->
              <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">15. CORS Misconfiguration</h5>
                        <p class="card-text">Błędna konfiguracja współdzielenia zasobów między różnymi domenami.</p>
                        <a href="vuln_cors.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

              <!-- Clickjacking -->
              <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">16. Clickjacking</h5>
                        <p class="card-text">Ukrywanie niewidocznych warstw pod przyciskami w celu oszukania użytkownika.</p>
                        <a href="vuln_clickjacking.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

             <!-- JWT -->
             <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">17. Ataki na JWT</h5>
                        <p class="card-text">Manipulacja tokenami sesyjnymi JSON Web Token (alg: none, kid injection).</p>
                        <a href="vuln_jwt-attacks.php" class="btn btn-primary w-100">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>