<?php
session_start();
require_once "conexion.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// --- Obtener equipos ---
$sqlEquipos = "SELECT id, nombre FROM Equipos ORDER BY nombre";
$stmtEquipos = sqlsrv_query($conexion, $sqlEquipos);
if ($stmtEquipos === false) {
    die(print_r(sqlsrv_errors(), true));
}

$equipos = [];
while ($row = sqlsrv_fetch_array($stmtEquipos, SQLSRV_FETCH_ASSOC)) {
    $equipos[] = $row;
}

// --- Obtener favoritos del usuario ---
$sqlFavoritos = "
    SELECT e.id, e.nombre
    FROM Favoritos f
    INNER JOIN Equipos e ON e.id = f.equipo_id
    WHERE f.usuario_id = ?
";
$paramsFavoritos = [$usuario_id];
$stmtFavoritos = sqlsrv_prepare($conexion, $sqlFavoritos, $paramsFavoritos);
if (!$stmtFavoritos || !sqlsrv_execute($stmtFavoritos)) {
    die(print_r(sqlsrv_errors(), true));
}

$favoritos = [];
while ($row = sqlsrv_fetch_array($stmtFavoritos, SQLSRV_FETCH_ASSOC)) {
    $favoritos[] = $row;
}
$favoritos_ids = array_column($favoritos, 'id');

// --- Obtener nombre del usuario ---
$sqlUser = "SELECT nombre FROM Usuarios WHERE id = ?";
$paramsUser = [$usuario_id];
$stmtUser = sqlsrv_prepare($conexion, $sqlUser, $paramsUser);
if (!$stmtUser || !sqlsrv_execute($stmtUser)) {
    die(print_r(sqlsrv_errors(), true));
}

$userRow = sqlsrv_fetch_array($stmtUser, SQLSRV_FETCH_ASSOC);
$userNombre = $userRow['nombre'] ?? 'Usuario';
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>MundialConnect 2026 - Dashboard</title>

<link rel="icon" type="image/x-icon" href="img/fifa.ico">
<link rel="stylesheet" href="styles.css">

<style>

/* 🏆 Encabezado Mundial 2026 */
.topbar {
    background: linear-gradient(90deg, #00205B, #D9001D, #000000);
    padding: 18px 10px;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.35);
    border-bottom: 4px solid #ffffff;
    position: relative;
    z-index: 10;
}

.topbar h2 {
    margin: 0;
    font-weight: bold;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    letter-spacing: 1px;
    font-size: 26px;
}

/* 🔔 Notificaciones */
.notif-icon {
    position: relative;
    cursor: pointer;
    font-size: 28px;
    color: #FFD700;
    transition: 0.2s;
    z-index: 20;
}
.notif-icon:hover { transform: scale(1.1); }

.notif-count {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #D9001D;
    border: 2px solid white;
    color: white;
    padding: 2px 6px;
    border-radius: 50%;
    font-weight: bold;
    font-size: 12px;
}

header {
    position: relative;
    overflow: visible !important;   /* IMPORTANTE */
    z-index: 1;
}


.notif-menu {
    display: none;
    position: absolute;
    top: 40px;       /* ← Agregado */
    right: 0;
    width: 280px;
    background: white;
    border-radius: 10px;
    border: 2px solid #00205B;
    z-index: 9999 !important;
}

.notif-menu.open {
    display: block;
}


.notif-item {
    padding: 12px;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
    color: #00205B;
}

.notif-actions button {
    padding: 6px 10px;
    border: none;
    border-radius: 5px;
    font-weight: bold;
    font-size: 12px;
    cursor: pointer;
}
.notif-actions button:first-child {
    background: #28a745;
    color: white;
}
.notif-actions button:last-child {
    background: #D9001D;
    color: white;
}
/* Fix: asegurar que el menú se posicione desde el contenedor correcto */
.notif-wrapper {
    position: relative;
}


/* 🎌 Banderas — SIN fondo negro */
.flags-marquee {
    width: 100%;
    overflow: hidden;
    height: 60px;
    display: flex;
    align-items: center;
    background: transparent !important;
}
.flags-slide {
    display: flex;
    gap: 20px;
    animation: marqueeFlags 15s linear infinite;
}
.flags-slide img { height: 40px; }

@keyframes marqueeFlags {
  0%   { transform: translateX(100%); }
  100% { transform: translateX(-50%); }
}

/* Texto scrolling */
.marquee-content {
    display: flex;
    gap: 50px;
    white-space: nowrap;
    font-weight: bold;
    font-size: 18px;
    color: white;
    animation: marqueeText 15s linear infinite;
}
@keyframes marqueeText {
  0%   { transform: translateX(100%); }
  100% { transform: translateX(-50%); }
}

/* Botones */
.perfil-btn {
    background: #00205B;
    color: #fff;
    border: 2px solid #fff;
    padding: 7px 12px;
    border-radius: 8px;
    font-weight: bold;
    transition: 0.2s;
}
.perfil-btn:hover { transform: scale(1.05); background: #00163F; }

.logout-btn {
    background: #D9001D;
    border: 2px solid white;
    color: white !important;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: bold;
    text-decoration: none;
    transition: 0.2s;
}
.logout-btn:hover { background: #FE1122; }

/* Tarjetas */
.card h3 {
    background: linear-gradient(90deg,#00205B,#D9001D);
    color: white;
    padding: 8px;
    border-radius: 5px;
}

/* Botón submit */
button[type="submit"] {
    background-color: #00205B !important;
    color: white !important;
    border: 2px solid #D9001D !important;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: bold;
}
button[type="submit"]:hover {
    background-color: #00163F !important;
}

/* Quita la línea azul debajo del texto de bienvenida */
.header-right,
.header-right * {
    border-bottom: none !important;
    box-shadow: none !important;
}

.header-right::after,
.header-right::before {
    content: none !important;
}

/* Evita que se corte el menú y elimina la línea rara */
.topbar .container {
    position: relative !important;
    overflow: visible !important;
}

.notif-wrapper {
    position: relative !important;
}

.notif-menu {
    z-index: 99999 !important;
    position: absolute;
    top: 45px;
    right: 0;
}

.notif-icon-container {
    margin-right: 40px !important;  /* 👉 Ajusta este número para moverla más */
}

</style>
</head>

<body class="bg">

<header class="topbar">
<div class="container" style="display:flex; justify-content: space-between; align-items:center;">


    <h2>🏆 MundialConnect 2026</h2>

<div class="notif-wrapper" style="display:flex; align-items:center; gap:15px; margin-left: auto;">

        <!-- 🔔 ÍCONO DE NOTIFICACIONES SIEMPRE VISIBLE -->
      <div class="notif-icon-container">
    <div class="notif-icon" onclick="toggleNotifMenu()">
        🔔
        <span id="notifCount" class="notif-count" style="display:none;">0</span>
    </div>
</div>

        <!-- MENÚ DESPLEGABLE -->
        <div id="notifMenu" class="notif-menu"></div>

        <div class="user">Bienvenido, <?= htmlspecialchars($userNombre) ?></div>

    </div>
  </div>
</header>

    <button onclick="window.location.href='perfil.php'" class="perfil-btn">
        Mi Perfil
    </button>

    <a href="logout.php" class="logout-btn">Cerrar sesión</a>


<script>
function toggleNotifMenu() {
    document.getElementById("notifMenu").classList.toggle("open");
}

function cargarNotificaciones() {
    fetch("notificaciones_ajax.php")
    .then(res => res.json())
    .then(data => {

        const menu = document.getElementById("notifMenu");
        const count = document.getElementById("notifCount");

        // Actualizar contador
        if (data.length > 0) {
            count.style.display = "inline-block";
            count.textContent = data.length;
        } else {
            count.style.display = "none";
        }

        // Llenar menú
        menu.innerHTML = "";
        data.forEach(n => {
            menu.innerHTML += `
                <div class="notif-item">
                    <strong>${n.mensaje}</strong>
                    <div class="notif-actions">
                      ${["solicitud", "solicitud_amistad", "amistad", "nueva_solicitud"].includes(n.tipo) ? `
                            <button onclick="responderSolicitud(${n.id_solicitud}, 1)">Aceptar</button>
                            <button onclick="responderSolicitud(${n.id_solicitud}, 0)">Rechazar</button>
                        ` : ""}
                    </div>
                </div>
            `;
        });
    });
}

function responderSolicitud(id, aceptar) {
    fetch("respuesta_solicitud.php?id="+id+"&aceptar="+aceptar)
    .then(() => cargarNotificaciones());
}

// Recargar cada 5 segundos
setInterval(cargarNotificaciones, 5000);
cargarNotificaciones();
</script>


<!-- Banderas deslizantes -->
<div class="flags-marquee">
  <div class="flags-slide">
    <!-- Duplicamos las banderas para scroll continuo -->
    <img src="img/mexico.png" alt="México">
    <img src="img/usa.png" alt="EE.UU.">
    <img src="img/canada.png" alt="Canadá">
    <img src="img/mexico.png" alt="México">
    <img src="img/usa.png" alt="EE.UU.">
    <img src="img/canada.png" alt="Canadá">
    <img src="img/mexico.png" alt="México">
    <img src="img/usa.png" alt="EE.UU.">
    <img src="img/canada.png" alt="Canadá">
  </div>
</div>

 <!-- Marquee con múltiples textos -->
<div class="marquee">
  <div class="marquee-content">
    <span>🌍 ¡Vive la emoción del Mundial 2026 en México, EE.UU. y Canadá! </span>
    <span>🏟️ Estadio Azteca, MetLife Stadium y más sedes increíbles. </span>
    <span>⚽ Sigue a tus equipos favoritos y noticias en tiempo real. </span>
    <span>🎉 Participa en foros y comparte tu pasión por el fútbol. </span>
    <!-- duplicamos los textos para scroll continuo -->
    <span>🌍 ¡Vive la emoción del Mundial 2026 en México, EE.UU. y Canadá! </span>
    <span>🏟️ Estadio Azteca, MetLife Stadium y más sedes increíbles. </span>
    <span>⚽ Sigue a tus equipos favoritos y noticias en tiempo real. </span>
    <span>🎉 Participa en foros y comparte tu pasión por el fútbol. </span>
  </div>
</div>


<main class="container">

<div class="search-bar">
  <form action="perfil.php" method="get" onsubmit="return goToProfile(this);">
    <input type="text" name="usuario" placeholder="Buscar usuario..." required>
    <button type="submit">Buscar</button>
  </form>
</div>

<script>
function goToProfile(form) {
  const usuario = form.usuario.value.trim();
  if (usuario) {
    window.location.href = `perfil.php?usuario=${encodeURIComponent(usuario)}`;
  }
  return false;
}
</script>

<!-- ⭐ Mis equipos favoritos -->
<section class="card">
  <h3>Mis equipos favoritos</h3>
  <ul class="favorites">
    <?php if (count($favoritos) > 0): ?>
      <?php foreach ($favoritos as $f): ?>
        <li>⭐ <?= htmlspecialchars($f['nombre']) ?></li>
      <?php endforeach; ?>
    <?php else: ?>
      <li>No tienes equipos favoritos aún.</li>
    <?php endif; ?>
  </ul>
</section>

<!-- ✅ Selección de equipos favoritos -->
<section class="card">
  <h3>Selecciona tus equipos favoritos</h3>
  <form method="post" action="guardar_favoritos.php">
    <?php foreach ($equipos as $e): ?>
      <label>
        <input type="checkbox" name="favoritos[]" value="<?= $e['id'] ?>"
          <?= in_array($e['id'], $favoritos_ids) ? 'checked' : '' ?>>
        <?= htmlspecialchars($e['nombre']) ?>
      </label><br>
    <?php endforeach; ?>
    <button type="submit">Guardar favoritos</button>
  </form>
  <p class="small">Tus selecciones se guardarán en tu cuenta.</p>
</section>

<!-- 📰 Noticias -->
<section class="card">
  <h3>Noticias del Mundial 2026</h3>
  <ul class="news">
    <li>
      <strong>Ciudad de México ultima la renovación del Estadio Azteca</strong> – Los trabajos de modernización del Estadio Azteca avanzan a gran velocidad. Se están renovando completamente las áreas de hospitalidad, los vestuarios y los accesos, garantizando seguridad y comodidad para los aficionados. Los organizadores aseguran que estas mejoras permitirán ofrecer una experiencia de primera clase durante los partidos, incluyendo zonas interactivas y nuevas pantallas gigantes. <em>“Será una experiencia única para los fans, desde la entrada hasta el último minuto del partido”,</em> declaró un portavoz de la federación mexicana. Este histórico estadio será sede de varios partidos clave, incluyendo la inauguración y algunos encuentros de cuartos de final.
    </li>
    <li>
      <strong>Preparativos en Canadá frente a riesgos de humo de incendios</strong> – Con ciudades como Toronto y Vancouver seleccionadas como sedes, las autoridades canadienses están evaluando planes de contingencia ante posibles incendios forestales y humo durante el verano. Los organizadores del Mundial trabajan junto con especialistas en calidad del aire para garantizar que las condiciones sean seguras para jugadores y espectadores. Algunos aficionados expresan preocupación en redes sociales, mientras que otros destacan la oportunidad de que Canadá demuestre su capacidad organizativa y hospitalidad.
    </li>
    <li>
      <strong>Se revela el calendario y sedes de la Copa Mundial 2026</strong> – FIFA ha publicado el calendario oficial: la final se jugará en el MetLife Stadium en Nueva Jersey, mientras que el partido inaugural tendrá lugar en el Estadio Azteca. En total, se disputarán 108 partidos en ciudades de EE. UU., México y Canadá. Aficionados de todo el mundo ya discuten en foros sobre qué partidos ver en vivo y cómo planificar sus viajes, generando un gran entusiasmo y debates sobre las sedes ideales para ver los encuentros de sus selecciones favoritas.
    </li>
    <li>
      <strong>Derrota de EE. UU. ante Canadá pone en alerta los preparativos</strong> – El equipo de EE. UU. sufrió una derrota 2‑1 frente a Canadá en la Liga de Naciones de la CONCACAF. Entrenadores y aficionados discuten estrategias y alineaciones en foros y redes sociales. <em>“Este partido nos mostró que debemos reforzar la defensa y mejorar la cohesión del equipo”,</em> comentó el capitán estadounidense. Aun así, los analistas destacan que todavía hay tiempo para ajustar tácticas y que la preparación general sigue siendo positiva.
    </li>
    <li>
      <strong>Impacto económico para Canadá estimado en US$3.8 mil millones</strong> – Estudios preliminares indican que la preparación y realización de la Copa en Canadá generará miles de empleos y un aumento significativo en turismo y comercio local. Aficionados y empresarios locales comparten expectativas y consejos en foros sobre cómo aprovechar la llegada de miles de visitantes. Algunos destacan oportunidades de voluntariado y participación en eventos paralelos al Mundial, generando un ambiente de anticipación en toda la región.
    </li>
    <li>
      <strong>Selección mexicana realiza concentraciones en distintos estados</strong> – La selección nacional ha iniciado una serie de concentraciones en Guadalajara, Monterrey y Ciudad de México para preparar el torneo. Los entrenadores han compartido videos y fotos en redes sociales donde se observa la intensidad de los entrenamientos y la interacción con jóvenes talentos locales. Los fanáticos comentan activamente en foros sobre el rendimiento de sus jugadores favoritos y discuten posibles alineaciones para los partidos iniciales del Mundial.
    </li>
    <li>
      <strong>Infraestructura en EE. UU. lista para recibir a los aficionados</strong> – Ciudades como Nueva York, Los Ángeles y Dallas ya han comenzado mejoras en transporte, señalización y seguridad para garantizar la comodidad de los visitantes. Los foros de viaje se han llenado de consejos sobre hospedaje y transporte público, y los aficionados comparten recomendaciones sobre cómo moverse entre los estadios y los principales puntos turísticos.
    </li>
    <li>
      <strong>Canadá apuesta por sostenibilidad en sedes del Mundial</strong> – Todos los estadios canadienses están siendo remodelados con criterios de sostenibilidad: paneles solares, reducción de consumo de agua y transporte público accesible para los aficionados. Los organizadores esperan que estos cambios marquen un precedente para futuros torneos, mientras que en foros se discuten los beneficios ambientales y las oportunidades educativas para jóvenes locales.
    </li>
  </ul>
  <p class="small">*Los datos pueden actualizarse a medida que se acerque el torneo y se confirmen nuevos avances o sedes. Comparte tu opinión en los foros y mantente al día con las noticias oficiales.</p>
</section>


<!-- 📊 Seguimiento de favoritos -->
<section class="card">
  <h3>Seguimiento de equipos favoritos</h3>
  <?php if (count($favoritos) > 0): ?>
    <ul>
      <?php foreach ($favoritos as $f): ?>
        <li>
          <strong>⭐ <?= htmlspecialchars($f['nombre']) ?></strong>
          <div style="margin-top:4px; margin-bottom:10px;">
            <em>Próximos partidos:</em>
            <ul>
              <li><?= htmlspecialchars($f['nombre']) ?> vs Rival FC – 5 de Noviembre, 18:00 hrs</li>
              <li><?= htmlspecialchars($f['nombre']) ?> vs Otro Equipo – 12 de Noviembre, 20:00 hrs</li>
            </ul>
            <em>Últimos resultados:</em>
            <ul>
              <li><?= htmlspecialchars($f['nombre']) ?> 2 - 1 Rival FC</li>
              <li><?= htmlspecialchars($f['nombre']) ?> 0 - 0 Otro Equipo</li>
            </ul>
            <p style="font-size:0.9em; color:#555;">
              Comentario del entrenador: “El equipo mantiene buen ritmo, necesitamos ajustar la defensa y aprovechar las oportunidades en ataque. ¡Gran desempeño de los jóvenes talentos!”  
            </p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No tienes equipos favoritos seleccionados.</p>
  <?php endif; ?>
</section>


<!-- Widget API-Sports filtrando favoritos -->
<div id="api-content" class="col-span-3 row-span-3 flex flex-col items-center justify-center text-xl font-semibold text-gray-800 text-center">
  <api-sports-widget data-type="games"></api-sports-widget>
  <api-sports-widget
    data-type="config"
    data-key="5921cc3be28bc23071d3bd8843bff0cc"
    data-sport="football"
    data-refresh="15"
    data-show-logos="true"
    data-favorite="<?= implode(',', $favoritos_ids) ?>">
  </api-sports-widget>
</div>

<script type="module" src="https://widgets.api-sports.io/3.1.0/widgets.js"></script>

</main>
</body>
</html>
