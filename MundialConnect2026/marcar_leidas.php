<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$idReceptor = $_SESSION['user_id'];

/* Actualizar solo si hay notificaciones no leídas */
$sql = "UPDATE Notificaciones SET leida = 1 WHERE id_receptor = ? AND leida = 0";
$params = [$idReceptor];
$stmt = sqlsrv_prepare($conexion, $sql, $params);

if (!$stmt || !sqlsrv_execute($stmt)) {
    $err = sqlsrv_errors();
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
}

echo json_encode(['success' => true]);
