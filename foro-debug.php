<?php
// Mostrar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');
require_login();

global $DB, $OUTPUT, $PAGE;

$apellido = optional_param('apellido', '', PARAM_RAW);

// Página Moodle
$PAGE->set_url(new moodle_url('/reportes/foro.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Consulta de Foros por Usuario");

echo $OUTPUT->header();

echo '<div class="container">
<h2>Buscar tareas del curso por apellido</h2>
<form method="get" class="mb-4">
  <label>Apellido del usuario:</label>
  <input type="text" name="apellido" value="' . s($apellido) . '" class="form-control" style="max-width:300px; display:inline-block;" />
  <button type="submit" class="btn btn-primary ml-2">Buscar</button>
</form>';

if (!empty($apellido)) {
  $sql = "
    SELECT 
      c.id AS curso_id,
      c.shortname AS curso,
      f.name AS tarea,
      f.intro AS intro,
      u.lastname AS apellido,
      u.firstname AS nombre,
      p.subject AS asunto,
      p.message AS mensaje

    FROM {forum} f
    LEFT JOIN {course} c ON c.id = f.course
    LEFT JOIN {forum_discussions} d ON d.forum = f.id
    LEFT JOIN {forum_posts} p ON p.discussion = d.id
    LEFT JOIN {user} u ON u.id = d.userid

    WHERE c.id = 10
      AND (u.lastname LIKE :apellido OR u.lastname IS NULL)

    ORDER BY f.name
  ";

  $params = ['apellido' => "%$apellido%"];
  $registros = $DB->get_recordset_sql($sql, $params);

  echo '<table class="table table-bordered table-striped">
    <thead class="thead-dark"><tr>
      <th>Curso</th>
      <th>Tarea</th>
      <th>Intro</th>
      <th>Apellido</th>
      <th>Nombre</th>
      <th>Asunto</th>
      <th>Mensaje</th>
    </tr></thead><tbody>';

  foreach ($registros as $r) {
    echo '<tr>
      <td>' . s($r->curso) . '</td>
      <td>' . s($r->tarea) . '</td>
      <td>' . format_text($r->intro, FORMAT_HTML) . '</td>
      <td>' . s($r->apellido) . '</td>
      <td>' . s($r->nombre) . '</td>
      <td>' . s($r->asunto) . '</td>
      <td>' . format_text($r->mensaje, FORMAT_HTML) . '</td>
    </tr>';
  }

  echo '</tbody></table>';
  $registros->close();
}

echo '</div>';
echo $OUTPUT->footer();
?>
