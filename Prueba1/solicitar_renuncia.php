<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

if ($_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Procesar la solicitud de renuncia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $motivo = $_POST['id_motivo_renuncia']; // ID del motivo de renuncia
    $finiquito = $_POST['finiquito']; // Valor del finiquito
    $fecha_solicitud = date('Y-m-d'); // Fecha actual
    $estado_solicitud = 1; // Estado inicial de la solicitud (1 = Pendiente)

    // Consulta para insertar la solicitud de renuncia
    $query = "INSERT INTO JS_FS_FV_VV_Solicitud_Renuncia 
              (ID_Solicitud, ID_Motivo_Renuncia, Finiquito, Fecha_Solicitud, ID_UsuarioRenuncia, Estado_Solicitud) 
              VALUES (SEQ_JS_FS_FV_VV_Solicitud_Renuncia.NEXTVAL, :motivo, :finiquito, TO_DATE(:fecha_solicitud, 'YYYY-MM-DD'), :rut, :estado)";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':motivo', $motivo);
    oci_bind_by_name($stmt, ':finiquito', $finiquito);
    oci_bind_by_name($stmt, ':fecha_solicitud', $fecha_solicitud);
    oci_bind_by_name($stmt, ':rut', $rut);
    oci_bind_by_name($stmt, ':estado', $estado_solicitud);

    // Ejecutar la consulta y manejar el mensaje
    if (oci_execute($stmt)) {
        $mensaje = "Solicitud de renuncia enviada con éxito.";
    } else {
        $e = oci_error($stmt);
        $mensaje = "Error al enviar la solicitud de renuncia: " . htmlspecialchars($e['message']);
    }
}

// Consulta para obtener los motivos de renuncia
$query_motivos = "SELECT ID_Motivo_Renuncia, Motivo FROM JS_FS_FV_VV_Motivo_Renuncia";
$stmt_motivos = oci_parse($conn, $query_motivos);
oci_execute($stmt_motivos);
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
        .container select,
        .container input {
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
    </style>
</head>
<body>
    <div class="container">
        <h1>Solicitar Renuncia</h1>
        <?php if (isset($mensaje)): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <label for="id_motivo_renuncia">Motivo de Renuncia:</label>
            <select name="id_motivo_renuncia" id="id_motivo_renuncia" required>
                <option value="" disabled selected>Seleccione un motivo</option>
                <?php while ($motivo = oci_fetch_assoc($stmt_motivos)): ?>
                    <option value="<?php echo $motivo['ID_MOTIVO_RENUNCIA']; ?>">
                        <?php echo htmlspecialchars($motivo['MOTIVO']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <label for="finiquito">Monto de Finiquito:</label>
            <input type="number" name="finiquito" id="finiquito" placeholder="Ingrese el monto" required>
            <button type="submit">Enviar Solicitud</button>
        </form>
    </div>
</body>
</html>
