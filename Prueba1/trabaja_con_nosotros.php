<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $rut = $_POST['rut'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $deporte = $_POST['deporte'];
    $cargo = $_POST['cargo_postular'];
    $curriculum = $_FILES['curriculum'];
    $correo = $_POST['correo']; // Nuevo campo para el correo

    // Verificar si la edad es mayor o igual a 18 años
    $fecha_nacimiento_date = new DateTime($fecha_nacimiento);
    $fecha_actual = new DateTime();
    $edad = $fecha_actual->diff($fecha_nacimiento_date)->y;

    if ($edad < 18) {
        $mensaje = "Debes tener al menos 18 años para postularte.";
        $mensaje_clase = "error";
    } else {
        // Verificar si el usuario ya envió una solicitud con el mismo RUT
        $query_check = "SELECT COUNT(*) AS EXISTE 
                        FROM JS_FS_FV_VV_CANDIDATO 
                        WHERE RUT_CANDIDATO = :rut OR (Nombre1 || ' ' || Apellido1) = :nombre";
        $stmt_check = oci_parse($conn, $query_check);
        oci_bind_by_name($stmt_check, ':rut', $rut);
        oci_bind_by_name($stmt_check, ':nombre', $nombre);
        oci_execute($stmt_check);
        $result = oci_fetch_assoc($stmt_check);

        if ($result['EXISTE'] > 0) {
            $mensaje = "Ya has enviado una solicitud con este RUT. No puedes postular nuevamente.";
            $mensaje_clase = "error";
        } else {
            // Verificar si el RUT está en la tabla de despidos
            $query_despido = "SELECT COUNT(*) AS DESPEDIDO 
                              FROM JS_FS_FV_VV_Solicitud_Despido 
                              WHERE ID_DESPEDIDO = :rut";
            $stmt_despido = oci_parse($conn, $query_despido);
            oci_bind_by_name($stmt_despido, ':rut', $rut);
            oci_execute($stmt_despido);
            $despedido = oci_fetch_assoc($stmt_despido)['DESPEDIDO'];

            if ($despedido > 0) {
                $mensaje = "Este RUT corresponde a un empleado despedido y no puede postularse.";
                $mensaje_clase = "error";
            } else {
                // Separar el nombre completo en partes
                $nombres = explode(' ', $nombre);
                $nombre1 = $nombres[0];
                $nombre2 = isset($nombres[1]) ? $nombres[1] : '';
                $apellido1 = isset($nombres[2]) ? $nombres[2] : '';
                $apellido2 = isset($nombres[3]) ? $nombres[3] : '';

                // Verificar si se subió un archivo
                if ($curriculum['error'] === UPLOAD_ERR_OK) {
                    $curriculum_path = 'uploads/' . basename($curriculum['name']);
                    if (move_uploaded_file($curriculum['tmp_name'], $curriculum_path)) {
                        // Guardar los datos en la base de datos
                        $query = "INSERT INTO JS_FS_FV_VV_CANDIDATO 
                                  (Nombre1, Nombre2, Apellido1, Apellido2, RUT_CANDIDATO, Fecha_Nacimiento, Deporte_interes, Cargo_Postular, Curriculum, Correo) 
                                  VALUES (:nombre1, :nombre2, :apellido1, :apellido2, :rut, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :deporte, :cargo_postular, :curriculum_path, :correo)";
                        $stmt = oci_parse($conn, $query);

                        oci_bind_by_name($stmt, ':nombre1', $nombre1);
                        oci_bind_by_name($stmt, ':nombre2', $nombre2);
                        oci_bind_by_name($stmt, ':apellido1', $apellido1);
                        oci_bind_by_name($stmt, ':apellido2', $apellido2);
                        oci_bind_by_name($stmt, ':rut', $rut);
                        oci_bind_by_name($stmt, ':fecha_nacimiento', $fecha_nacimiento);
                        oci_bind_by_name($stmt, ':deporte', $deporte);
                        oci_bind_by_name($stmt, ':cargo_postular', $cargo);
                        oci_bind_by_name($stmt, ':curriculum_path', $curriculum_path);
                        oci_bind_by_name($stmt, ':correo', $correo);

                        if (oci_execute($stmt)) {
                            $mensaje = "¡Gracias por postular! Nos pondremos en contacto contigo.";
                            $mensaje_clase = "success";
                        } else {
                            $mensaje = "Error al enviar la postulación. Inténtalo nuevamente.";
                            $mensaje_clase = "error";
                        }

                        oci_free_statement($stmt);
                    } else {
                        $mensaje = "Error al subir el archivo de currículum.";
                        $mensaje_clase = "error";
                    }
                } else {
                    $mensaje = "Error al subir el archivo de currículum.";
                    $mensaje_clase = "error";
                }
            }
        }
    }
}

// Obtener la lista de deportes desde la base de datos
$query_deportes = "SELECT ID_Deporte, Deporte FROM JS_FS_FV_VV_Deportes ORDER BY Deporte";
$stmt_deportes = oci_parse($conn, $query_deportes);
if (!oci_execute($stmt_deportes)) {
    $e = oci_error($stmt_deportes);
    die("Error al obtener la lista de deportes: " . htmlspecialchars($e['message']));
}

// Obtener la lista de cargos desde la base de datos
$query_cargos = "SELECT ID_Cargo, Nombre_Cargo FROM JS_FS_FV_VV_Cargo ORDER BY Nombre_Cargo";
$stmt_cargos = oci_parse($conn, $query_cargos);
if (!oci_execute($stmt_cargos)) {
    $e = oci_error($stmt_cargos);
    die("Error al obtener la lista de cargos: " . htmlspecialchars($e['message']));
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
            margin-top: 1rem;
            font-weight: bold;
        }

        .message.success {
            color: green;
        }

        .message.error {
            color: red;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            color: #0056b3;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            background-color: #ffffff;
            border: 2px solid #0056b3;
            padding: 10px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
        .back-button:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="login.php" class="back-button">← Volver al menú</a>
        <h1>Trabaja con Nosotros</h1>
        <?php if (isset($mensaje)): ?>
            <p class="message <?php echo htmlspecialchars($mensaje_clase); ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre Completo:</label>
            <input type="text" id="nombre" name="nombre" placeholder="Ingrese su nombre" required>

            <label for="rut">RUT:</label>
            <input type="text" id="rut" name="rut" placeholder="Ingrese su RUT completo sin guion" required>

            <label for="correo">Correo Electrónico:</label>
            <input type="email" id="correo" name="correo" placeholder="Ingrese su correo" required>

            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

            <label for="deporte">Deporte o Pasión:</label>
            <select id="deporte" name="deporte" required>
                <option value="">-- Seleccione un deporte --</option>
                <?php while ($deporte = oci_fetch_assoc($stmt_deportes)): ?>
                    <option value="<?php echo htmlspecialchars($deporte['ID_DEPORTE']); ?>">
                        <?php echo htmlspecialchars($deporte['DEPORTE']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="cargo_postular">Cargo a Postular:</label>
            <select id="cargo_postular" name="cargo_postular" required>
                <option value="">-- Seleccione un cargo --</option>
                <?php while ($cargo = oci_fetch_assoc($stmt_cargos)): ?>
                    <option value="<?php echo htmlspecialchars($cargo['ID_CARGO']); ?>">
                        <?php echo htmlspecialchars($cargo['NOMBRE_CARGO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="curriculum">Currículum (PDF):</label>
            <input type="file" id="curriculum" name="curriculum" accept=".pdf" required>

            <button type="submit">Enviar Postulación</button>
        </form>
    </div>
</body>
</html>
