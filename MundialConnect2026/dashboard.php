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
<link rel="stylesheet" href="styles.css">
<style>
.flags-container {
  display: flex;
  gap: 20px;
  overflow: hidden;
  position: relative;
  height: 60px;
  align-items: center;
  margin-bottom: 10px;
}
.flags-slide {
  display: flex;
  animation: slideFlags 12s linear infinite;
}
.flags-slide img {
  height: 40px;
  width: auto;
}
@keyframes slideFlags {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}
.marquee {
  white-space: nowrap;
  overflow: hidden;
  animation: marquee 15s linear infinite;
  font-weight: bold;
  color: #fff;
}
@keyframes marquee {
  0% { text-indent: 100%; }
  100% { text-indent: -100%; }
}
.search-bar {
  text-align: right;
  margin-top: 10px;
}
.search-bar input[type="text"] {
  padding: 6px;
  border-radius: 6px;
  border: 1px solid #ccc;
}
.card {
  background: #fff;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 8px;
}
.favorites li {
  margin-bottom: 5px;
}
.logout-btn {
    color: #fff;
    text-decoration: underline;
    margin-left: 10px;
    font-weight: bold;
}
.logout-btn:hover {
    color: #ff5555;
}
</style>
</head>

<body class="bg">

<header class="topbar">
  <div class="container">
    <h2>🏆 MundialConnect 2026</h2>
    <div class="user">Bienvenido, <?= htmlspecialchars($userNombre) ?></div>
      | <a href="logout.php" class="logout-btn">Cerrar sesión</a>
      <div class="user">
    Bienvenido, <?= htmlspecialchars($userNombre) ?>
    <button onclick="window.location.href='perfil.php'" 
            style="margin-left:10px; padding:5px 10px; border:none; border-radius:5px; background-color:#28a745; color:#fff; cursor:pointer;">
        Mi Perfil
    </button>
</div>
  </div>

  <div class="flags-container">
    <div class="flags-slide">
      <img src="img/mexico.png" alt="México">
      <img src="img/usa.png" alt="EE.UU.">
      <img src="img/canada.png" alt="Canadá">
      <img src="img/mexico.png" alt="México">
      <img src="img/usa.png" alt="EE.UU.">
      <img src="img/canada.png" alt="Canadá">
    </div>
  </div>
  <div class="marquee">🌍 ¡Vive la pasión del Mundial 2026 en México 🇲🇽, Estados Unidos 🇺🇸 y Canadá 🇨🇦!</div>
</header>

<main class="container">

<div class="search-bar">
  <form action="perfil.html" method="get" onsubmit="return goToProfile(this);">
    <input type="text" name="usuario" placeholder="Buscar usuario..." required>
    <button type="submit">Buscar</button>
  </form>
</div>

<script>
function goToProfile(form) {
  const usuario = form.usuario.value.trim();
  if (usuario) {
    window.location.href = `perfil.html?usuario=${encodeURIComponent(usuario)}`;
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
