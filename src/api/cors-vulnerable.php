<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST");
header("Content-Type: application/json");

echo json_encode([
    "status" => "success",
    "user" => [
        "id" => 123,
        "username" => "janek123",
        "email" => "janek@przyklad.pl",
        "role" => "admin",
        "api_key" => "sk_live_8f3k9x2m7p0q"
    ],
    "note" => "Ta odpowiedź jest dostępna z każdej domeny (CORS Misconfiguration)"
]);