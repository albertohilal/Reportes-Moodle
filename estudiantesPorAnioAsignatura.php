<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../config.php');
require_login();

$anio = optional_param('anio', '', PARAM_TEXT);
$courseid = optional_param('courseid', '', PARAM_INT);

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/reportes/estudiantesPorAnioAsignatura.php', array('anio' => $anio, 'courseid' => $courseid)));
$PAGE->set_title('Estudiantes por Año y Asignatura');
$PAGE->set_heading('Buscar estudiantes por año y asignatura');

// Inyectar estilo para forzar visibilidad clara en select
echo '<style>
    select, select option {
        color: #000 !important;
        background-color: #fff !important;
    }
</style>';

// Inicio de página
echo $OUTPUT->header();
echo $OUTPUT->heading('Buscar estudiantes por año y asignatura');

// Formulario de búsqueda
echo '<form method="GET" class="row mb-4">';
echo '<div class="col-md-4 mb-3">';
echo '<label for="anio" class="form-label">Año</label>';
echo '<select name="anio" id="anio" class="form-select" onchange="this.form.submit()">';
echo '<option value="">Seleccionar año</option>';
for ($y = 2022; $y <= date('Y') + 1; $y++) {
    $selected = ($anio == $y) ? 'selected' : '';
    echo '<option value="' . $y . '" ' . $selected . '>' . $y . '</option>';
}
echo '</select>';
echo '</div>';

echo '<div class="col-md-6 mb-3">';
echo '<label for="courseid" class="form-label">Asignatura</label>';
echo '<select name="courseid" id="courseid" class="form-select">';
echo '<option value="">Seleccionar curso</option>';

$courses = $DB->get_records('course', array(), 'fullname');
foreach ($courses as $c) {
    if (!$anio || strpos($c->fullname, $anio) !== false) {
        $selected = ($courseid == $c->id) ? 'selected' : '';
        echo '<option value="' . $c->id . '" ' . $selected . '>' . format_string($c->fullname) . '</option>';
    }
}
echo '</select>';
echo '</div>';

echo '<div class="col-md-2 d-flex align-items-end">';
echo '<button type="submit" class="btn btn-primary w-100">Buscar</button>';
echo '</div>';
echo '</form>';

if (!empty($courseid)) {
    $sql = "
        SELECT u.username, u.firstname, u.lastname, u.email
        FROM {user} u
        JOIN {user_enrolments} ue ON ue.userid = u.id
        JOIN {enrol} e ON e.id = ue.enrolid
        WHERE e.courseid = :courseid
        ORDER BY u.lastname, u.firstname
    ";
    $users = $DB->get_records_sql($sql, ['courseid' => $courseid]);

    echo '<h4>Resultados</h4>';
    echo '<table class="table table-striped">';
    echo '<thead><tr><th>Username</th><th>Nombre</th><th>Apellido</th><th>Email</th></tr></thead><tbody>';

    foreach ($users as $u) {
        echo '<tr>';
        echo '<td>' . s($u->username) . '</td>';
        echo '<td>' . s($u->firstname) . '</td>';
        echo '<td>' . s($u->lastname) . '</td>';
        echo '<td>' . s($u->email) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

echo $OUTPUT->footer();
