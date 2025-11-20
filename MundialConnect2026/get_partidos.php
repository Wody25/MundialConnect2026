<?php
header("Content-Type: application/json");

if (!isset($_GET['team'])) {
    echo json_encode(["error" => "Falta team"]);
    exit;
}

$team = $_GET['team'];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://v3.football.api-sports.io/fixtures?team=$team&next=5",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "x-apisports-key: 5921cc3be28bc23071d3bd8843bff0cc",
        "x-apisports-host: v3.football.api-sports.io"
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
?>
