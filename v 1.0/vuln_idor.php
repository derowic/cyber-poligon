<?php
$conn = new mysqli("db", "root", "root_password", "lab_db");
$user_id = $_GET['id'] ?? 1;
$mode = $_GET['mode'] ?? 'vulnerable';

// Symulacja zalogowanego użytkownika - udajemy, że jesteśmy użytkownikiem o ID = 2 (Jan Kowalski)
$logged_in_user_id = 2; 

// --- LOGIKA PODATNA ---
if ($mode == 'vulnerable') {
    // Pobieramy dane DOWOLNEGO użytkownika tylko na podstawie ID z adresu URL
    $query = "SELECT username, email, secret_info FROM users WHERE id = $user_id";
    $result = $conn->query($query);
}

// --- LOGIKA BEZPIECZNA ---
if ($mode == 'secure') {
    // Sprawdzamy, czy żądane ID jest zgodne z ID zalogowanego użytkownika
    if ($user_id != $logged_in_user_id) {
        $error = "BŁĄD: Nie masz uprawnień do przeglądania tego profilu!";
    } else {
        $stmt = $conn->prepare("SELECT username, email, secret_info FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }
}
$user = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>IDOR Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <a href="index.php" class="btn btn-secondary mb-3">← Powrót</a>
    <h2>Laboratorium 04: IDOR (Błędna autoryzacja)</h2>
    
    <div class="alert alert-warning shadow-sm">
        <strong>Status sesji:</strong> Jesteś zalogowany jako użytkownik o <b>ID: 2 (jan_kowalski)</b>.
    </div>

    <div class="card p-4 shadow">
        <h5>Mój Profil (ID: <?php echo htmlspecialchars($user_id); ?>)</h5>
        <hr>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif ($user): ?>
            <p><strong>Nazwa użytkownika:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p class="text-danger"><strong>Tajne informacje:</strong> <?php echo htmlspecialchars($user['secret_info']); ?></p>
        <?php else: ?>
            <p>Użytkownik nie istnieje.</p>
        <?php endif; ?>
    </div>

    <div class="mt-4">
        <a href="?id=2&mode=<?php echo $mode; ?>" class="btn btn-primary">Mój profil (ID: 2)</a>
        <a href="?id=1&mode=vulnerable" class="btn btn-danger">Profil Admina (ID: 1) - Atak!</a>
    </div>

    <div class="mt-5 border-top pt-3">
        <h5>Misja dla ucznia:</h5>
        <ol>
            <li>Jesteś użytkownikiem nr 2. Kliknij w przycisk "Mój profil".</li>
            <li>Zmień w adresie URL <code>id=2</code> na <code>id=1</code>. Co widzisz?</li>
            <li>Przełącz tryb na "Secure" i spróbuj ponownie wejść na ID: 1.</li>
        </ol>
    </div>
</div>
</body>
</html>