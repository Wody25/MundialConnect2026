<?php
require 'conexion.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password)<6){
        die('Email inválido o contraseña muy corta.');
    }

    $stmt = $conn->prepare("SELECT id FROM Usuarios WHERE email=?");
    $stmt->execute([$email]);
    if($stmt->fetch()){
        die('El email ya está registrado.');
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO Usuarios (nombre,email,password_hash,fecha_registro) VALUES (?,?,?,GETDATE())");
    $stmt->execute([$nombre,$email,$hash]);
    header("Location: index.html");
    exit;
}
?>