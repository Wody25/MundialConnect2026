<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];
$favoritos = $_POST['favoritos'] ?? [];

// --- Iniciar transacción ---
sqlsrv_query($conexion, "BEGIN TRANSACTION");

try {
    // 1️⃣ Eliminar favoritos anteriores
    $sqlDelete = "DELETE FROM Favoritos WHERE usuario_id = ?";
    $stmtDelete = sqlsrv_prepare($conexion, $sqlDelete, [$usuario_id]);
    if (!$stmtDelete || !sqlsrv_execute($stmtDelete)) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    // 2️⃣ Insertar favoritos nuevos
    if (!empty($favoritos)) {
        $sqlInsert = "INSERT INTO Favoritos (usuario_id, equipo_id) VALUES (?, ?)";
        foreach ($favoritos as $equipo_id) {
            $stmtInsert = sqlsrv_prepare($conexion, $sqlInsert, [$usuario_id, $equipo_id]);
            if (!$stmtInsert || !sqlsrv_execute($stmtInsert)) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
        }
    }

    // 3️⃣ Confirmar cambios
    sqlsrv_query($conexion, "COMMIT TRANSACTION");

    header("Location: dashboard.php");
    exit;

} catch (Exception $e) {
    sqlsrv_query($conexion, "ROLLBACK TRANSACTION");
    die("Error al guardar favoritos: " . $e->getMessage());
}
?>
