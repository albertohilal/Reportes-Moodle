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
            mdl_course.id,
            mdl_course.shortname AS curso,
            mdl_forum.name AS tarea, 
            mdl_forum.intro AS intro,
            mdl_user.lastname AS apellido,
            mdl_user.firstname AS nombre,
            mdl_forum_posts.subject AS subject,
            mdl_forum_posts.message AS mensaje
        FROM 
            {forum} mdl_forum
        LEFT JOIN 
            {forum_discussions} mdl_forum_discussions ON mdl_forum_discussions.forum = mdl_forum.id
        LEFT JOIN 
            {forum_posts} mdl_forum_posts ON mdl_forum_posts.discussion = mdl_forum_discussions.id
        LEFT JOIN 
            {user} mdl_user ON mdl_user.id = mdl_forum_discussions.userid
        LEFT JOIN 
            {course} mdl_course ON mdl_course.id = mdl_forum.course
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
        <title>Resultados de la Consulta</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>

    <div class="container mt-5">
        <h2 class="mb-4">Resultados de la Consulta</h2>
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
