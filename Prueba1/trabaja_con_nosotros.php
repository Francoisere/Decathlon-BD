<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $rut = $_POST['rut'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $deporte = $_POST['deporte'];
    $curriculum = $_FILES['curriculum'];

    // Verificar si se subió un archivo
    if ($curriculum['error'] === UPLOAD_ERR_OK) {
        $curriculum_path = 'uploads/' . basename($curriculum['name']);
        if (move_uploaded_file($curriculum['tmp_name'], $curriculum_path)) {
            // Guardar los datos en la base de datos
            $query = "INSERT INTO JS_FS_FV_VV_Postulantes (Nombre, RUT, Fecha_Nacimiento, Deporte, Curriculum) 
                      VALUES (:nombre, :rut, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :deporte, :curriculum_path)";
            $stmt = oci_parse($conn, $query);

            oci_bind_by_name($stmt, ':nombre', $nombre);
            oci_bind_by_name($stmt, ':rut', $rut);
            oci_bind_by_name($stmt, ':fecha_nacimiento', $fecha_nacimiento);
            oci_bind_by_name($stmt, ':deporte', $deporte);
            oci_bind_by_name($stmt, ':curriculum_path', $curriculum_path);

            if (oci_execute($stmt)) {
                $mensaje = "¡Gracias por postular! Nos pondremos en contacto contigo.";
            } else {
                $mensaje = "Error al enviar la postulación. Inténtalo nuevamente.";
            }

            oci_free_statement($stmt);
        } else {
            $mensaje = "Error al subir el archivo de currículum.";
        }
    } else {
        $mensaje = "Error al subir el archivo de currículum.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabaja con Nosotros</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 500px;
        }

        h1 {
            color: #0056b3;
            margin-bottom: 1rem;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            color: #0056b3;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        input, select {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        button {
            background-color: #007bff;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .message {
            text-align: center;
            color: green;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h1>Trabaja con Nosotros</h1>
        <?php if (isset($mensaje)): ?>
            <p class="message"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ingrese su nombre" required>

            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" placeholder="Ingrese su RUT" required>

            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

            <label for="deporte">Deporte o Pasión:</label>
            <input type="text" id="deporte" name="deporte" list="deportes" placeholder="Escriba o elija un deporte" required>
            <datalist id="deportes">
                <option value="Fútbol"></option>
                <option value="Básquetbol"></option>
                <option value="Tenis"></option>
                <option value="Natación"></option>
                <option value="Ciclismo"></option>
                <option value="Voleibol"></option>
                <option value="Atletismo"></option>
                <option value="Yoga"></option>
                <option value="Escalada"></option>
                <option value="Surf"></option>
                <option value="Boxeo"></option>
                <option value="Esquí"></option>
                <option value="Snowboard"></option>
                <option value="Béisbol"></option>
                <option value="Karate"></option>
                <option value="Taekwondo"></option>
                <option value="Judo"></option>
                <option value="Artes Marciales Mixtas"></option>
                <option value="Golf"></option>
                <option value="Equitación"></option>
            </datalist>

            <label for="curriculum">Currículum (PDF):</label>
            <input type="file" id="curriculum" name="curriculum" accept=".pdf" required>

            <button type="submit">Enviar Postulación</button>
        </form>
    </div>

    <script>
        // Filtrado interactivo de deportes
        document.getElementById('deporte').addEventListener('input', function () {
            const input = this.value.toLowerCase();
            const options = document.querySelectorAll('#deportes option');
            options.forEach(option => {
                if (option.value.toLowerCase().includes(input)) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
