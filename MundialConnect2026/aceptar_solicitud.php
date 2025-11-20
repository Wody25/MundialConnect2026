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
$emisor_id = intval($_POST['emisor_id'] ?? 0);

if (!$notificacion_id || !$id_amistad || !$emisor_id) {
    echo json_encode(['success'=>false, 'error'=>'Parámetros faltantes']);
    exit;
}

// 1. Actualizar amistad a 'aceptada'
$sql = "UPDATE Amistades SET estado='aceptada' WHERE id=? AND usuario2=?";
$params = [$id_amistad, $usuarioLog];
$stmt = sqlsrv_prepare($conexion, $sql, $params);
if (!$stmt || !sqlsrv_execute($stmt)) {
    echo json_encode(['success'=>false, 'error'=>'No se pudo aceptar la solicitud']);
    exit;
}

// 2. Marcar notificación como leída
$sql2 = "UPDATE Notificaciones SET leida=1 WHERE id=?";
$stmt2 = sqlsrv_prepare($conexion, $sql2, [$notificacion_id]);
sqlsrv_execute($stmt2);

// 3. Crear notificación para el emisor
$sqlUser = "SELECT nombre FROM Usuarios WHERE id=?";
$stmtUser = sqlsrv_prepare($conexion, $sqlUser, [$usuarioLog]);
sqlsrv_execute($stmtUser);
$user = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
$nombreUsuario = $user['nombre'] ?? 'Usuario';

$mensaje = "✔ Tu solicitud de amistad ha sido aceptada por $nombreUsuario";
$sqlNotif = "INSERT INTO Notificaciones (id_usuario, tipo, mensaje) VALUES (?, 'info', ?)";
$stmtNotif = sqlsrv_prepare($conexion, $sqlNotif, [$emisor_id, $mensaje]);
sqlsrv_execute($stmtNotif);

echo json_encode(['success'=>true]);
exit;
