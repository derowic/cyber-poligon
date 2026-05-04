
🛡️ Laboratorium Cyberbezpieczeństwa: Poligon Web
Witaj w laboratorium poświęconym bezpieczeństwu aplikacji internetowych! Ten projekt to "poligon doświadczalny", który pozwoli Ci poznać najpopularniejsze podatności (zgodnie z listą OWASP Top 10) oraz nauczyć się, jak przed nimi chronić swoje aplikacje.
📌 O projekcie
Laboratorium zawiera szereg modułów interaktywnych (PHP/MySQL), które demonstrują różnice między kodem podatnym a bezpiecznym.

🛠️ Wymagania systemowe
Do uruchomienia laboratorium potrzebujesz:
Docker oraz Docker Compose zainstalowane na Twoim systemie.
Terminal (Linux, macOS) lub PowerShell (Windows).

🚀 Instalacja i uruchomienie
1. Przygotowanie struktury plików
Upewnij się, że Twoje pliki są ułożone w następujący sposób:
.
├── docker-compose.yml
├── Dockerfile
└── src/                # Tu znajdują się wszystkie pliki .php
    ├── index.php
    └── uploads/        # Katalog na wgrane pliki

2. Uruchomienie kontenerów
Otwórz terminal w folderze projektu i wykonaj komendę:
```
docker compose up -d --build
```
Komenda ta zbuduje obraz serwera WWW i uruchomi bazę danych w tle.

4. Nadanie uprawnień (WAŻNE)
Aby moduł File Upload działał poprawnie, musisz nadać serwerowi uprawnienia do zapisu w folderze uploads. W terminalu (Linux/macOS) wpisz:
```
chmod 777 src/uploads
```
Na Windows folder zazwyczaj ma uprawnienia zapisu domyślnie, jeśli nie – sprawdź ustawienia folderu).

🔗 Dostęp do usług
Po poprawnym uruchomieniu, laboratorium dostępne jest pod adresami:
Aplikacja Lab: http://localhost:8080
Baza danych (phpMyAdmin): http://localhost:8081
Host: db
Użytkownik: root
Hasło: root_password

📋 Przygotowanie bazy danych
Jeśli baza nie zainicjowała się automatycznie, wejdź do phpMyAdmin i wykonaj poniższy skrypt SQL w bazie lab_db:
```
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    secret_info VARCHAR(100) NOT NULL
);

INSERT INTO users (username, email, secret_info ) VALUES
('admin', 'admin@przyklad.pl', 'Hasło: Frog34'),
('janek123', 'janek@przyklad.pl', 'Lądowanie na księżycu to kłamstwo'),
('testuser', 'test@przyklad.pl', 'Matrix to film dokumentalny'),
('user2025', 'nowy@przyklad.pl', 'Nie ufaj nikomu');
```
🧹 Zarządzanie środowiskiem
Uruchomienie: 
docker compose up

Zatrzymanie laboratorium:
docker compose down

Całkowite usunięcie (wraz z danymi bazy danych): 
docker compose down --volumes --remove-orphans

Przebudowanie po zmianach w Dockerfile:
docker compose up --build -d

🔗 Dostęp do usług
Aplikacja Lab: http://localhost:8080
Baza danych (phpMyAdmin): http://localhost:8081

⚠️ Uwaga dotycząca bezpieczeństwa
To laboratorium zawiera celowo podatny kod.
Nigdy nie instaluj go na publicznym serwerze.
Używaj go wyłącznie w odizolowanym środowisku Docker.
Wszystkie techniki ataku wykonuj wyłącznie w celach edukacyjnych na tej platformie.
