<?php
session_start();
require_once "conexion.php";

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// --- Obtener datos del perfil ---
$sqlPerfil = "SELECT nombre, email, fecha_nacimiento, pais FROM Usuarios WHERE id = ?";
$paramsPerfil = [$usuario_id];
$stmtPerfil = sqlsrv_prepare($conexion, $sqlPerfil, $paramsPerfil);

if (!$stmtPerfil || !sqlsrv_execute($stmtPerfil)) {
    die(print_r(sqlsrv_errors(), true));
}

$perfil = sqlsrv_fetch_array($stmtPerfil, SQLSRV_FETCH_ASSOC);

// --- Obtener equipos favoritos ---
$sqlFavoritos = "
    SELECT e.nombre
    FROM Favoritos f
    INNER JOIN Equipos e ON e.id = f.equipo_id
    WHERE f.usuario_id = ?
";
$paramsFav = [$usuario_id];
$stmtFav = sqlsrv_prepare($conexion, $sqlFavoritos, $paramsFav);

if (!$stmtFav || !sqlsrv_execute($stmtFav)) {
    die(print_r(sqlsrv_errors(), true));
}

$favoritos = [];
while ($row = sqlsrv_fetch_array($stmtFav, SQLSRV_FETCH_ASSOC)) {
    $favoritos[] = $row['nombre'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil de Usuario - MundialConnect 2026</title>
<link rel="stylesheet" href="styles.css">
<style>
body { font-family: Arial, sans-serif; background-color:#f4f4f4; margin:0; padding:0; }
.container { max-width: 800px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 8px; }
h2 { color: #333; }
.profile-section { margin-bottom: 20px; }
ul { list-style: disc; margin-left: 20px; }
button { padding: 10px 15px; border: none; border-radius: 5px; background-color:#007BFF; color:#fff; cursor:pointer; }
button:hover { background-color:#0056b3; }
</style>
</head>
<body>

<div class="container">
    <h2>Perfil de Usuario</h2>

    <div class="profile-section">
        <strong>Nombre:</strong> <?= htmlspecialchars($perfil['nombre']) ?><br>
        <strong>Email:</strong> <?= htmlspecialchars($perfil['email']) ?><br>
        <strong>Fecha de nacimiento:</strong> <?= isset($perfil['fecha_nacimiento']) ? $perfil['fecha_nacimiento']->format('d/m/Y') : 'No registrado' ?><br>
        <strong>País:</strong> <?= htmlspecialchars($perfil['pais'] ?? 'No registrado') ?><br>
    </div>

    <div class="profile-section">
        <h3>Mis equipos favoritos</h3>
        <?php if (count($favoritos) > 0): ?>
            <ul>
                <?php foreach ($favoritos as $equipo): ?>
                    <li>⭐ <?= htmlspecialchars($equipo) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No has seleccionado equipos favoritos.</p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <button onclick="window.location.href='dashboard.php'">Volver al Dashboard</button>
    </div>
</div>

</body>
</html>
