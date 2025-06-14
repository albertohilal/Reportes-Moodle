<?php
require_once('../config.php'); // Ajustá la ruta según tu estructura
require_login();

global $DB;

// Inicializamos variables
$apellido = '';
$estudiantes = [];
$mostrar_resultado = false;

// Procesamos si envían datos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apellido = required_param('apellido', PARAM_TEXT);
    $mostrar_resultado = true;

    // Consulta a la base de datos
    $sql = "SELECT 
                u.username, 
                u.firstname, 
                u.lastname, 
                u.email,
                u.idnumber,
                c.id AS courseid, 
                c.fullname  
            FROM {role_assignments} AS ra
            INNER JOIN {context} AS cx ON ra.contextid = cx.id AND cx.contextlevel = 50 AND ra.roleid = 5
            INNER JOIN {user} AS u ON u.id = ra.userid
            INNER JOIN {course} AS c ON c.id = cx.instanceid
            WHERE u.lastname LIKE :apellido";

    $params = ['apellido' => "%$apellido%"];
    $estudiantes = $DB->get_records_sql($sql, $params);
}

$PAGE->set_context(context_system::instance());
$PAGE->set_url(new moodle_url('/local/tuplugin/estudiantesPorCurso.php')); // Ajustá tu ruta
$PAGE->set_title('Consulta de Estudiantes por Curso');
$PAGE->set_heading('Consulta de Estudiantes por Curso');

echo $OUTPUT->header();
?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8">

      <?php if ($mostrar_resultado): ?>
        <h3 class="mb-4">Resultados de la búsqueda</h3>

        <?php if (!empty($estudiantes)): ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead class="table-light">
                <tr>
                  <th>Username</th>
                  <th>Nombre</th>
                  <th>Apellido</th>
                  <th>Email</th>
                  <th>ID Número</th>
                  <th>ID Curso</th>
                  <th>Nombre del Curso</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($estudiantes as $e): ?>
                  <tr>
                    <td><?= s($e->username) ?></td>
                    <td><?= s($e->firstname) ?></td>
                    <td><?= s($e->lastname) ?></td>
                    <td><?= s($e->email) ?></td>
                    <td><?= s($e->idnumber) ?></td>
                    <td><?= s($e->courseid) ?></td>
                    <td><?= s($e->fullname) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <a href="estudiantesPorCurso.php" class="btn btn-primary">Nueva búsqueda</a>
        <?php else: ?>
          <div class="alert alert-warning">
            No se encontraron estudiantes que coincidan con "<strong><?= s($apellido) ?></strong>".
          </div>
          <a href="estudiantePorCurso.php" class="btn btn-primary">Volver</a>

        <?php endif; ?>

      <?php else: ?>
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white text-center">
            <h2>Buscar Estudiantes por Apellido</h2>
          </div>
          <div class="card-body">
            <form method="POST" action="">
              <div class="mb-3">
                <label for="apellido" class="form-label">Apellido</label>
                <input type="text" class="form-control" id="apellido" name="apellido" required>
              </div>

              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Buscar</button>
              </div>
            </form>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php
echo $OUTPUT->footer();
?>
