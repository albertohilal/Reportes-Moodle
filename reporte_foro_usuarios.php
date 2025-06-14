<?php
require_once('../../config.php');
require_login();

$courseid = required_param('courseid', PARAM_INT);
$lastname = optional_param('lastname', '', PARAM_TEXT); // Apellido del estudiante

$context = context_course::instance($courseid);
require_capability('moodle/course:view', $context);

$PAGE->set_url(new moodle_url('/reportes/reporte_foro_usuarios.php', array('courseid' => $courseid, 'lastname' => $lastname)));
$PAGE->set_context($context);
$PAGE->set_title("Participación en foros");
$PAGE->set_heading("Participación en foros del curso");

echo $OUTPUT->header();
echo $OUTPUT->heading("Reporte de participación en foros");

$params = ['courseid' => $courseid];
$where = "WHERE c.id = :courseid AND cm.visible = 1";

if (!empty($lastname)) {
    $params['lastname'] = "%$lastname%";
    $where .= " AND u.lastname LIKE :lastname";
}

$sql = "
    SELECT 
      f.id AS foro_id,
      f.name AS foro_nombre,
      c.id AS curso_id,
      c.shortname AS curso,
      u.id AS usuario_id,
      u.lastname AS apellido,
      u.firstname AS nombre,
      COUNT(p.id) AS mensajes,
      MAX(p.created) AS fecha_ultimo_mensaje

    FROM {forum} f
    JOIN {course} c ON c.id = f.course
    JOIN {course_modules} cm ON cm.instance = f.id
    JOIN {modules} m ON m.id = cm.module AND m.name = 'forum'
    JOIN {user} u ON 1 = 1
    LEFT JOIN {forum_discussions} d ON d.forum = f.id
    LEFT JOIN {forum_posts} p ON p.discussion = d.id AND p.userid = u.id

    $where

    GROUP BY f.id, f.name, c.id, c.shortname, u.id, u.firstname, u.lastname
    ORDER BY f.name
";

$records = $DB->get_records_sql($sql, $params);

if ($records) {
    echo '<table class="generaltable">
            <thead>
                <tr>
                    <th>Foro</th>
                    <th>Curso</th>
                    <th>Estudiante</th>
                    <th>Mensajes</th>
                    <th>Fecha del último mensaje</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($records as $r) {
        echo '<tr>';
        echo '<td>'.format_string($r->foro_nombre).'</td>';
        echo '<td>'.format_string($r->curso).'</td>';
        echo '<td>'.fullname((object)['firstname' => $r->nombre, 'lastname' => $r->apellido]).'</td>';
        echo '<td>'.($r->mensajes ?: 0).'</td>';
        echo '<td>'.($r->fecha_ultimo_mensaje ? userdate($r->fecha_ultimo_mensaje) : '-').'</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo html_writer::div('No se encontraron datos para el criterio solicitado.', 'alert alert-info');
}

echo $OUTPUT->footer();
