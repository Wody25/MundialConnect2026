<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success'=>false, 'error'=>'No autorizado']);
    exit;
}

$usuarioLog = $_SESSION['user_id'];
$notificacion_id = intval($_POST['notificacion_id'] ?? 0);
$id_amistad = intval($_POST['id_amistad'] ?? 0);

if (!$notificacion_id || !$id_amistad) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros faltantes']);
    exit;
}

// 1. Eliminar amistad pendiente
$sql = "DELETE FROM Amistades WHERE id=? AND usuario2=?";
$params = [$id_amistad, $usuarioLog];
$stmt = sqlsrv_prepare($conexion, $sql, $params);
if (!$stmt || !sqlsrv_execute($stmt)) {
    echo json_encode(['success'=>false, 'error'=>'No se pudo rechazar la solicitud']);
    exit;
}

// 2. Marcar notificación como leída
$sql2 = "UPDATE Notificaciones SET leida=1 WHERE id=?";
$stmt2 = sqlsrv_prepare($conexion, $sql2, [$notificacion_id]);
sqlsrv_execute($stmt2);

echo json_encode(['success'=>true]);
