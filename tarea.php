<?php
require_once('../config.php');
require_login();

global $DB;

// Obtener la lista de cursos de la base de datos
$sql = "SELECT id AS idcurso, shortname AS nombrecorto FROM {course}";
$cursos = $DB->get_recordset_sql($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Consulta de Tarea</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Consulta en Foros</h2>
    <form action="usuario-tarea.php" method="post">
        <div class="form-group">
            <label for="courseId">Seleccione un Curso</label>
            <select class="form-control" id="courseId" name="course_id" required>
                <?php foreach ($cursos as $curso): ?>
                    <option value="<?php echo $curso->idcurso; ?>"><?php echo htmlspecialchars($curso->nombrecorto); ?></option>
                <?php endforeach; ?>
                <?php $cursos->close(); // Cierra el recordset ?>
            </select>
        </div>
        <div class="form-group">
            <label for="userLastname">Apellido del Usuario</label>
            <input type="text" class="form-control" id="userLastname" name="user_lastname" placeholder="Ingrese parte del Apellido del Usuario" required>
        </div>
        <button type="submit" class="btn btn-primary">Consultar</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
