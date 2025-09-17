<?php
// Conexion a SQL Server
$serverName = "YOUR_SERVER_NAME";
$database = "MundialConnect2026DB";
$username = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";

try {
    $conn = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>