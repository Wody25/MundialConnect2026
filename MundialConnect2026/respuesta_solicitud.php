<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['user_id'])) { exit; }

$id = intval($_GET['id']);
$aceptar = intval($_GET['aceptar']);

// Obtener info de la solicitud
$sql = "SELECT usuario1, usuario2 FROM Amistades WHERE id = ?";
$stmt = sqlsrv_prepare($conexion, $sql, [$id]);
sqlsrv_execute($stmt);
$sol = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$sol) exit;

if ($aceptar == 1) {
    // Aceptar solicitud
    $sqlA = "UPDATE Amistades SET estado = 'aceptada' WHERE id = ?";
    $stmtA = sqlsrv_prepare($conexion, $sqlA, [$id]);
    sqlsrv_execute($stmtA);
} else {
    // Rechazar solicitud
    $sqlR = "UPDATE Amistades SET estado = 'rechazada' WHERE id = ?";
    $stmtR = sqlsrv_prepare($conexion, $sqlR, [$id]);
    sqlsrv_execute($stmtR);
}

// Marcar notificación como leída
$sqlN = "UPDATE Notificaciones SET leida = 1 WHERE id_amistad = ?";
$stmtN = sqlsrv_prepare($conexion, $sqlN, [$id]);
sqlsrv_execute($stmtN);

echo "OK";
?>
