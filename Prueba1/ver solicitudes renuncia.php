<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

$usuario = $_SESSION['usuario'];

// Verificar que el usuario sea administrador (Poder = 1)
if ($usuario['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Procesar la acción de aceptar renuncia y eliminar al usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aceptar'])) {
    $rutUsuario = $_POST['rut_usuario'];

    try {
        // Eliminar las referencias en JS_FS_FV_VV_Solicitud_Renuncia
        $deleteSolicitudQuery = "DELETE FROM JS_FS_FV_VV_Solicitud_Renuncia WHERE ID_USUARIORENUNCIA = :RUT_USUARIO";
        $stmtDeleteSolicitud = oci_parse($conn, $deleteSolicitudQuery);
        oci_bind_by_name($stmtDeleteSolicitud, ':RUT_USUARIO', $rutUsuario);

        if (!oci_execute($stmtDeleteSolicitud)) {
            throw new Exception("Error al eliminar las referencias de solicitud de renuncia.");
        }

        // Eliminar al usuario de la tabla JS_FS_FV_VV_Usuario
        $deleteUsuarioQuery = "DELETE FROM JS_FS_FV_VV_Usuario WHERE RUT_USUARIO = :RUT_USUARIO";
        $stmtDeleteUsuario = oci_parse($conn, $deleteUsuarioQuery);
        oci_bind_by_name($stmtDeleteUsuario, ':RUT_USUARIO', $rutUsuario);

        if (!oci_execute($stmtDeleteUsuario)) {
            throw new Exception("Error al eliminar el usuario.");
        }

        // Mensaje de éxito
        $successMessage = "Usuario despedido y eliminado correctamente.";
    } catch (Exception $e) {
        // En caso de error
        die("Error al procesar la solicitud: " . $e->getMessage());
    }
}

// Consulta para obtener las solicitudes de renuncia junto con los estados
$query = "SELECT sr.ID_SOLICITUD, 
                 sr.FECHA_SOLICITUD, 
                 sr.COMENTARIO, 
                 mr.Motivo AS Motivo_Renuncia,
                 (u.NOMBRE1 || ' ' || u.APELLIDO1) AS Nombre_Usuario,
                 u.RUT_USUARIO,
                 e.Estados AS Estado_Solicitud
          FROM JS_FS_FV_VV_Solicitud_Renuncia sr
          JOIN JS_FS_FV_VV_Motivo_Renuncia mr ON sr.ID_MOTIVO_RENUNCIA = mr.ID_MOTIVO_RENUNCIA
          JOIN JS_FS_FV_VV_Usuario u ON sr.ID_USUARIORENUNCIA = u.RUT_USUARIO
          JOIN JS_FS_FV_VV_Estados e ON sr.ESTADO_SOLICITUD = e.ID_Estado
          ORDER BY sr.FECHA_SOLICITUD DESC";

$stmt = oci_parse($conn, $query);
if (!oci_execute($stmt)) {
    $e = oci_error($stmt);
    die("Error al ejecutar la consulta: " . htmlspecialchars($e['message']));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Renuncia</title>
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

        .table-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 800px;
        }

        h1 {
            color: #0056b3;
            margin-bottom: 1rem;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table th, table td {
            border: 1px solid #cccccc;
            padding: 0.75rem;
            text-align: left;
        }

        table th {
            background-color: #0056b3;
            color: #ffffff;
        }

        table td {
            background-color: #f9f9f9;
        }

        button {
            background-color: #007bff;
            color: #ffffff;
            font-size: 0.9rem;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
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
    <a href="accediste.php" class="back-button">← Volver al menú</a>
    <div class="table-container">
        <h1>Solicitudes de Despido</h1>
        <table>
            <thead>
                <tr>
                    <th>ID Solicitud</th>
                    <th>Fecha Solicitud</th>
                    <th>Nombre</th>
                    <th>RUT</th>
                    <th>Motivo</th>
                    <th>Comentario</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_SOLICITUD']); ?></td>
                        <td><?php echo htmlspecialchars($row['RUT_USUARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE_USUARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['MOTIVO_RENUNCIA']); ?></td>
                        <td><?php echo htmlspecialchars($row['COMENTARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['ESTADO_SOLICITUD']); ?></td>
                        <td><?php echo htmlspecialchars($row['FECHA_SOLICITUD']); ?></td>
                        <td>
                            <?php if ($row['ESTADO_SOLICITUD'] === 'Pendiente'): ?>
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="rut_usuario" value="<?php echo htmlspecialchars($row['RUT_USUARIO']); ?>">
                                    <button type="submit" name="aceptar">Aceptar</button>
                                </form>
                            <?php else: ?>
                                <span>Procesada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
