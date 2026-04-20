<?php
// Clickjacking Demo
$mode = $_GET['mode'] ?? 'vulnerable';
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratorium: Clickjacking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .vuln-card { border-left: 6px solid #dc3545; }
        .safe-card { border-left: 6px solid #198754; }
        
        /* Stylizacja "ataku" */
        .attack-container {
            position: relative;
            width: 100%;
            height: 350px;
            border: 2px dashed #ccc;
            background: #fff;
            overflow: hidden;
        }

        /* Warstwa 1: Prawdziwa strona (ofiara) - ukryta pod spodem */
        .victim-frame {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
            z-index: 1;
            /* W prawdziwym ataku opacity byłoby bliskie 0 */
            opacity: 0.3; 
            transition: opacity 0.3s;
        }

        .attack-container:hover .victim-frame {
            opacity: 1; /* Pokazujemy ofierze co jest pod spodem po najechaniu dla nauki */
        }

        /* Warstwa 2: Przynęta atakującego - na wierzchu */
        .attacker-ui {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 2;
            pointer-events: none; /* Kliknięcia przechodzą PRZEZ tę warstwę do iframe! */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            background: rgba(255, 255, 255, 0.7);
        }

        .btn-fake {
            padding: 15px 30px;
            font-size: 1.5rem;
            pointer-events: none;
        }

        code { color: #d63384; }
    </style>
</head>
<body class="bg-light">
<div class="container py-5">

    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="display-5 fw-bold">Laboratorium: Clickjacking</h1>
            <p class="lead">Zrozum, jak hakerzy przejmują Twoje kliknięcia (UI Redressing).</p>
            <div class="btn-group">
                <a href="?mode=vulnerable" class="btn <?= $mode == 'vulnerable' ? 'btn-danger' : 'btn-outline-danger' ?>">Tryb podatny</a>
                <a href="?mode=safe" class="btn <?= $mode == 'safe' ? 'btn-success' : 'btn-outline-success' ?>">Tryb bezpieczny</a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- LEWA KOLUMNA: DEMO -->
        <div class="col-lg-7">
            <div class="card shadow <?= $mode == 'safe' ? 'safe-card' : 'vuln-card' ?>">
                <div class="card-header bg-white fw-bold">
                    Podgląd ataku (Wizualizacja)
                </div>
                <div class="card-body text-center">
                    <p class="small text-muted">
                        <?php if ($mode == 'vulnerable'): ?>
                            <i class="bi bi-exclamaion-triangle-fill text-danger"></i> 
                            <strong>Tryb podatny:</strong> Iframe ładuje się poprawnie. Najedź myszką na ramkę, aby zobaczyć "ukrytą" stronę.
                        <?php else: ?>
                            <i class="bi bi-shield-check text-success"></i> 
                            <strong>Tryb bezpieczny:</strong> Przeglądarka powinna zablokować ładowanie ramki.
                        <?php endif; ?>
                    </p>

                    <div class="attack-container shadow-sm rounded">
                        <!-- IFRAME (OFIARA) -->
                        <iframe src="clickjacking-victim.php?mode=<?= $mode ?>" class="victim-frame"></iframe>
                        
                        <!-- UI ATAKUJĄCEGO -->
                        <div class="attacker-ui">
                            <h3 class="text-danger fw-bold">GRATULACJE!</h3>
                            <p>Jesteś 1,000,000 odwiedzającym!</p>
                            <button class="btn btn-warning btn-fake shadow-lg">ODBIERZ DARMOWEGO IPHONE'A</button>
                            <p class="mt-3 text-muted small">(Twoje kliknięcie przejdzie do przycisku pod spodem)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PRAWA KOLUMNA: ANALIZA -->
        <div class="col-lg-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-dark text-white">Analiza techniczna</div>
                <div class="card-body">
                    <h6>Co widzi użytkownik?</h6>
                    <p class="small">Kolorowy przycisk "Odbierz iPhone".</p>
                    
                    <h6>Co widzi przeglądarka?</h6>
                    <p class="small">Dwie warstwy. Warstwa wierzchnia ma parametr <code>pointer-events: none</code>, co oznacza, że jest "duchem". Kliknięcie trafia w niewidoczną ramkę <code>iframe</code> pod spodem.</p>

                    <hr>

                    <h6>Zabezpieczenie PHP:</h6>
                    <?php if ($mode == 'vulnerable'): ?>
                        <div class="alert alert-warning py-2 small">Brak nagłówków ochronnych. Strona pozwala na osadzanie w ramkach.</div>
                        <pre class="bg-light p-2 border small"><code>// Brak kodu zabezpieczającego</code></pre>
                    <?php else: ?>
                        <div class="alert alert-success py-2 small">Serwer wysyła instrukcje blokujące ramki.</div>
                        <pre class="bg-light p-2 border small"><code>header("X-Frame-Options: DENY");
header("Content-Security-Policy: frame-ancestors 'none'");</code></pre>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm border-info">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="bi bi-info-circle"></i> Zadanie dla ucznia</h5>
                    <ul class="small ps-3">
                        <li>Sprawdź w <b>Trybie podatnym</b>, co się stanie po kliknięciu w żółty przycisk.</li>
                        <li>Uruchom <b>Narzędzia Deweloperskie (F12)</b>, przejdź do zakładki "Sieć" (Network) i odśwież stronę.</li>
                        <li>Kliknij na plik <code>clickjacking-victim.php</code> i poszukaj nagłówka <code>X-Frame-Options</code>.</li>
                        <li>Zastanów się: dlaczego w trybie bezpiecznym ramka jest pusta lub wyświetla błąd?</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- DOLNA SEKCJA: TEORIA -->
    <div class="row mt-5">
        <div class="col-md-4">
            <h5><i class="bi bi-layers"></i> Dlaczego to działa?</h5>
            <p class="small text-muted">Przeglądarki domyślnie pozwalają na osadzanie stron w <code>&lt;iframe&gt;</code>. Atakujący używa CSS, aby uczynić ramkę niewidoczną (<code>opacity: 0</code>), ale wciąż aktywną do kliknięć.</p>
        </div>
        <div class="col-md-4">
            <h5><i class="bi bi-shield-lock"></i> Jak się bronić?</h5>
            <p class="small text-muted">Należy nakazać przeglądarce, aby nie wyświetlała strony, jeśli próbuje ona być załadowana w ramce. Służą do tego nagłówki <b>X-Frame-Options</b> oraz nowocześniejszy <b>Content-Security-Policy</b>.</p>
        </div>
        <div class="col-md-4">
            <h5><i class="bi bi-exclamation-octagon"></i> Skutki ataku</h5>
            <p class="small text-muted">Nieświadoma zmiana hasła, usunięcie konta, polubienie postu na Facebooku, lub wykonanie przelewu bankowego (tzw. One-Click Attack).</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>