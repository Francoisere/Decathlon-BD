<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Manejar acciones de aceptar/rechazar solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitud = $_POST['id_solicitud'];
    $accion = $_POST['accion'];

    if ($accion === 'aceptar') {
        // Obtener ID del usuario despedido
        $query_get_usuario = "SELECT ID_DESPEDIDO FROM JS_FS_FV_VV_Solicitud_Despido WHERE ID_SOLICITUD = :id_solicitud";
        $stmt_get_usuario = oci_parse($conn, $query_get_usuario);
        oci_bind_by_name($stmt_get_usuario, ':id_solicitud', $id_solicitud);

        if (!oci_execute($stmt_get_usuario)) {
            $error = oci_error($stmt_get_usuario);
            die("Error al ejecutar la consulta: " . $error['message']);
        }

        $usuario = oci_fetch_assoc($stmt_get_usuario)['ID_DESPEDIDO'];

        // Redirigir a la página de finiquito con el ID del usuario despedido
        header("Location: finiquito.php?id_despedido=" . urlencode($usuario));
        exit;
    } elseif ($accion === 'rechazar') {
        // Actualizar estado de solicitud a rechazada
        $query_update = "UPDATE JS_FS_FV_VV_Solicitud_Despido SET ESTADO_SOLICITUD = 3 WHERE ID_SOLICITUD = :id_solicitud";
        $stmt_update = oci_parse($conn, $query_update);
        oci_bind_by_name($stmt_update, ':id_solicitud', $id_solicitud);

        if (!oci_execute($stmt_update)) {
            $error = oci_error($stmt_update);
            die("Error al actualizar la solicitud: " . $error['message']);
        }

        // Mensaje de éxito
        echo "<script>alert('Solicitud rechazada exitosamente.'); window.location.href = 'accediste.php';</script>";
        exit;
    }
}

// Obtener solicitudes de despido pendientes
$query_solicitudes =
   "SELECT 
        sd.ID_SOLICITUD, 
        sd.FECHA_SOLICITUD, 
        u.NOMBRE1 || ' ' || u.APELLIDO1 AS NOMBRE, 
        u.RUT_USUARIO AS RUT_USUARIO, 
        md.MOTIVO AS MOTIVO, 
        sd.COMENTARIO
    FROM JS_FS_FV_VV_Solicitud_Despido sd
    JOIN JS_FS_FV_VV_Usuario u ON sd.ID_DESPEDIDO = u.RUT_USUARIO
    JOIN JS_FS_FV_VV_Motivos_Despidos md ON sd.ID_MOTIVO_SOLICITUD = md.ID_MOTIVO_DESPIDO
    WHERE sd.ESTADO_SOLICITUD = 2";

$stmt_solicitudes = oci_parse($conn, $query_solicitudes);

if (!oci_execute($stmt_solicitudes)) {
    $error = oci_error($stmt_solicitudes);
    die("Error al obtener las solicitudes de despido: " . $error['message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Despido</title>
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
            vertical-align: middle;
        }

        table th {
            background-color: #0056b3;
            color: #ffffff;
            text-align: center;
        }

        table td {
            background-color: #f9f9f9;
            text-align: center;
        }

        .btn {
            font-size: 0.9rem;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-accept {
            background-color: #28a745;
            color: #ffffff;
            margin-right: 10px;
        }

        .btn-accept:hover {
            background-color: #218838;
        }

        .btn-reject {
            background-color: #dc3545;
            color: #ffffff;
        }

        .btn-reject:hover {
            background-color: #c82333;
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
            color: #ffffff;
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
                <?php while ($row = oci_fetch_assoc($stmt_solicitudes)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_SOLICITUD']); ?></td>
                        <td><?php echo htmlspecialchars($row['FECHA_SOLICITUD']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE']); ?></td>
                        <td><?php echo htmlspecialchars($row['RUT_USUARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['MOTIVO']); ?></td>
                        <td><?php echo htmlspecialchars($row['COMENTARIO']); ?></td>
                        <td>
                            <form method="POST" style="display: flex; justify-content: center; gap: 10px;">
                                <input type="hidden" name="id_solicitud" value="<?php echo $row['ID_SOLICITUD']; ?>">
                                <button type="submit" name="accion" value="aceptar" class="btn btn-accept">Aceptar</button>
                                <button type="submit" name="accion" value="rechazar" class="btn btn-reject">Rechazar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
