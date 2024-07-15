<?php

require_once('../config.php');
require_login();

global $DB;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $user_lastname = isset($_POST['user_lastname']) ? $_POST['user_lastname'] : '';

    if ($course_id > 0 && !empty($user_lastname)) {
        // Consulta a la base de datos
        $sql = "
        SELECT 
    mdl_course.id AS idCurso,
    mdl_course.shortname AS Curso,
    mdl_assign.name AS Tarea,
    mdl_assign.intro AS Intro,
    mdl_user.lastname AS Apellido,
    mdl_user.firstname AS Nombre,
    mdl_assignsubmission_onlinetext.onlinetext AS TextoEnLinea
FROM 
    mdl_course
JOIN 
    mdl_assign 
    ON mdl_assign.course = mdl_course.id
JOIN 
    mdl_assign_submission 
    ON mdl_assign_submission.assignment = mdl_assign.id
JOIN 
    mdl_assignsubmission_onlinetext 
    ON mdl_assignsubmission_onlinetext.submission = mdl_assign_submission.id
JOIN 
    mdl_user 
    ON mdl_user.id = mdl_assign_submission.userid
        WHERE 
            mdl_course.id = ? AND mdl_user.lastname LIKE ?";
        
        $params = [$course_id, '%' . $user_lastname . '%'];
        $recordset = $DB->get_recordset_sql($sql, $params);

        // Contar resultados manualmente
        $count_results = 0;
        foreach ($recordset as $record) {
            $count_results++;
        }
    } else {
        $recordset = null;
        $count_results = 0;
    }

    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Resultados de Tarea</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

    <div class="container mt-5">
        <h2 class="mb-4">Resultados de Tarea</h2>
        <p>Cantidad de resultados obtenidos: <?php echo $count_results; ?></p>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Curso</th>
                    <th>Curso</th>
                    <th>Tarea</th>
                    <th>Intro</th>
                    <th>Apellido</th>
                    <th>Nombre</th>
                    <th>Asunto</th>
                    <th>Mensaje</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($recordset) && $count_results > 0): ?>
                    <?php
                    // Reset recordset
                    $recordset->close(); 
                    $recordset = $DB->get_recordset_sql($sql, $params);
                    while ($recordset->valid()): ?>
                        <?php $record = $recordset->current(); ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record->id); ?></td>
                            <td><?php echo htmlspecialchars($record->curso); ?></td>
                            <td><?php echo htmlspecialchars($record->tarea); ?></td>
                            <td><?php echo (substr($record->intro, 0, 80)); ?></td>
                            <td><?php echo htmlspecialchars($record->apellido); ?></td>
                            <td><?php echo htmlspecialchars($record->nombre); ?></td>
                            <td><?php echo htmlspecialchars($record->subject); ?></td>
                            <td><?php echo $record->mensaje; ?></td> <!-- Se muestra el mensaje en formato HTML -->
                        </tr>
                        <?php $recordset->next(); ?>
                    <?php endwhile; ?>
                    <?php $recordset->close(); ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No se encontraron resultados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
    </html>
    <?php
}
?>
