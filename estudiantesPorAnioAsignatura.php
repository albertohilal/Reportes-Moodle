<?php
require_once('../config.php');
require_login();
global $DB;

$anio = optional_param('anio', '', PARAM_INT);
$cursoid = optional_param('cursoid', '', PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/reportes/estudiantesPorAnioAsignatura.php'));
$PAGE->set_title('Estudiantes por Año y Asignatura');
$PAGE->set_heading('Estudiantes por Año y Asignatura');

echo $OUTPUT->header();

// Obtener lista de años desde los cursos
$sqlanios = "SELECT DISTINCT FROM_UNIXTIME(startdate, '%Y') as anio
             FROM {course}
             WHERE startdate > 0
             ORDER BY anio DESC";
$anios = $DB->get_records_sql_menu($sqlanios);

// Si hay un año elegido, obtenemos los cursos de ese año
$cursos = [];
if ($anio) {
    $sqlcursos = "SELECT id, fullname
                  FROM {course}
                  WHERE FROM_UNIXTIME(startdate, '%Y') = :anio
                  ORDER BY fullname ASC";
    $cursos = $DB->get_records_sql_menu($sqlcursos, ['anio' => $anio]);
}

// Si hay curso seleccionado, buscamos estudiantes
$estudiantes = [];
if ($cursoid) {
    $sql = "SELECT u.id, u.username, u.firstname, u.lastname, u.email
            FROM {user} u
            JOIN {role_assignments} ra ON ra.userid = u.id
            JOIN {context} cx ON cx.id = ra.contextid
            JOIN {course} c ON c.id = cx.instanceid
            WHERE ra.roleid = 5 AND cx.contextlevel = 50 AND c.id = :cursoid";
    $estudiantes = $DB->get_records_sql($sql, ['cursoid' => $cursoid]);
}
?>

<div class="container py-4">
    <h2 class="mb-4">Buscar estudiantes por año y asignatura</h2>
    <form method="GET" class="row mb-4">
        <div class="col-md-4 mb-3">
            <label for="anio" class="form-label">Año</label>
            <select name="anio" id="anio" class="form-select" onchange="this.form.submit()">
                <option value="">Seleccionar año</option>
                <?php foreach ($anios as $valor => $label): ?>
                    <option value="<?= $valor ?>" <?= $anio == $valor ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!empty($cursos)): ?>
        <div class="col-md-6 mb-3">
            <label for="cursoid" class="form-label">Asignatura</label>
            <select name="cursoid" id="cursoid" class="form-select">
                <option value="">Seleccionar asignatura</option>
                <?php foreach ($cursos as $id => $nombre): ?>
                    <option value="<?= $id ?>" <?= $cursoid == $id ? 'selected' : '' ?>><?= format_string($nombre) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 mb-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Buscar</button>
        </div>
        <?php endif; ?>
    </form>

    <?php if ($cursoid): ?>
        <h4>Resultados</h4>
        <?php if (!empty($estudiantes)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $e): ?>
                            <tr>
                                <td><?= s($e->username) ?></td>
                                <td><?= s($e->firstname) ?></td>
                                <td><?= s($e->lastname) ?></td>
                                <td><?= s($e->email) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">No se encontraron estudiantes.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php echo $OUTPUT->footer(); ?>
