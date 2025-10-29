<?php
require 'conexion.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($nombre) || empty($email) || empty($password)) {
        die('Por favor, completa todos los campos.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die('Email inválido.');
    }

    if (strlen($password) < 8) {
        die('La contraseña debe tener al menos 8 caracteres.');
    }

    // Verificar si el email ya existe
    $sqlCheck = "SELECT id FROM Usuarios WHERE email = ?";
    $paramsCheck = [$email];
    $stmtCheck = sqlsrv_prepare($conexion, $sqlCheck, $paramsCheck);

    if (!$stmtCheck || !sqlsrv_execute($stmtCheck)) {
        die("Error al verificar el email: " . print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_fetch_array($stmtCheck)) {
        die('El email ya está registrado.');
    }

    // Encriptar contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar nuevo usuario
    $sqlInsert = "INSERT INTO Usuarios (nombre, email, password_hash, fecha_registro)
                  VALUES (?, ?, ?, GETDATE())";
    $paramsInsert = [$nombre, $email, $hash];
    $stmtInsert = sqlsrv_prepare($conexion, $sqlInsert, $paramsInsert);

    if (!$stmtInsert || !sqlsrv_execute($stmtInsert)) {
        die("Error al registrar usuario: " . print_r(sqlsrv_errors(), true));
    }

    // Redirigir al login
    header("Location: index.html");
    exit;
}
?>
