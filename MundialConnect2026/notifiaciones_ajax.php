<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        ["tipo" => "info", "mensaje" => "No has iniciado sesión"]
    ]);
    exit;
}

$usuario = $_SESSION['user_id'];

$sql = "
    SELECT 
        n.id,
        n.tipo,
        n.mensaje,
        n.leida,
        a.id AS id_solicitud,
        a.usuario1,
        a.usuario2,
        a.estado
    FROM Notificaciones n
    LEFT JOIN Amistades a ON a.id = n.id_amistad
    WHERE n.id_usuario = ?
    ORDER BY n.fecha DESC
";

$stmt = sqlsrv_prepare($conexion, $sql, [$usuario]);
sqlsrv_execute($stmt);

$notificaciones = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $notificaciones[] = [
        "id" => $row['id'],
        "tipo" => $row['tipo'],
        "mensaje" => $row['mensaje'],
        "id_solicitud" => $row['id_solicitud'] ?: 0
    ];
}

// ⭐ SI NO HAY NOTIFICACIONES → ENVIAR UNA POR DEFECTO
if (count($notificaciones) === 0) {
    $notificaciones[] = [
        "tipo" => "info",
        "mensaje" => "No tienes notificaciones",
        "id_solicitud" => 0
    ];
}

echo json_encode($notificaciones);
?>
