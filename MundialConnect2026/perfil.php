<?php
session_start();
require_once "conexion.php";

// --- Verificar si el usuario está logueado ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// --- Determinar qué perfil mostrar ---
if (isset($_GET['usuario']) && !empty(trim($_GET['usuario']))) {
    $nombreBuscado = trim($_GET['usuario']);
    $sqlUser = "SELECT id, nombre, email FROM Usuarios WHERE nombre = ?";
    $stmtUser = sqlsrv_prepare($conexion, $sqlUser, [$nombreBuscado]);

    if (!$stmtUser || !sqlsrv_execute($stmtUser)) {
        die(print_r(sqlsrv_errors(), true));
    }

    $perfil = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
    if (!$perfil) die("Usuario no encontrado");

    $usuario_id = $perfil['id'];
} else {
    $usuario_id = $_SESSION['user_id'];
    $sqlPerfil = "SELECT nombre, email FROM Usuarios WHERE id = ?";
    $stmtPerfil = sqlsrv_prepare($conexion, $sqlPerfil, [$usuario_id]);
    if (!$stmtPerfil || !sqlsrv_execute($stmtPerfil)) die(print_r(sqlsrv_errors(), true));
    $perfil = sqlsrv_fetch_array($stmtPerfil, SQLSRV_FETCH_ASSOC);
}

// --- Equipos favoritos del usuario ---
$sqlFavoritos = "
    SELECT e.id, e.nombre
    FROM Favoritos f
    INNER JOIN Equipos e ON e.id = f.equipo_id
    WHERE f.usuario_id = ?
";
$stmtFav = sqlsrv_prepare($conexion, $sqlFavoritos, [$usuario_id]);
if (!$stmtFav || !sqlsrv_execute($stmtFav)) die(print_r(sqlsrv_errors(), true));

$favoritos = [];
$fav_ids_usuario = [];
while ($row = sqlsrv_fetch_array($stmtFav, SQLSRV_FETCH_ASSOC)) {
    $favoritos[] = $row['nombre'];
    $fav_ids_usuario[] = $row['id'];
}

// --- Amigos del usuario ---
$idsAmigos = [];
$sqlAmigosIds = "
    SELECT CASE WHEN usuario1 = ? THEN usuario2 ELSE usuario1 END AS amigo_id
    FROM Amistades
    WHERE (usuario1 = ? OR usuario2 = ?) AND estado='aceptada'
";
$stmtAmigosIds = sqlsrv_prepare($conexion, $sqlAmigosIds, [$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
sqlsrv_execute($stmtAmigosIds);
while ($row = sqlsrv_fetch_array($stmtAmigosIds, SQLSRV_FETCH_ASSOC)) {
    $idsAmigos[] = $row['amigo_id'];
}

// --- Equipos favoritos de los amigos ---
$equiposAmigos = [];
if (count($idsAmigos) > 0) {
    $placeholders = implode(',', array_fill(0, count($idsAmigos), '?'));
    $stmtFavAmigos = sqlsrv_prepare(
        $conexion,
        "SELECT DISTINCT f.equipo_id, e.nombre FROM Favoritos f INNER JOIN Equipos e ON f.equipo_id = e.id WHERE f.usuario_id IN ($placeholders)",
        $idsAmigos
    );
    sqlsrv_execute($stmtFavAmigos);

    while ($row = sqlsrv_fetch_array($stmtFavAmigos, SQLSRV_FETCH_ASSOC)) {
        $equiposAmigos[$row['equipo_id']] = $row['nombre'];
    }
}

// --- Validar amistad / solicitud ---
$esMismoUsuario = ($_SESSION['user_id'] == $usuario_id);

$sqlAmigos = "
    SELECT * FROM Amistades 
    WHERE ((usuario1 = ? AND usuario2 = ?) OR (usuario1 = ? AND usuario2 = ?))
      AND estado = 'aceptada'
";
$stmtAmigos = sqlsrv_prepare($conexion, $sqlAmigos, [$_SESSION['user_id'], $usuario_id, $usuario_id, $_SESSION['user_id']]);
sqlsrv_execute($stmtAmigos);
$sonAmigos = sqlsrv_fetch_array($stmtAmigos);

$sqlPend = "
    SELECT * FROM Amistades 
    WHERE ((usuario1 = ? AND usuario2 = ?) OR (usuario1 = ? AND usuario2 = ?))
      AND estado = 'pendiente'
";
$stmtPend = sqlsrv_prepare($conexion, $sqlPend, [$_SESSION['user_id'], $usuario_id, $usuario_id, $_SESSION['user_id']]);
sqlsrv_execute($stmtPend);
$solicitudPendiente = sqlsrv_fetch_array($stmtPend);

// --- Lista de amigos ---
$amigos = [];
$sqlListaAmigos = "
    SELECT u.nombre
    FROM Amistades a
    INNER JOIN Usuarios u ON (u.id = CASE WHEN a.usuario1 = ? THEN a.usuario2 ELSE a.usuario1 END)
    WHERE (a.usuario1 = ? OR a.usuario2 = ?) AND a.estado='aceptada'
";
$stmtLista = sqlsrv_prepare($conexion, $sqlListaAmigos, [$usuario_id, $usuario_id, $usuario_id]);
sqlsrv_execute($stmtLista);
while ($row = sqlsrv_fetch_array($stmtLista, SQLSRV_FETCH_ASSOC)) {
    $amigos[] = $row['nombre'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Perfil de Usuario - MundialConnect 2026</title>
<link rel="icon" type="image/x-icon" href="img/fifa.ico">
<link rel="stylesheet" href="styles.css">
<style>
.perfil-layout { display: flex; flex-wrap: wrap; gap: 20px; }
.perfil-main { flex: 3; }
.perfil-side { flex: 1; background: #f7f7f7; border-radius: 10px; padding: 15px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
.perfil-side h3 { margin-top: 0; color: #ce1126; }
.amigos-list, .news-list, .favorites { list-style: none; padding-left: 0; }
.amigos-list li, .news-list li { padding: 10px; border-bottom: 1px solid #ddd; }
.amigos-list li:last-child, .news-list li:last-child { border-bottom: none; }
.btn-enviar { background-color: #007bff; color: white; border: none; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
.btn-enviar:hover { background-color: #0056b3; }
.btn-disabled { background-color: #ccc; color: #555; cursor: not-allowed; border: none; padding: 8px 12px; border-radius: 6px; }
.news-list li strong { color: #00205B; }
.news-list li small { color: #666; font-size: 0.85em; }
</style>
</head>
<body class="bg">

<div class="topbar">
  <div class="container">
    <h2>MundialConnect 2026</h2>
    <div>
      <a href="dashboard.php">🏠 Dashboard</a> |
      <a href="logout.php">🚪 Cerrar sesión</a>
    </div>
  </div>
</div>

<div class="container">
  <div class="card">
    <h2>👤 Perfil de Usuario</h2>

    <div class="perfil-layout">
      <div class="perfil-main">
        <p><strong>Nombre:</strong> <?= htmlspecialchars($perfil['nombre']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($perfil['email']) ?></p>

        <h3>⭐ Equipos Favoritos</h3>
        <?php if (count($favoritos) > 0): ?>
            <ul class="favorites">
                <?php foreach ($favoritos as $equipo): ?>
                    <li>⚽ <?= htmlspecialchars($equipo) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No hay equipos favoritos.</p>
        <?php endif; ?>

        <button onclick="window.location.href='dashboard.php'">⬅ Volver al Dashboard</button>
      </div>

      <div class="perfil-side">
        <h3>👥 Amigos</h3>
        <?php if (count($amigos) > 0): ?>
            <ul class="amigos-list">
                <?php foreach ($amigos as $amigo): ?>
                    <li><?= htmlspecialchars($amigo) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="small">Aún no hay amigos agregados.</p>
        <?php endif; ?>

        <hr>

        <?php if ($esMismoUsuario): ?>
            <button class="btn-disabled" disabled>Este es tu perfil</button>
        <?php elseif ($sonAmigos): ?>
            <button class="btn-disabled" disabled>✔ Ya son amigos</button>
        <?php elseif ($solicitudPendiente): ?>
            <button class="btn-disabled" disabled>⏳ Solicitud enviada</button>
        <?php else: ?>
            <button id="btnSolicitud" class="btn-enviar" data-receptor="<?= $usuario_id ?>">
                ➕ Enviar solicitud de amistad
            </button>
        <?php endif; ?>
      </div>
    </div>

    <h3>📰 Noticias de los equipos favoritos de tus amigos</h3>
    <ul id="noticiasAmigos" class="news-list">
        <li>Cargando noticias...</li>
    </ul>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const listaNoticias = document.getElementById("noticiasAmigos");
    const equiposAmigos = <?= json_encode(array_keys($equiposAmigos)) ?>;

    if (equiposAmigos.length === 0) {
        listaNoticias.innerHTML = "<li>No hay noticias de los equipos de tus amigos por el momento.</li>";
        return;
    }

    const url = `get_news_fake.php?teams=${equiposAmigos.join(",")}`;

    fetch(url)
        .then(res => res.json())
        .then(data => {
            listaNoticias.innerHTML = "";
            if (!data.response || data.response.length === 0) {
                listaNoticias.innerHTML = "<li>No hay noticias de los equipos de tus amigos por el momento.</li>";
                return;
            }

            data.response.forEach(noticia => {
                const li = document.createElement("li");
                li.innerHTML = `
                    <strong>${noticia.title}</strong><br>
                    <p>${noticia.description}</p>
                    <a href="${noticia.url}" target="_blank">Leer más</a>
                `;
                listaNoticias.appendChild(li);
            });
        })
        .catch(err => {
            listaNoticias.innerHTML = `<li>Error al cargar noticias: ${err}</li>`;
        });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btnSolicitud");
    if (!btn) return;

    btn.addEventListener("click", async () => {
        const receptorId = btn.getAttribute("data-receptor");
        btn.disabled = true;
        btn.textContent = "⏳ Enviando...";

        try {
            const formData = new FormData();
            formData.append("receptor_id", receptorId);

            const res = await fetch("enviar_solicitud.php", {
                method: "POST",
                body: formData,
                credentials: "same-origin"
            });

            const data = await res.text();

            if (res.ok) {
                btn.textContent = "⏳ Solicitud enviada";
                btn.classList.remove("btn-enviar");
                btn.classList.add("btn-disabled");
            } else {
                btn.disabled = false;
                btn.textContent = "➕ Enviar solicitud de amistad";
                alert("Error al enviar solicitud: " + data);
            }
        } catch (err) {
            btn.disabled = false;
            btn.textContent = "➕ Enviar solicitud de amistad";
            alert("Error en la solicitud: " + err);
        }
    });
});
</script>

</body>
</html>
