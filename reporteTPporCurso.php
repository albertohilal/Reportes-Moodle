<?php
require_once('../config.php'); // Moodle config
require_login();

global $DB;

// Obtener listado de cursos con tareas TP-*
$courses = $DB->get_records_sql("
    SELECT DISTINCT c.id, c.shortname
    FROM {course} c
    JOIN {forum} f ON f.course = c.id
    WHERE f.name LIKE 'TP-%'
    ORDER BY c.shortname
");

// Obtener parámetro de curso si está seteado
$courseid = optional_param('courseid', null, PARAM_INT);
$selected_course = null;
$report_data = [];

if ($courseid) {
    $selected_course = $DB->get_record('course', ['id' => $courseid]);
    
    // Obtener estudiantes inscriptos
    $students = $DB->get_records_sql("
        SELECT u.id, u.firstname, u.lastname
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        JOIN {context} cx ON cx.id = ra.contextid AND cx.contextlevel = 50
        WHERE cx.instanceid = :courseid AND ra.roleid = 5
        ORDER BY u.lastname, u.firstname
    ", ['courseid' => $courseid]);

    // Obtener foros TP del curso
    $forums = $DB->get_records_sql("
        SELECT id, name
        FROM {forum}
        WHERE course = :courseid AND name LIKE 'TP-%'
        ORDER BY name
    ", ['courseid' => $courseid]);

    // Obtener participaciones
    foreach ($students as $student) {
        $row = ['apellido' => $student->lastname, 'nombre' => $student->firstname];
        foreach ($forums as $forum) {
            $message = $DB->get_field_sql("
                SELECT fp.message
                FROM {forum_discussions} fd
                JOIN {forum_posts} fp ON fp.discussion = fd.id
                WHERE fd.forum = :forumid AND fp.userid = :userid
                ORDER BY fp.created ASC
                LIMIT 1
            ", ['forumid' => $forum->id, 'userid' => $student->id]);
            $row[$forum->name] = $message ?: '';
        }
        $report_data[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte TP por Curso</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Participación en Foros TP</h2>

        <form method="get" class="mb-3">
            <label for="courseid" class="form-label"><strong>Seleccionar curso:</strong></label>
            <select name="courseid" id="courseid" class="form-select" onchange="this.form.submit()">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?= $course->id ?>" <?= ($courseid == $course->id) ? 'selected' : '' ?>>
                        <?= $course->shortname ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($selected_course): ?>
            <p><strong>Curso:</strong> <em><?= $selected_course->shortname ?></em></p>

            <button id="exportBtn" class="btn btn-success mb-3">Exportar a Excel</button>

            <div class="table-responsive">
                <table class="table table-bordered" id="tablaReporte">
                    <thead class="table-light">
                        <tr>
                            <th>Apellido</th>
                            <th>Nombre</th>
                            <?php foreach ($forums as $forum): ?>
                                <th><?= $forum->name ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($report_data as $row): ?>
                            <tr>
                                <td><?= $row['apellido'] ?></td>
                                <td><?= $row['nombre'] ?></td>
                                <?php foreach ($forums as $forum): ?>
                                    <td><?= $row[$forum->name] ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Script para exportar a Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script>
        document.getElementById("exportBtn")?.addEventListener("click", function () {
            const table = document.getElementById("tablaReporte");
            if (!table) {
                alert("No se encontró la tabla.");
                return;
            }

            if (typeof XLSX === 'undefined' || typeof XLSX.utils === 'undefined') {
                alert("La librería XLSX no se cargó correctamente.");
                return;
            }

            const wb = XLSX.utils.table_to_book(table, { sheet: "Reporte TP" });
            XLSX.writeFile(wb, "reporte_tp.xlsx");
        });
    </script>
</body>
</html>
