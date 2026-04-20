<?php
// Hasło do testów
$password = "MojeSuperHaslo123";

// Trzy sposoby przechowywania
$plain_text = $password;
$md5_hash = md5($password);
$argon2_hash = password_hash($password, PASSWORD_ARGON2I);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Hashing Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 07: Przechowywanie haseł</h2>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered bg-white shadow">
                <thead class="table-dark">
                    <tr>
                        <th>Metoda</th>
                        <th>Jak to wygląda w bazie danych?</th>
                        <th>Bezpieczeństwo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-danger">
                        <td>Plain Text</td>
                        <td><code><?php echo $plain_text; ?></code></td>
                        <td><b>KRYTYCZNE</b> - Każdy, kto ma dostęp do bazy, widzi hasła.</td>
                    </tr>
                    <tr class="table-warning">
                        <td>MD5 / SHA1</td>
                        <td><code><?php echo $md5_hash; ?></code></td>
                        <td><b>SŁABE</b> - Można złamać w sekundę przez "Rainbow Tables".</td>
                    </tr>
                    <tr class="table-success">
                        <td>Argon2 / Bcrypt</td>
                        <td><code><?php echo $argon2_hash; ?></code></td>
                        <td><b>SILNE</b> - Odporne na ataki siłowe (posiada Salt).</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card p-4 mt-4 bg-dark text-white shadow">
        <h5>Misja dla ucznia: "Złam" skrót MD5</h5>
        <p>1. Skopiuj ten hash MD5: <code>76e31b667e411b22e1967262a0953a9e</code></p>
        <p>2. Wejdź na stronę typu "MD5 Decryptor" (np. <i>crackstation.net</i>).</p>
        <p>3. Wklej hash i zobacz, jak szybko system go rozpoznał.</p>
        <p>4. <b>Wniosek:</b> MD5 to nie szyfrowanie, to tylko krótki podpis, który łatwo odgadnąć.</p>
    </div>
</div>
</body>
</html>