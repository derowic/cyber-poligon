<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Cyber-Poligon | Szkoła IT</title>
    <style>
        .card:hover { transform: scale(1.02); transition: 0.3s; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 border-bottom d-inline-block">Poligon Cyberbezpieczeństwa</h1>
            <p class="lead mt-3">Wybierz moduł szkoleniowy i przetestuj podatności.</p>
        </div>

        <div class="row g-4">
            <!-- Moduł SQLi -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-danger">01. SQL Injection</h5>
                        <p class="card-text">Atak na bazę danych. Naucz się wyciągać ukryte rekordy i omijać logowanie.</p>
                        <a href="vuln_sqli.php" class="btn btn-outline-danger">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Moduł XSS -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-warning">02. Cross-Site Scripting (XSS)</h5>
                        <p class="card-text">Wstrzykiwanie złośliwego kodu JS. Naucz się przejmować sesje użytkowników.</p>
                        <a href="vuln_xss.php" class="btn btn-outline-warning text-dark">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <!-- Moduł XSS -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-success">03. Insecure File Upload (IFU)</h5>
                        <p class="card-text">To jedna z najgroźniejszych luk, która prowadzi do RCE (Remote Code Execution), czyli zdalnego wykonywania komend na serwerze.</p>
                        <a href="vuln_upload.php" class="btn btn-outline-warning text-dark">Rozpocznij lekcję</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-primary">04. IDOR</h5>
                        <p class="card-text">Dostęp do danych innych osób poprzez prostą zmianę ID w adresie URL.</p>
                        <a href="vuln_idor.php" class="btn btn-outline-primary">Lekcja 04</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title text-success">05. Path Traversal</h5>
                        <p class="card-text">Odczytywanie plików serwera (np. /etc/passwd) przez błędy w ścieżkach.</p>
                        <a href="vuln_traversal.php" class="btn btn-outline-success">Lekcja 05</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-danger">
                    <div class="card-body">
                        <h5 class="card-title text-danger">06. Brute Force</h5>
                        <p class="card-text">Automatyczne łamanie haseł przy użyciu słowników i narzędzia Burp Suite.</p>
                        <a href="vuln_login.php" class="btn btn-outline-danger">Lekcja 06</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-info">
                    <div class="card-body">
                        <h5 class="card-title text-info">07. Hashing & Storage</h5>
                        <p class="card-text">Zobacz dlaczego MD5 to przeszłość i jak bezpiecznie trzymać hasła w bazie.</p>
                        <a href="vuln_hashing.php" class="btn btn-outline-info">Lekcja 07</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-dark">
                    <div class="card-body">
                        <h5 class="card-title text-dark">08. Command Injection</h5>
                        <p class="card-text">Wykonywanie komend systemowych Linuxa bezpośrednio z poziomu strony www.</p>
                        <a href="vuln_cmd.php" class="btn btn-outline-dark">Lekcja 08</a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-secondary">
                    <div class="card-body">
                        <h5 class="card-title text-secondary">09. CSRF Attack</h5>
                        <p class="card-text">Przejęcie akcji użytkownika (np. zmiana hasła) przez złośliwe strony zewnętrzne.</p>
                        <a href="vuln_csrf.php" class="btn btn-outline-secondary">Lekcja 09</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>