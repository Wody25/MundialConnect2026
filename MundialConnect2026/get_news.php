<?php
header("Content-Type: application/json");

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://v3.football.api-sports.io/news?league=1&season=2024", 
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "x-apisports-key: 5921cc3be28bc23071d3bd8843bff0cc"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

echo $response;
?>
