<?php
require_once('../../config.php');

// Capturar el apellido desde el formulario (GET)
$apellido = optional_param('apellido', '', PARAM_RAW);

// Encabezado HTML
echo '<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Foros del usuario</title>
  <style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%%; }
    th, td { border: 1px solid #ccc; padding: 8px; }
    th { background-color: #f2f2f2; }
  </style>
</head>
<body>';

echo '<h2>Buscar tareas del curso por apellido</h2>
<form method="get">
  <label>Apellido: </label>
  <input type="text" name="apellido" value="' . s($apellido) . '" />
  <button type="submit">Buscar</button>
</form>';

// Solo ejecutar si se ingresó un apellido
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

  echo '<table>
    <tr>
      <th>Curso</th>
      <th>Tarea</th>
      <th>Intro</th>
      <th>Apellido</th>
      <th>Nombre</th>
      <th>Asunto</th>
      <th>Mensaje</th>
    </tr>';

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

  echo '</table>';
  $registros->close();
}

echo '</body></html>';
?>
