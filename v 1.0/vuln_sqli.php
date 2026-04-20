<?php
// Połączenie z bazą
$conn = new mysqli("db", "root", "root_password", "lab_db");

$search = $_GET['search'] ?? '';
$mode = $_GET['mode'] ?? 'vulnerable';

// --- LOGIKA PODATNA (VULNERABLE) ---
if ($mode == 'vulnerable' && $search != '') {
    // KATASTROFA: Zmienna wprost w zapytaniu SQL
    $query = "SELECT username, email FROM users WHERE username = '$search'";
    $result = $conn->query($query);
}

// --- LOGIKA BEZPIECZNA (SECURE) ---
if ($mode == 'secure' && $search != '') {
    // POPRAWNIE: Prepared Statements
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $search);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>SQL Injection Lab</title>
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Laboratorium 01: SQL Injection</h2>
    <div class="row mt-4">
        <!-- Panel Testowy -->
        <div class="col-md-6">
            <div class="card p-4">
                <h5>Wyszukiwarka pracowników</h5>
                <form method="GET">
                    <input type="text" name="search" class="form-control" placeholder="Wpisz login (np. admin)">
                    <div class="mt-2">
                        <button name="mode" value="vulnerable" class="btn btn-danger">Szukaj (Tryb podatny)</button>
                        <button name="mode" value="secure" class="btn btn-success">Szukaj (Tryb bezpieczny)</button>
                    </div>
                </form>

                <div class="mt-4">
                    <h6>Wyniki:</h6>
                    <table class="table table-striped">
                        <?php if (isset($result) && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr><td><?= htmlspecialchars($row['username']) ?></td><td><?= htmlspecialchars($row['email']) ?></td></tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <div class="mt-5">
                <ol>
                    <li>  
                        Wpisz w pole loginu:
                        <code>' OR 1=1 -- </code> nie zapomnij o spacji na końcu po dwóch myślnikach
                    </li>

                    <li class="mt-2">
                        Wpisz w pole loginu:
                        <code>' UNION SELECT username, secret_info FROM users -- </code> nie zapomnij o spacji na końcu po dwóch myślnikach
                    </li>
                </ol>
                
              
            </div>
        </div>

        <!-- Panel Edukacyjny -->
        <div class="col-md-6">
            <div class="card p-3 bg-dark text-white">
                <h6>Analiza kodu:</h6>
                <?php if ($mode == 'vulnerable'): ?>
                    <small class="text-warning">Uruchomiono kod podatny:</small>
                    <code>$query = "SELECT ... WHERE user = '$search'";</code>
                    <p class="mt-2 small text-danger font-monospace">
                        Payload do testu: <strong>' OR '1'='1</strong>
                    </p>
                <?php else: ?>
                    <small class="text-success">Uruchomiono kod bezpieczny:</small>
                    <code>$stmt = $conn->prepare("SELECT ... WHERE user = ?");<br>$stmt->bind_param("s", $search);</code>
                <?php endif; ?>

               
            </div>
            
        </div>
    </div>
</div>
</body>
</html>