<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['user_id'])) {
    exit("No autorizado");
}

$emisor = $_SESSION['user_id'];
$receptor = intval($_POST['receptor_id']);

if ($emisor == $receptor) {
    exit("No puedes enviarte solicitud a ti mismo.");
}

// Verificar si ya existe una solicitud o amistad
$sqlCheck = "SELECT id FROM Amistades 
             WHERE (usuario1 = ? AND usuario2 = ?) 
                OR (usuario1 = ? AND usuario2 = ?)";
$paramsCheck = [$emisor, $receptor, $receptor, $emisor];
$stmtCheck = sqlsrv_query($conexion, $sqlCheck, $paramsCheck);

if ($row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC)) {
    exit("Ya existe una solicitud o amistad.");
}

// Insertar solicitud y obtener ID
$sql = "INSERT INTO Amistades (usuario1, usuario2, estado) 
        OUTPUT INSERTED.id 
        VALUES (?, ?, 'pendiente')";
$params = [$emisor, $receptor];
$stmt = sqlsrv_query($conexion, $sql, $params);
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$idAmistad = $row['id'];

// Obtener nombre del emisor
$sqlUser = "SELECT nombre FROM Usuarios WHERE id = ?";
$stmtUser = sqlsrv_query($conexion, $sqlUser, [$emisor]);
$userData = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
$nombreEmisor = $userData['nombre'];

// Crear notificación
$sqlNotif = "
    INSERT INTO Notificaciones (id_usuario, id_receptor, tipo, mensaje, id_amistad)
    VALUES (?, ?, 'solicitud_amistad', ?, ?)
";
$mensaje = "📩 Tienes una solicitud de amistad de: " . $nombreEmisor;
$paramsNotif = [$emisor, $receptor, $mensaje, $idAmistad];
sqlsrv_query($conexion, $sqlNotif, $paramsNotif);

// Redirigir al perfil usando usuario_id
header("Location: perfil.php?usuario_id=" . $receptor);
exit;
?>
