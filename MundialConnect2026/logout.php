<?php
session_start();

// Destruir toda la sesión
session_unset();
session_destroy();

// Redirigir a la página principal
header("Location: index.html");
exit;
?>
