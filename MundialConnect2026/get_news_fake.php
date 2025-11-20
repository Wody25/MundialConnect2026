<?php
header("Content-Type: application/json");

// Noticias simuladas por equipo
$news = [
    [
        "team_id" => 1,
        "title" => "México prepara estrategia para el siguiente partido",
        "description" => "El entrenador nacional ajusta la alineación previo a los amistosos.",
        "url" => "https://espndeportes.espn.com"
    ],
    [
        "team_id" => 2,
        "title" => "Canadá sorprende con nuevo delantero estrella",
        "description" => "El joven talento debuta con una actuación impresionante.",
        "url" => "https://www.goal.com"
    ],
    [
        "team_id" => 3,
        "title" => "EE.UU. intensifica entrenamientos rumbo al Mundial",
        "description" => "El equipo estadounidense muestra gran forma física.",
        "url" => "https://espndeportes.espn.com"
    ],
    [
        "team_id" => 4,
        "title" => "Brasil anuncia lista preliminar para el torneo",
        "description" => "Varios talentos jóvenes podrían debutar.",
        "url" => "https://as.com"
    ],
    [
        "team_id" => 5,
        "title" => "Alemania reorganiza su defensa ante bajas importantes",
        "description" => "El técnico alemán confirma nuevas incorporaciones.",
        "url" => "https://marca.com"
    ],
    [
        "team_id" => 6,
        "title" => "Japón presenta su nueva camiseta con estilo futurista",
        "description" => "La federación japonesa sorprende con diseño innovador.",
        "url" => "https://es.goal.com"
    ],
    [
        "team_id" => 7,
        "title" => "Argentina celebra victoria contundente",
        "description" => "La albiceleste continúa en excelente forma.",
        "url" => "https://tycsports.com"
    ],
    [
        "team_id" => 8,
        "title" => "España anuncia renovación generacional",
        "description" => "Nuevos talentos buscan un lugar en el once titular.",
        "url" => "https://mundodeportivo.com"
    ],
        // === Noticias para equipos nuevos (ID 9 al 34) ===
    [
        "team_id" => 9,
        "title" => "Ecuador cierra preparación con victoria sólida",
        "description" => "La Tri continúa mostrando regularidad antes del Mundial.",
        "url" => "https://www.elcomercio.com/deportes/futbol/ecuador-amistoso-previo-mundial.html"
    ],
    [
        "team_id" => 10,
        "title" => "Uruguay confirma amistoso internacional",
        "description" => "La Celeste enfrentará a Japón en su próxima preparación.",
        "url" => "https://www.republica.com.uy/deportes/uruguay-amistoso-fifa-2026"
    ],
    [
        "team_id" => 11,
        "title" => "Colombia anuncia microciclo de entrenamiento",
        "description" => "La Selección Colombia afina detalles tácticos.",
        "url" => "https://www.eltiempo.com/deportes/seleccion-colombia"
    ],
    [
        "team_id" => 12,
        "title" => "Paraguay revela nueva convocatoria",
        "description" => "Nuevos talentos aparecen en la lista rumbo al Mundial.",
        "url" => "https://www.abc.com.py/deportes/futbol/paraguay-convocatoria"
    ],
    [
        "team_id" => 13,
        "title" => "Irán continúa invicto en su preparación",
        "description" => "La selección iraní suma otro triunfo en amistoso internacional.",
        "url" => "https://www.marca.com/futbol/internacional/iran.html"
    ],
    [
        "team_id" => 14,
        "title" => "Australia anuncia gira por Asia",
        "description" => "Los Socceroos enfrentarán a Corea del Sur y Japón.",
        "url" => "https://www.abc.net.au/news/sport/soccer/australia"
    ],
    [
        "team_id" => 15,
        "title" => "Uzbekistán sorprende con nuevo técnico",
        "description" => "La federación anunció cambios estratégicos antes del Mundial.",
        "url" => "https://www.fifa.com/es/noticias/uzbekistan-futbol"
    ],
    [
        "team_id" => 16,
        "title" => "Corea del Sur presenta lista preliminar",
        "description" => "La selección surcoreana incorpora caras nuevas.",
        "url" => "https://www.koreatimes.co.kr/www/sports"
    ],
    [
        "team_id" => 17,
        "title" => "Jordania continúa con su histórico proceso mundialista",
        "description" => "El equipo mantiene el ritmo con otro amistoso ganado.",
        "url" => "https://www.aljazeera.com/sports/jordan-football"
    ],
    [
        "team_id" => 18,
        "title" => "Arabia Saudita refuerza su defensiva",
        "description" => "El técnico anuncia ajustes clave antes de los partidos oficiales.",
        "url" => "https://arabnews.com/sport/saudi-football"
    ],
    [
        "team_id" => 19,
        "title" => "Catar anuncia sede para próximos amistosos",
        "description" => "El equipo jugará en Doha sus últimos partidos de preparación.",
        "url" => "https://www.qfa.qa/news"
    ],
    [
        "team_id" => 20,
        "title" => "Marruecos continua con racha positiva",
        "description" => "Los Leones del Atlas siguen brillando en su preparación.",
        "url" => "https://www.marca.com/futbol/marruecos.html"
    ],
    [
        "team_id" => 21,
        "title" => "Túnez confirma plantilla para próximo duelo",
        "description" => "La selección tunecina incorpora jóvenes promesas.",
        "url" => "https://www.france24.com/es/tag/túnez-fútbol/"
    ],
    [
        "team_id" => 22,
        "title" => "Egipto listo para enfrentar una dura agenda",
        "description" => "Los Faraones preparan su esquema táctico.",
        "url" => "https://www.kingfut.com/"
    ],
    [
        "team_id" => 23,
        "title" => "Ghana presenta nueva indumentaria oficial",
        "description" => "El diseño generó opiniones divididas entre los aficionados.",
        "url" => "https://www.ghanafa.org/"
    ],
    [
        "team_id" => 24,
        "title" => "Argelia comienza concentración en Europa",
        "description" => "El equipo busca mejorar su rendimiento ofensivo.",
        "url" => "https://www.dzfoot.com/"
    ],
    [
        "team_id" => 25,
        "title" => "Cabo Verde sorprende con triunfo en amistoso",
        "description" => "La selección continúa mostrando un fútbol sólido.",
        "url" => "https://www.radioclusport.cv/"
    ],
    [
        "team_id" => 26,
        "title" => "Costa de Marfil afina detalles tácticos",
        "description" => "El entrenador ajusta formaciones antes de próxima gira.",
        "url" => "https://www.fifaciv.com/"
    ],
    [
        "team_id" => 27,
        "title" => "Senegal refuerza su plantel",
        "description" => "La selección suma nuevos jugadores del fútbol europeo.",
        "url" => "https://wiwsport.com/"
    ],
    [
        "team_id" => 28,
        "title" => "Sudáfrica anuncia nuevo proyecto juvenil",
        "description" => "Buscan potenciar a jugadores locales para el futuro.",
        "url" => "https://www.kickoff.com/"
    ],
    [
        "team_id" => 29,
        "title" => "Nueva Zelanda confirma gira por Europa",
        "description" => "Los All Whites se medirán a rivales de alto nivel.",
        "url" => "https://www.nzfootball.co.nz/"
    ],
    [
        "team_id" => 30,
        "title" => "Curazao continúa preparación intensa",
        "description" => "El equipo se concentra en fortalecer su defensa.",
        "url" => "https://www.ffk.cw/"
    ],
    [
        "team_id" => 31,
        "title" => "Haití anuncia renovación en el cuerpo técnico",
        "description" => "Buscan cambiar el estilo de juego para el Mundial.",
        "url" => "https://www.haititempo.com/"
    ],
    [
        "team_id" => 32,
        "title" => "Panamá celebra avance histórico",
        "description" => "La selección panameña vive uno de sus mejores momentos.",
        "url" => "https://www.fepafut.com/"
    ],
    [
        "team_id" => 33,
        "title" => "Inglaterra presenta su lista preliminar",
        "description" => "El equipo incorpora jóvenes de la Premier League.",
        "url" => "https://www.marca.com/futbol/inglaterra.html"
    ],
    [
        "team_id" => 34,
        "title" => "Francia cierra convocatoria con sorpresas",
        "description" => "Dos nuevos talentos se suman a la plantilla.",
        "url" => "https://www.lequipe.fr/Football/"
    ],
    [
    "team_id" => 39,
    "title" => "Noruega anuncia convocatoria para el Mundial 2026",
    "description" => "El equipo noruego presenta su lista de jugadores con varias jóvenes promesas.",
    "url" => "https://www.fotball.no/landslag/norge-a-menn/"
],

];

// IDs de favoritos enviados desde JavaScript
$fav_ids = isset($_GET["teams"]) ? explode(",", $_GET["teams"]) : [];

$result = [];

// filtrar noticias
foreach ($news as $n) {
    if (in_array($n["team_id"], $fav_ids)) {
        $result[] = $n;
    }
}

function getNoticiasPorEquipos($fav_ids) {
    global $news;
    $result = [];
    foreach ($news as $n) {
        if (in_array($n["team_id"], $fav_ids)) {
            $result[] = $n;
        }
    }
    return $result;
}

echo json_encode(["response" => $result]);
