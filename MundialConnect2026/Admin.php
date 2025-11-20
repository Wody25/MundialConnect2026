<?php
session_start();

// Validar que esté logueado y sea admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

// Conexión
require 'conexion.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
    <link rel="icon" type="image/x-icon" href="img/fifa.ico">
    <style>
        /* ======== CSS Topbar y botones ======== */
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
        .header-buttons {
            margin: 10px;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .btn-general {
            display: inline-block;
            background-color: #00205B;
            color: white;
            border: 2px solid #D9001D;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: bold;
            text-decoration: none;
            transition: 0.2s;
            margin: 10px 5px;
        }
        .btn-general:hover { background-color: #00163F; }

        /* ======== Estilos de tarjetas y listas ======== */
        .card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .card h3 {
            background: linear-gradient(90deg,#00205B,#D9001D);
            color: white;
            padding: 8px;
            border-radius: 5px;
        }
        .card ul {
            list-style: none;
            padding-left: 0;
        }
        .card ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .card ul li:last-child { border-bottom: none; }

        /* ======== Formulario ======== */
        input[type="text"] {
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-right: 5px;
            margin-bottom: 5px;
        }
        button[type="submit"] {
            background-color: #00205B;
            color: white;
            border: 2px solid #D9001D;
            padding: 8px 14px;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
        }
        button[type="submit"]:hover { background-color: #00163F; }

        /* ======== Nuevo CSS de perfil y listas ======== */
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

        /* Mensaje de alerta */
        .alert-success { padding:10px; border-radius:6px; margin-bottom:10px; color:#155724; background:#d4edda; border:1px solid #c3e6cb; }
        .alert-error { padding:10px; border-radius:6px; margin-bottom:10px; color:#721c24; background:#f8d7da; border:1px solid #f5c6cb; }
    </style>
</head>
<body>
<header class="topbar">
    <h2>Panel de Administración</h2>
    <div class="header-buttons">
        <a href="logout.php" class="btn-general">Cerrar sesión</a>
    </div>
</header>

<div class="container perfil-layout" style="padding:20px;">
    <div class="perfil-main">
        <?php
        // Contar equipos
        $sqlEquipos = "SELECT COUNT(*) AS total FROM Equipos";
        $resEquipos = sqlsrv_query($conexion, $sqlEquipos);
        $equipos = sqlsrv_fetch_array($resEquipos, SQLSRV_FETCH_ASSOC);

        // Contar usuarios
        $sqlUsuarios = "SELECT COUNT(*) AS total FROM Usuarios";
        $resUsuarios = sqlsrv_query($conexion, $sqlUsuarios);
        $usuarios = sqlsrv_fetch_array($resUsuarios, SQLSRV_FETCH_ASSOC);

        // Consultar amistades
        $sqlAmistades = "
            SELECT u1.nombre AS usuario1, u2.nombre AS usuario2, estado 
            FROM Amistades a
            JOIN Usuarios u1 ON a.usuario1 = u1.id
            JOIN Usuarios u2 ON a.usuario2 = u2.id
        ";
        $resAmistades = sqlsrv_query($conexion, $sqlAmistades);
        ?>

        <!-- Sección Equipos -->
        <div class="card">
            <h3>Equipos</h3>
            <p>Total de equipos agregados: <strong id="totalEquipos"><?php echo $equipos['total']; ?></strong></p>

            <div id="mensaje"></div>

            <form id="formAgregarEquipo">
                <input type="text" name="nombre" placeholder="Nombre del equipo" required>
                <input type="text" name="grupo" placeholder="Grupo (A-H)">
                <input type="text" name="pais_bandera" placeholder="URL bandera">
                <button type="submit">Agregar Equipo</button>
            </form>
        </div>

        <!-- Sección Usuarios -->
        <div class="card">
            <h3>Usuarios</h3>
            <p>Total de usuarios registrados: <strong><?php echo $usuarios['total']; ?></strong></p>
        </div>

        <!-- Sección Amistades -->
        <div class="card">
            <h3>Amistades</h3>
            <ul class="amigos-list">
                <?php
                while ($amistad = sqlsrv_fetch_array($resAmistades, SQLSRV_FETCH_ASSOC)) {
                    echo "<li>" . $amistad['usuario1'] . " es amigo de " . $amistad['usuario2'] . " (Estado: " . $amistad['estado'] . ")</li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <!-- Panel lateral con noticias -->
    <div class="perfil-side">
        <h3>Noticias Mundial 2026</h3>
        <ul class="news-list">
            <li><strong>FIFA anuncia sedes del Mundial 2026</strong><br><small>19/11/2025</small></li>
            <li><strong>Entradas disponibles desde diciembre</strong><br><small>18/11/2025</small></li>
            <li><strong>Calendario de partidos revelado</strong><br><small>17/11/2025</small></li>
            <li><strong>Nuevas sedes en Canadá y México</strong><br><small>16/11/2025</small></li>
            <li><strong>Equipos clasificados hasta ahora</strong><br><small>15/11/2025</small></li>
        </ul>
    </div>
</div>

<script>
document.getElementById('formAgregarEquipo').addEventListener('submit', async function(e){
    e.preventDefault();
    const form = e.target;
    const data = new FormData(form);
    data.append('ajax', '1');

    const response = await fetch('admin_agregar_equipo.php', {
        method: 'POST',
        body: data
    });

    const result = await response.json();

    const mensajeDiv = document.getElementById('mensaje');
    mensajeDiv.innerHTML = `<div class="${result.type==='success'?'alert-success':'alert-error'}">${result.msg}</div>`;

    if(result.type==='success'){
        // actualizar total de equipos
        document.getElementById('totalEquipos').innerText = result.totalEquipos;
        form.reset();
    }

    // Hacer que el mensaje desaparezca después de 5 segundos
    setTimeout(() => {
        mensajeDiv.innerHTML = '';
    }, 5000);
});
</script>

<?php sqlsrv_close($conexion); ?>
</body>
</html>
