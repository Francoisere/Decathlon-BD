<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos para acceder (Administrador = 1, Líder = 3)
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['PODER'] != 1 && $_SESSION['usuario']['PODER'] != 3)) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Verificar si el RUT del candidato ha sido pasado como parámetro
if (!isset($_GET['rut_candidato'])) {
    die("Error: No se proporcionó el RUT del candidato.");
}

$rut_candidato = $_GET['rut_candidato'];

// Consultar información del candidato
$query_candidato = "SELECT c.RUT_CANDIDATO, c.Nombre1, c.Apellido1, c.Nombre2, c.Apellido2, c.Correo, ca.Nombre_Cargo, ca.ID_CARGO
                    FROM JS_FS_FV_VV_Candidato c
                    LEFT JOIN JS_FS_FV_VV_Cargo ca ON c.Cargo_Postular = ca.ID_CARGO
                    WHERE c.RUT_CANDIDATO = :RUT_CANDIDATO";
$stmt_candidato = oci_parse($conn, $query_candidato);
oci_bind_by_name($stmt_candidato, ':RUT_CANDIDATO', $rut_candidato);

if (!oci_execute($stmt_candidato)) {
    $e = oci_error($stmt_candidato);
    die("Error al obtener información del candidato: " . htmlspecialchars($e['message']));
}

$candidato = oci_fetch_assoc($stmt_candidato);

if (!$candidato) {
    die("Error: No se encontró información del candidato.");
}

// Obtener la lista de deportes
$query_deportes = "SELECT ID_Deporte, Deporte FROM JS_FS_FV_VV_Deportes ORDER BY Deporte";
$stmt_deportes = oci_parse($conn, $query_deportes);

if (!oci_execute($stmt_deportes)) {
    $e = oci_error($stmt_deportes);
    die("Error al obtener la lista de deportes: " . htmlspecialchars($e['message']));
}

// Obtener la lista de horas disponibles
$query_horas_disponibles = "SELECT ID_Horas, Horas FROM JS_FS_FV_VV_Horas_Disponibles ORDER BY Horas";
$stmt_horas = oci_parse($conn, $query_horas_disponibles);

if (!oci_execute($stmt_horas)) {
    $e = oci_error($stmt_horas);
    die("Error al obtener la lista de horas disponibles: " . htmlspecialchars($e['message']));
}

// Obtener la lista de tipos de contrato
$query_tipos_contrato = "SELECT ID_Tipo_Contrato, Nombre_Tipo_Contrato FROM JS_FS_FV_VV_Tipo_Contratos ORDER BY Nombre_Tipo_Contrato";
$stmt_tipos_contrato = oci_parse($conn, $query_tipos_contrato);

if (!oci_execute($stmt_tipos_contrato)) {
    $e = oci_error($stmt_tipos_contrato);
    die("Error al obtener la lista de tipos de contrato: " . htmlspecialchars($e['message']));
}

/*
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duracion_meses = $_POST['duracion_meses'];
    $id_horas_asignadas = $_POST['horas_asignadas'];
    $salario = $_POST['salario'];
    $renovacion_automatica = isset($_POST['renovacion_automatica']) ? 1 : 0;
    $id_deporte = $_POST['deporte']; // Deporte seleccionado
    $id_tipo_contrato = $_POST['tipo_contrato']; // Tipo de contrato seleccionado

    // Obtener el valor de la secuencia para ID_Contrato
    $query_get_nextval = "SELECT SEQ_CONTRATO.NEXTVAL AS ID_CONTRATO FROM DUAL";
    $stmt_get_nextval = oci_parse($conn, $query_get_nextval);

    if (!oci_execute($stmt_get_nextval)) {
        $error = oci_error($stmt_get_nextval);
        die("Error al obtener el siguiente valor de la secuencia: " . $error['message']);
    }

    $row_nextval = oci_fetch_assoc($stmt_get_nextval);
    $id_contrato = $row_nextval['ID_CONTRATO'];

    // Insertar el contrato en la base de datos
    $query_insert_contrato = "INSERT INTO JS_FS_FV_VV_Contratos
                              (ID_Contrato, Duracion_Meses, Renovacion_Automatica, ID_Cargo, Horas_Asignadas, Fecha_Contratacion, Deporte_Asignado, RUT_CANDIDATO, Salario_Asignado, Contrato)
                              VALUES (:id_contrato, :duracion_meses, :renovacion_automatica, :id_cargo, :id_horas_asignadas, SYSDATE, :id_deporte, :rut_candidato, :salario, :id_tipo_contrato)";
    $stmt_insert = oci_parse($conn, $query_insert_contrato);

    oci_bind_by_name($stmt_insert, ':id_contrato', $id_contrato);
    oci_bind_by_name($stmt_insert, ':duracion_meses', $duracion_meses);
    oci_bind_by_name($stmt_insert, ':renovacion_automatica', $renovacion_automatica);
    oci_bind_by_name($stmt_insert, ':id_cargo', $candidato['ID_CARGO']);
    oci_bind_by_name($stmt_insert, ':id_horas_asignadas', $id_horas_asignadas);
    oci_bind_by_name($stmt_insert, ':id_deporte', $id_deporte);
    oci_bind_by_name($stmt_insert, ':rut_candidato', $rut_candidato);
    oci_bind_by_name($stmt_insert, ':salario', $salario);
    oci_bind_by_name($stmt_insert, ':id_tipo_contrato', $id_tipo_contrato);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Contrato creado con éxito para el candidato " . htmlspecialchars($candidato['NOMBRE1']) . " " . htmlspecialchars($candidato['APELLIDO1']) . ".";
    } else {
        $error = oci_error($stmt_insert);
        die("Error al insertar el contrato: " . $error['message']);
    }
}
*/



// Manejar la creación del contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duracion_meses = $_POST['duracion_meses'];
    $id_horas_asignadas = $_POST['horas_asignadas'];
    $salario = $_POST['salario'];
    $renovacion_automatica = isset($_POST['renovacion_automatica']) ? 1 : 0;
    $id_deporte = $_POST['deporte']; // Deporte seleccionado
    $id_tipo_contrato = $_POST['tipo_contrato']; // Tipo de contrato seleccionado
    $id_contrato = null;

    $query_get_seq = "SELECT SEQ_CONTRATO.NEXTVAL AS ID_CONTRATO FROM DUAL";
    $stmt_seq = oci_parse($conn, $query_get_seq);
    if (!$stmt_seq) {
        $error = oci_error($conn);
        die("Error al preparar la consulta de secuencia: " . htmlspecialchars($error['message']));
    }
    // Ejecutar la consulta
    if (!oci_execute($stmt_seq)) {
        $error = oci_error($stmt_seq);
        die("Error al ejecutar la consulta de secuencia: " . htmlspecialchars($error['message']));
    }
    // Obtener el valor de la secuencia
    $row = oci_fetch_assoc($stmt_seq);
    if ($row && isset($row['ID_CONTRATO'])) {
        $id_contrato = $row['ID_CONTRATO'];
    } else {
        die("No se pudo obtener el valor de la secuencia.");
    }
    // Insertar el contrato en la base de datos
    $query_insert_contrato = "INSERT INTO JS_FS_FV_VV_Contratos
                              (ID_Contrato, Duracion_Meses, Renovacion_Automatica, ID_Cargo, Horas_Asignadas, Fecha_Contratacion, Deporte_Asignado, RUT_CANDIDATO, Salario_Asignado, Contrato)
                              VALUES (:id_contrato, :duracion_meses, :renovacion_automatica, :id_cargo, :id_horas_asignadas, SYSDATE, :id_deporte, :rut_candidato, :salario, :id_tipo_contrato)";
    $stmt_insert = oci_parse($conn, $query_insert_contrato);

    oci_bind_by_name($stmt_insert, ':id_contrato', $id_contrato);
    oci_bind_by_name($stmt_insert, ':duracion_meses', $duracion_meses);
    oci_bind_by_name($stmt_insert, ':renovacion_automatica', $renovacion_automatica);
    oci_bind_by_name($stmt_insert, ':id_cargo', $candidato['ID_CARGO']);
    oci_bind_by_name($stmt_insert, ':id_horas_asignadas', $id_horas_asignadas);
    oci_bind_by_name($stmt_insert, ':id_deporte', $id_deporte);
    oci_bind_by_name($stmt_insert, ':rut_candidato', $rut_candidato);
    oci_bind_by_name($stmt_insert, ':salario', $salario);
    oci_bind_by_name($stmt_insert, ':id_tipo_contrato', $id_tipo_contrato);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Contrato creado con éxito para el candidato " . htmlspecialchars($candidato['NOMBRE1']) . " " . htmlspecialchars($candidato['APELLIDO1']) . ".";

        // Crear el usuario en la tabla de usuarios después de crear el contrato
        $query_insert_usuario = "
        INSERT INTO JS_FS_FV_VV_Usuario 
        (RUT_USUARIO, NOMBRE1, NOMBRE2, APELLIDO1, APELLIDO2, CLAVE, TELEFONO, SALARIO, ID_CARGO, ID_CONTRATO, ID_PREVISION, ID_CC, CORREO) 
        VALUES 
        (:RUT_USUARIO, :NOMBRE1, :NOMBRE2, :APELLIDO1, :APELLIDO2, JS_FS_FV_VV_GENERA_CLAVE(:NOMBRE1, :APELLIDO1), JS_FS_FV_VV_GENERA_TELEFONO, :SALARIO, :ID_CARGO, :ID_CONTRATO, 1, 1,  JS_FS_FV_VV_GENERA_CORREO(:NOMBRE1, :APELLIDO1, :APELLIDO2))"; // ID_PREVISION and ID_CC set to 1

        // Preparar la consulta
        $stmt_insert_usuario = oci_parse($conn, $query_insert_usuario);

        // Vincular valores
        oci_bind_by_name($stmt_insert_usuario, ':RUT_USUARIO', $candidato['RUT_CANDIDATO']);
        oci_bind_by_name($stmt_insert_usuario, ':NOMBRE1', $candidato['NOMBRE1']);
        oci_bind_by_name($stmt_insert_usuario, ':NOMBRE2', $candidato['NOMBRE2']);
        oci_bind_by_name($stmt_insert_usuario, ':APELLIDO1', $candidato['APELLIDO1']);
        oci_bind_by_name($stmt_insert_usuario, ':APELLIDO2', $candidato['APELLIDO2']);
        oci_bind_by_name($stmt_insert_usuario, ':SALARIO', $salario);
        oci_bind_by_name($stmt_insert_usuario, ':ID_CARGO', $candidato['ID_CARGO']);
        oci_bind_by_name($stmt_insert_usuario, ':ID_CONTRATO', $id_contrato);

        // Ejecutar el insert del usuario
        if (!oci_execute($stmt_insert_usuario)) {
            $e = oci_error($stmt_insert_usuario);
            $mensaje .= " Error al crear el usuario: " . htmlspecialchars($e['message']);
        }
    } else {
        $e = oci_error($stmt_insert);
        $mensaje = "Error al crear el contrato: " . htmlspecialchars($e['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Contrato</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }

        h1 {
            color: #007bff;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .mensaje {
            color: green;
            font-weight: bold;
        }

        .error {
            color: red;
            font-weight: bold;
        }
    </style>

    <script>
        function confirmExit() {
            var confirmation = confirm("¿Estás seguro de que deseas salir? Se perderá la información ingresada y el candidato dejará de ser una opción.");
            return confirmation;
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Crear Contrato</h1>
        <?php if (isset($mensaje)): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <p><strong>Candidato:</strong> <?php echo htmlspecialchars($candidato['NOMBRE1'] . " " . $candidato['APELLIDO1'] . " " . $candidato['NOMBRE2'] . " " . $candidato['APELLIDO2']); ?></p>
        <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato['NOMBRE_CARGO']); ?></p>
        <form method="POST">
            <label for="duracion_meses">Duración del Contrato (en meses):</label>
            <input type="number" id="duracion_meses" name="duracion_meses" min="1" required>

            <label for="horas_asignadas">Horas Asignadas:</label>
            <select id="horas_asignadas" name="horas_asignadas" required>
                <option value="">-- Seleccione una cantidad de horas --</option>
                <?php while ($hora = oci_fetch_assoc($stmt_horas)): ?>
                    <option value="<?php echo $hora['ID_HORAS']; ?>">
                        <?php echo htmlspecialchars($hora['HORAS']); ?> horas
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="salario">Salario:</label>
            <input type="number" id="salario" name="salario" min="1" required>

            <label for="deporte">Deporte:</label>
            <select id="deporte" name="deporte" required>
                <option value="">-- Seleccione un deporte --</option>
                <?php while ($deporte = oci_fetch_assoc($stmt_deportes)): ?>
                    <option value="<?php echo htmlspecialchars($deporte['ID_DEPORTE']); ?>">
                        <?php echo htmlspecialchars($deporte['DEPORTE']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="tipo_contrato">Tipo de Contrato:</label>
            <select id="tipo_contrato" name="tipo_contrato" required>
                <option value="">-- Seleccione un tipo de contrato --</option>
                <?php while ($tipo = oci_fetch_assoc($stmt_tipos_contrato)): ?>
                    <option value="<?php echo htmlspecialchars($tipo['ID_TIPO_CONTRATO']); ?>">
                        <?php echo htmlspecialchars($tipo['NOMBRE_TIPO_CONTRATO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="renovacion_automatica">Renovación Automática:</label>
            <input type="checkbox" id="renovacion_automatica" name="renovacion_automatica">

            <button type="submit">Crear Contrato</button>
        </form>

        <!-- Botón para volver a la página de acceso -->
        <a href="accediste.php" class="back-button" style="background-color: #007bff; color: white; padding: 10px 20px; font-size: 14px; text-decoration: none; border-radius: 5px;" onclick="return confirmExit();">← Volver al menú</a>
    </div>
</body>

</html>