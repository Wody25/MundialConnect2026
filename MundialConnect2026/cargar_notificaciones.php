<?php
session_start();
require_once "conexion.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

$idReceptor = $_SESSION['user_id'];

/*
  Traemos las últimas 20 notificaciones dirigidas al usuario receptor.
  Incluimos el nombre del emisor y el id_amistad si aplica.
*/
$sql = "
    SELECT N.id, N.id_usuario AS emisor_id, U.nombre AS emisor_nombre,
           N.tipo, N.mensaje, N.fecha, N.leida, N.id_amistad
    FROM Notificaciones N
    LEFT JOIN Usuarios U ON U.id = N.id_usuario
    WHERE N.id_receptor = ?
    ORDER BY N.fecha DESC
    OFFSET 0 ROWS FETCH NEXT 50 ROWS ONLY
";

$params = [$idReceptor];
$stmt = sqlsrv_prepare($conexion, $sql, $params);

if (!$stmt || !sqlsrv_execute($stmt)) {
    $err = sqlsrv_errors();
    echo json_encode(['success' => false, 'error' => $err]);
    exit;
}

$notifs = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Convertir DATETIME a string legible
    $fecha = $row['fecha'] instanceof DateTime ? $row['fecha']->format('Y-m-d H:i:s') : $row['fecha'];

    $notifs[] = [
        'id' => (int)$row['id'],
        'emisor_id' => (int)$row['emisor_id'],
        'emisor_nombre' => $row['emisor_nombre'],
        'tipo' => $row['tipo'],
        'mensaje' => $row['mensaje'],
        'fecha' => $fecha,
        'leida' => (int)$row['leida'],
        'id_amistad' => isset($row['id_amistad']) ? (int)$row['id_amistad'] : null
    ];
}

echo json_encode(['success' => true, 'notifications' => $notifs]);
