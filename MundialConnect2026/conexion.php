<?php
$serverName = "LAPTOP-L2AHOEJ1\\SQLEXPRESS";
$connectionOptions = [
    "Database" => "MundialConnect2026DB",
    "CharacterSet" => "UTF-8",
    "TrustServerCertificate" => true,
    // "UID" => "sa",
    // "PWD" => "tu_contraseña"
];

$conexion = sqlsrv_connect($serverName, $connectionOptions);

if (!$conexion) {
    die("❌ Error de conexión: " . print_r(sqlsrv_errors(), true));
}
?>
