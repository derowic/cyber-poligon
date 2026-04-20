<?php
$allowed_origins = [
    'http://localhost:8080',
    'https://twoja-strona.pl',
    'https://app.twoja-strona.pl'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: null");
}

header("Access-Control-Allow-Methods: GET, POST");
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => 123,
        "username" => "janek123",
        "email" => "janek@przyklad.pl"
    ],
    "note" => "Bezpieczna konfiguracja CORS"
]);