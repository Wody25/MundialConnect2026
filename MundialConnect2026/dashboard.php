<?php
session_start();
require "conexion.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_SESSION['user_id'];

/* -----------------------------------------------------
   0. BUSCADOR DE USUARIO
----------------------------------------------------- */
$busquedaUsuario = "";
$resultadosBusqueda = [];
if (isset($_GET['buscar_usuario']) && !empty(trim($_GET['buscar_usuario']))) {
    $busquedaUsuario = trim($_GET['buscar_usuario']);
    $sqlBuscar = "SELECT nombre FROM Usuarios WHERE nombre LIKE ?";
    $stmtBuscar = sqlsrv_query($conexion, $sqlBuscar, ["%$busquedaUsuario%"]);
    if ($stmtBuscar === false) {
        die("Error al buscar usuario: " . print_r(sqlsrv_errors(), true));
    }
    while ($row = sqlsrv_fetch_array($stmtBuscar, SQLSRV_FETCH_ASSOC)) {
        $resultadosBusqueda[] = $row['nombre'];
    }
}

/* -----------------------------------------------------
   1. OBTENER NOMBRE DEL USUARIO
----------------------------------------------------- */
$sqlUser = "SELECT nombre FROM Usuarios WHERE id = ?";
$stmtUser = sqlsrv_query($conexion, $sqlUser, [$id]);
if ($stmtUser === false) die("Error al obtener usuario: " . print_r(sqlsrv_errors(), true));

$user = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
$userNombre = $user ? $user['nombre'] : "Usuario";

/* -----------------------------------------------------
   2. OBTENER FAVORITOS
----------------------------------------------------- */
$sqlFav = "
    SELECT Equipos.id, Equipos.nombre
    FROM Favoritos
    INNER JOIN Equipos ON Equipos.id = Favoritos.equipo_id
    WHERE Favoritos.usuario_id = ?
";
$stmtFav = sqlsrv_query($conexion, $sqlFav, [$id]);
if ($stmtFav === false) die("Error al obtener favoritos: " . print_r(sqlsrv_errors(), true));

$favoritos = [];
while ($row = sqlsrv_fetch_array($stmtFav, SQLSRV_FETCH_ASSOC)) {
    $favoritos[] = $row;
}
$favoritos_ids = array_column($favoritos, "id");

/* -----------------------------------------------------
   3. OBTENER TODOS LOS EQUIPOS
----------------------------------------------------- */
$sqlEq = "SELECT id, nombre FROM Equipos ORDER BY nombre";
$stmtEq = sqlsrv_query($conexion, $sqlEq);
if ($stmtEq === false) die("Error al obtener equipos: " . print_r(sqlsrv_errors(), true));

$equipos = [];
while ($row = sqlsrv_fetch_array($stmtEq, SQLSRV_FETCH_ASSOC)) {
    $equipos[] = $row;
}

/* -----------------------------------------------------
   4. GENERAR HTML DINÁMICO
----------------------------------------------------- */
$listaFavoritos = "";
foreach ($favoritos as $f) {
    $listaFavoritos .= "<li class='team' data-id='{$f['id']}'>⭐ " . htmlspecialchars($f['nombre']) . "</li>";
}
if (!$listaFavoritos) $listaFavoritos = "<li>No tienes equipos favoritos aún.</li>";

$checkboxEquipos = "";
foreach ($equipos as $e) {
    $checked = in_array($e['id'], $favoritos_ids) ? "checked" : "";
    $checkboxEquipos .= "<label><input type='checkbox' name='favoritos[]' value='{$e['id']}' {$checked}>"
                        . htmlspecialchars($e['nombre']) . "</label><br>";
}

$favoritosIDs = implode(",", $favoritos_ids);

/* -----------------------------------------------------
   5. CARGAR PLANTILLA HTML
----------------------------------------------------- */
$html = file_get_contents("dashboard.html");
$html = str_replace("{{userNombre}}", $userNombre, $html);
$html = str_replace("{{listaFavoritos}}", $listaFavoritos, $html);
$html = str_replace("{{checkboxEquipos}}", $checkboxEquipos, $html);
$html = str_replace("{{favoritosIDs}}", $favoritosIDs, $html);

/* -----------------------------------------------------
   6. RESULTADOS DE BUSQUEDA
----------------------------------------------------- */
$searchHTML = "";
if (!empty($resultadosBusqueda)) {
    $searchHTML .= "<ul class='search-results'>";
    foreach ($resultadosBusqueda as $u) {
        $searchHTML .= "<li><a href='perfil.php?usuario=" . urlencode($u) . "'>" . htmlspecialchars($u) . "</a></li>";
    }
    $searchHTML .= "</ul>";
} elseif ($busquedaUsuario !== "") {
    $searchHTML = "<p>No se encontraron usuarios con '$busquedaUsuario'.</p>";
}

$html = str_replace("{{searchResults}}", $searchHTML, $html);

echo $html;
echo '<script src="https://widgets.api-sports.io/football/1.0.0/widget.js"></script>';
?>
