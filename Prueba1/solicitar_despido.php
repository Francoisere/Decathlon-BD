<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

$usuario = $_SESSION['usuario'];
$rutUsuario = $usuario['RUT_USUARIO'];

// Verificar el nivel de poder del usuario logueado
$query_poder = "SELECT c.PODER, u.ID_CARGO
                FROM JS_FS_FV_VV_Usuario u
                JOIN JS_FS_FV_VV_Cargo c ON u.ID_Cargo = c.ID_Cargo
                WHERE u.RUT_USUARIO = :rut_usuario";

$stmt_poder = oci_parse($conn, $query_poder);
oci_bind_by_name($stmt_poder, ':rut_usuario', $rutUsuario);

if (!oci_execute($stmt_poder)) {
    $e = oci_error($stmt_poder);
    die("Error al verificar los permisos del usuario: " . htmlspecialchars($e['message']));
}

$row_poder = oci_fetch_assoc($stmt_poder);

if (!$row_poder) {
    die("Error: No se encontró información del usuario con RUT: " . htmlspecialchars($rutUsuario));
}

// Nivel de poder del usuario logueado
$poderUsuario = $row_poder['PODER'];

// Obtener la lista de empleados que pueden ser despedidos
if ($poderUsuario == 1) {
    // Administradores (nivel 1) pueden despedir a Líderes (nivel 3) y Vendedores (nivel 2)
    $query_empleados = "SELECT u.RUT_USUARIO, u.NOMBRE1 || ' ' || u.APELLIDO1 AS NOMBRE_COMPLETO
                        FROM JS_FS_FV_VV_Usuario u
                        JOIN JS_FS_FV_VV_Cargo c ON u.ID_Cargo = c.ID_Cargo
                        WHERE c.PODER IN (2, 3)"; // Líderes y Vendedores
} elseif ($poderUsuario == 3) {
    // Líderes (nivel 3) solo pueden despedir a Vendedores (nivel 2)
    $query_empleados = "SELECT u.RUT_USUARIO, u.NOMBRE1 || ' ' || u.APELLIDO1 AS NOMBRE_COMPLETO
                        FROM JS_FS_FV_VV_Usuario u
                        JOIN JS_FS_FV_VV_Cargo c ON u.ID_Cargo = c.ID_Cargo
                        WHERE c.PODER = 2"; // Solo Vendedores
} else {
    die("Error: No tienes permisos para despedir a otros usuarios.");
}

$stmt_empleados = oci_parse($conn, $query_empleados);

if (!oci_execute($stmt_empleados)) {
    $e = oci_error($stmt_empleados);
    die("Error al obtener la lista de empleados: " . htmlspecialchars($e['message']));
}

// Manejar el envío de la solicitud de despido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_despedido = $_POST['rut_despedido'];
    $id_motivo = $_POST['id_motivo'];
    $comentario = $_POST['comentario'];

    // Validar si el RUT ingresado corresponde a un usuario que se puede despedir
    $query_validar_rut = "SELECT COUNT(*) AS TOTAL 
                          FROM JS_FS_FV_VV_Usuario u
                          JOIN JS_FS_FV_VV_Cargo c ON u.ID_Cargo = c.ID_Cargo
                          WHERE u.RUT_USUARIO = :rut_despedido
                          AND (
                              (:poder_usuario = 1 AND c.PODER IN (2, 3)) OR
                              (:poder_usuario = 3 AND c.PODER = 2)
                          )";

    $stmt_validar_rut = oci_parse($conn, $query_validar_rut);
    oci_bind_by_name($stmt_validar_rut, ':rut_despedido', $rut_despedido);
    oci_bind_by_name($stmt_validar_rut, ':poder_usuario', $poderUsuario);
    oci_execute($stmt_validar_rut);

    $row_validar_rut = oci_fetch_assoc($stmt_validar_rut);
    if ($row_validar_rut['TOTAL'] == 0) {
        die("Error: No tienes permisos para despedir a este usuario.");
    }

    // Insertar la nueva solicitud de despido
    $query_insert = "INSERT INTO JS_FS_FV_VV_Solicitud_Despido 
                     (ID_SOLICITUD, ID_MOTIVO_SOLICITUD, FINIQUITO, FECHA_SOLICITUD, ID_USUARIOSOLICITUD, ESTADO_SOLICITUD, ID_DESPEDIDO, COMENTARIO) 
                     VALUES (SEQ_SOLICITUD_DESPIDO.NEXTVAL, :id_motivo, NULL, SYSDATE, :rut_usuario, 2, :rut_despedido, :comentario)";

    $stmt_insert = oci_parse($conn, $query_insert);
    oci_bind_by_name($stmt_insert, ':id_motivo', $id_motivo);
    oci_bind_by_name($stmt_insert, ':rut_usuario', $rutUsuario);
    oci_bind_by_name($stmt_insert, ':rut_despedido', $rut_despedido);
    oci_bind_by_name($stmt_insert, ':comentario', $comentario);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Solicitud de despido enviada con éxito.";
    } else {
        $e = oci_error($stmt_insert);
        $mensaje = "Error al registrar la solicitud: " . htmlspecialchars($e['message']);
    }
}

// Obtener la lista de motivos de despido
$query_motivos = "SELECT ID_MOTIVO_DESPIDO, MOTIVO FROM JS_FS_FV_VV_Motivos_Despidos";
$stmt_motivos = oci_parse($conn, $query_motivos);

if (!oci_execute($stmt_motivos)) {
    $e = oci_error($stmt_motivos);
    die("Error al obtener los motivos de despido: " . htmlspecialchars($e['message']));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Despido</title>
    <style>


        body {
            font-family: Arial, sans-serif;
            background-color: #e3f2fd; /* Azul claro como fallback */
            background-image: url('https://www.2playbook.com/uploads/s1/15/60/83/decathlon-tienda-recurso_14_744x403.jpeg'); /* Ruta de la imagen de fondo */
            background-size: cover; /* Ajustar el tamaño de la imagen para cubrir toda la pantalla */
            background-position: center; /* Centrar la imagen */
            background-attachment: fixed; /* Fijar la imagen al hacer scroll */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            position: relative; /* Necesario para posicionar el pseudo-elemento */
        }
        
        /* Pseudo-elemento para crear la capa difusa */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            backdrop-filter: blur(10px);
            filter: blur(16px); /* Desenfoque de la imagen */
            z-index: -1; /* Colocar la capa detrás del contenido */
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

        .container h1 {
            color: #0056b3;
            margin-bottom: 1rem;
        }

        .container form {
            display: flex;
            flex-direction: column;
        }

        .container label {
            color: #0056b3;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-align: left;
        }

        .container select, .container textarea {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        .container button {
            background-color: #007bff;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .container button:hover {
            background-color: #0056b3;
        }

        .mensaje {
            color: green;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .back-button {
            position: fixed; /* Cambiar a fixed para que esté en una posición fija */
            top: 20px; /* Separación desde la parte superior */
            left: 20px; /* Separación desde la izquierda */
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
            background-color: #0056b3;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <a href="accediste.php" class="back-button">← Volver al menú</a>
    <div class="container">
        <h1>Solicitar Despido</h1>
        <?php if (isset($mensaje)): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <label for="rut_despedido">Selecciona el empleado a despedir:</label>
            <select id="rut_despedido" name="rut_despedido" required>
                <option value="" disabled selected>Seleccione un empleado</option>
                <?php while ($empleado = oci_fetch_assoc($stmt_empleados)): ?>
                    <option value="<?php echo htmlspecialchars($empleado['RUT_USUARIO']); ?>">
                        <?php echo htmlspecialchars($empleado['NOMBRE_COMPLETO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="id_motivo">Motivo del Despido:</label>
            <select id="id_motivo" name="id_motivo" required>
                <option value="" disabled selected>Seleccione un motivo</option>
                <?php while ($motivo = oci_fetch_assoc($stmt_motivos)): ?>
                    <option value="<?php echo htmlspecialchars($motivo['ID_MOTIVO_DESPIDO']); ?>">
                        <?php echo htmlspecialchars($motivo['MOTIVO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="comentario">Comentario Adicional:</label>
            <textarea id="comentario" name="comentario" rows="4"></textarea>

            <button type="submit">Enviar Solicitud</button>
        </form>
    </div>
</body>
</html>