<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

$usuario = $_SESSION['usuario'];
$rutUsuario = $usuario['RUT_USUARIO'];

// Verificar si el usuario ya tiene una solicitud pendiente
$queryVerificar = "SELECT COUNT(*) AS PENDIENTES 
                   FROM JS_FS_FV_VV_Solicitud_Renuncia 
                   WHERE Id_UsuarioRenuncia = :RUT_USUARIO AND Estado_Solicitud = 2"; // 2 es el estado "Pendiente"
$stmtVerificar = oci_parse($conn, $queryVerificar);
oci_bind_by_name($stmtVerificar, ':RUT_USUARIO', $rutUsuario);

if (!oci_execute($stmtVerificar)) {
    $e = oci_error($stmtVerificar);
    die("Error al verificar solicitudes pendientes: " . htmlspecialchars($e['message']));
}

$rowVerificar = oci_fetch_assoc($stmtVerificar);
$solicitudPendiente = $rowVerificar['PENDIENTES'] > 0;

// Procesar la solicitud de renuncia si no hay solicitudes pendientes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$solicitudPendiente) {
    $motivoRenuncia = $_POST['motivo'];
    $comentario = trim($_POST['comentario']);
    
    if (empty($motivoRenuncia)) {
        $errorMessage = "Error: Debes seleccionar un motivo de renuncia.";
    } else {
        $queryInsert = "INSERT INTO JS_FS_FV_VV_Solicitud_Renuncia 
                        (ID_Solicitud, ID_Motivo_Renuncia, Fecha_Solicitud, Id_UsuarioRenuncia, Estado_Solicitud, Comentario) 
                        VALUES (SEQ_RENUNCIA.NEXTVAL, :ID_MOTIVO_RENUNCIA, SYSDATE, :RUT_USUARIO, 2, :COMENTARIO)";

        $stmtInsert = oci_parse($conn, $queryInsert);
        oci_bind_by_name($stmtInsert, ':ID_MOTIVO_RENUNCIA', $motivoRenuncia);
        oci_bind_by_name($stmtInsert, ':RUT_USUARIO', $rutUsuario);
        oci_bind_by_name($stmtInsert, ':COMENTARIO', $comentario);

        if (oci_execute($stmtInsert)) {
            $successMessage = "Solicitud enviada correctamente.";
            header("Location: solicitar_renuncia.php?success=1");
            exit;
        } else {
            $e = oci_error($stmtInsert);
            $errorMessage = "Error al enviar la solicitud: " . htmlspecialchars($e['message']);
        }
    }
}

// Mostrar mensaje de éxito si redirigido después de enviar la solicitud
if (isset($_GET['success'])) {
    $successMessage = "Solicitud enviada correctamente.";
}

// Obtener los motivos de renuncia desde la base de datos
$queryMotivos = "SELECT ID_Motivo_Renuncia, Motivo FROM JS_FS_FV_VV_Motivo_Renuncia";
$stmtMotivos = oci_parse($conn, $queryMotivos);

if (!oci_execute($stmtMotivos)) {
    $e = oci_error($stmtMotivos);
    die("Error al obtener los motivos: " . htmlspecialchars($e['message']));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Renuncia</title>
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
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            position: relative;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }

        h1 {
            color: #007bff;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        textarea, select {
            width: 100%;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }

        button:hover {
            background-color: #0056b3;
        }

        .success, .error, .info {
            font-size: 1rem;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
        }

        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
        }

        .info {
            color: #004085;
            background-color: #cce5ff;
            border: 1px solid #b8daff;
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
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Botón de volver -->
    <a href="accediste.php" class="back-button">← Volver al menú</a>

    <div class="container">
        <h1>Solicitar Renuncia</h1>

        <!-- Mostrar mensajes -->
        <?php if (!empty($successMessage)): ?>
            <div class="success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php elseif ($solicitudPendiente): ?>
            <div class="info">Ya tienes una solicitud pendiente. Espera a que sea respondida antes de enviar una nueva.</div>
        <?php elseif (!empty($errorMessage)): ?>
            <div class="error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="motivo">Motivo de la renuncia:</label>
            <select name="motivo" id="motivo" required <?php echo $solicitudPendiente ? 'disabled' : ''; ?>>
                <option value="">Seleccione un motivo</option>
                <?php while ($row = oci_fetch_assoc($stmtMotivos)): ?>
                    <option value="<?php echo htmlspecialchars($row['ID_MOTIVO_RENUNCIA']); ?>">
                        <?php echo htmlspecialchars($row['MOTIVO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <label for="comentario">Comentario adicional:</label>
            <textarea name="comentario" id="comentario" <?php echo $solicitudPendiente ? 'disabled' : ''; ?>></textarea>
            
            <button type="submit" <?php echo $solicitudPendiente ? 'disabled' : ''; ?>>
                Enviar Solicitud
            </button>
        </form>
    </div>
</body>
</html>
