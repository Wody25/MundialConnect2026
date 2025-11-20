<?php
require 'conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        die("Por favor, ingresa tu email y contraseña.");
    }

    // Consulta el usuario por email
    $sql = "SELECT id, nombre, password_hash, rol FROM Usuarios WHERE email = ?";
    $params = [$email];
    $stmt = sqlsrv_prepare($conexion, $sql, $params);

    if (!$stmt) {
        die("Error al preparar la consulta: " . print_r(sqlsrv_errors(), true));
    }

    if (!sqlsrv_execute($stmt)) {
        die("Error al ejecutar la consulta: " . print_r(sqlsrv_errors(), true));
    }

    $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Verificar contraseña
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        $_SESSION['user_rol'] = $user['rol'];

        // Redirigir según rol
        if ($user['rol'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    } else {
        die("Email o contraseña incorrectos.");
    }
}
?>
