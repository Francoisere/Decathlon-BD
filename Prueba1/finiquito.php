<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

$mensaje = null; // Inicializar mensaje vacío

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_despedido = $_POST['id_despedido'];
    $diasTrabajados = $_POST['dias_trabajados'] ?? null;
    $diasVacaciones = $_POST['dias_vacaciones'] ?? null;
    $indemnizacionAnios = $_POST['indemnizacion_anios'] ?? 'N';
    $avisoPrevio = $_POST['aviso_previo'] ?? 'N';

    // Validaciones de entrada
    if (empty($id_despedido)) {
        $mensaje = "Error: Debes ingresar un RUT válido.";
    } elseif (!is_numeric($diasTrabajados) || $diasTrabajados < 0) {
        $mensaje = "Error: Los días trabajados deben ser un número positivo.";
    } elseif (!is_numeric($diasVacaciones) || $diasVacaciones < 0) {
        $mensaje = "Error: Los días de vacaciones deben ser un número positivo.";
    } elseif (!in_array($indemnizacionAnios, ['S', 'N']) || !in_array($avisoPrevio, ['S', 'N'])) {
        $mensaje = "Error: Indemnización y aviso previo deben ser 'S' o 'N'.";
    } else {
        try {
            // Paso 1: Actualizar los días trabajados y días de vacaciones en la tabla solicitud de despido
            $query_update_dias = "UPDATE JS_FS_FV_VV_SOLICITUD_DESPIDO 
                                  SET DIAS_TRABAJADOS = :dias_trabajados, 
                                      DIAS_VACACIONES = :dias_vacaciones 
                                  WHERE ID_DESPEDIDO = :id_despedido";

            $stmt_update = oci_parse($conn, $query_update_dias);
            oci_bind_by_name($stmt_update, ':dias_trabajados', $diasTrabajados);
            oci_bind_by_name($stmt_update, ':dias_vacaciones', $diasVacaciones);
            oci_bind_by_name($stmt_update, ':id_despedido', $id_despedido);

            if (!oci_execute($stmt_update)) {
                $error = oci_error($stmt_update);
                throw new Exception("Error al actualizar los días trabajados y vacaciones: " . $error['message']);
            }

            // Paso 2: Llamar al procedimiento almacenado para calcular el finiquito
            $query_finiquito = "BEGIN 
                JS_FS_FV_VV_CALCULA_FINIQUITO(
                    :id_despedido, 
                    :indemnizacion_anios, 
                    :aviso_previo
                ); 
            END;";

            $stmt_finiquito = oci_parse($conn, $query_finiquito);
            oci_bind_by_name($stmt_finiquito, ':id_despedido', $id_despedido);
            oci_bind_by_name($stmt_finiquito, ':indemnizacion_anios', $indemnizacionAnios);
            oci_bind_by_name($stmt_finiquito, ':aviso_previo', $avisoPrevio);

            if (!oci_execute($stmt_finiquito)) {
                $error = oci_error($stmt_finiquito);
                throw new Exception("Error al calcular el finiquito: " . $error['message']);
            }

            // Paso 3: Llamar al procedimiento para eliminar al usuario
            $query_eliminar_usuario = "BEGIN 
                JS_FS_FV_VV_ELIMINAR_USUARIO_RELACIONES(:id_despedido); 
            END;";

            $stmt_eliminar_usuario = oci_parse($conn, $query_eliminar_usuario);
            oci_bind_by_name($stmt_eliminar_usuario, ':id_despedido', $id_despedido);

            if (!oci_execute($stmt_eliminar_usuario)) {
                $error = oci_error($stmt_eliminar_usuario);
                throw new Exception("Error al eliminar al usuario: " . $error['message']);
            }

            $mensaje = "Finiquito calculado y usuario eliminado correctamente.";
        } catch (Exception $e) {
            $mensaje = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calcular Finiquito</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
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
        .mensaje {
            margin-bottom: 1rem;
            padding: 10px;
            border: 1px solid;
            border-radius: 5px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        form label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }
        form input, form select {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        form button {
            background-color: #007bff;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .back-button {
            position: fixed;
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
            color: #ffffff;
        }
    </style>
</head>
<body>
    <a href="accediste.php" class="back-button">← Volver</a>
    <?php if ($mensaje): ?>
        <div class="mensaje <?php echo strpos($mensaje, 'Error') === false ? 'exito' : 'error'; ?>">
            <?php echo htmlspecialchars($mensaje); ?>
        </div>
    <?php endif; ?>
        <div class="container">
        <h1>Calcular Finiquito</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo strpos($mensaje, 'Error') === false ? 'exito' : 'error'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        <form method="POST">
            <label for="id_despedido">Rut del Despedido:</label>
            <input type="text" id="id_despedido" name="id_despedido" placeholder="Ingrese el rut del despedido" required>

            <label for="dias_trabajados">Días Trabajados:</label>
            <input type="number" id="dias_trabajados" name="dias_trabajados" placeholder="Ingrese días trabajados" required>

            <label for="dias_vacaciones">Días de Vacaciones:</label>
            <input type="number" id="dias_vacaciones" name="dias_vacaciones" placeholder="Ingrese días de vacaciones" required>

            <label for="indemnizacion_anios">¿Aplica Indemnización por Años de Servicio?</label>
            <select id="indemnizacion_anios" name="indemnizacion_anios">
                <option value="S">Sí</option>
                <option value="N" selected>No</option>
            </select>

            <label for="aviso_previo">¿Aplica Aviso Previo?</label>
            <select id="aviso_previo" name="aviso_previo">
                <option value="S">Sí</option>
                <option value="N" selected>No</option>
            </select>

            <button type="submit">Calcular Finiquito</button>
        </form>
    </div>
</body>
</html>
