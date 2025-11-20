<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] ?? '') !== 'admin') {
    echo json_encode(['type'=>'error','msg'=>'No tienes permisos']);
    exit;
}

require 'conexion.php';

$nombre = trim($_POST['nombre'] ?? '');
$grupo = trim($_POST['grupo'] ?? '');
$pais_bandera = trim($_POST['pais_bandera'] ?? '');

if (empty($nombre)) {
    echo json_encode(['type'=>'error','msg'=>'❌ Debes ingresar el nombre del equipo']);
    exit;
}

// Verificar duplicado ignorando mayúsculas, minúsculas y acentos
$sqlCheck = "SELECT COUNT(*) AS total 
             FROM Equipos 
             WHERE nombre COLLATE Latin1_General_CI_AI = ?";
$paramsCheck = [$nombre];
$options = ["Scrollable" => SQLSRV_CURSOR_KEYSET];
$stmtCheck = sqlsrv_query($conexion, $sqlCheck, $paramsCheck, $options);

if ($stmtCheck === false) {
    echo json_encode(['type'=>'error','msg'=>'❌ Error al verificar duplicados']);
    exit;
}

$rowCheck = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC);
if ($rowCheck['total'] > 0) {
    echo json_encode(['type'=>'error','msg'=>"❌ El equipo '$nombre' ya existe"]);
    exit;
}

// Insertar equipo
$sqlInsert = "INSERT INTO Equipos (nombre, grupo, pais_bandera) VALUES (?, ?, ?)";
$paramsInsert = [$nombre, $grupo ?: null, $pais_bandera ?: null];
$stmtInsert = sqlsrv_query($conexion, $sqlInsert, $paramsInsert);

if ($stmtInsert) {
    $resTotal = sqlsrv_query($conexion, "SELECT COUNT(*) AS total FROM Equipos");
    $total = sqlsrv_fetch_array($resTotal, SQLSRV_FETCH_ASSOC)['total'];

    echo json_encode(['type'=>'success','msg'=>"✅ Equipo '$nombre' agregado correctamente", "totalEquipos"=>$total]);
} else {
    echo json_encode(['type'=>'error','msg'=>'❌ Error al agregar el equipo']);
}

sqlsrv_close($conexion);
?>
