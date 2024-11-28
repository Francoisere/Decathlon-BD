<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Verificar que se proporcionó el ID del despedido
if (!isset($_GET['id_despedido']) || empty($_GET['id_despedido'])) {
    die("Error: No se proporcionó un ID de despedido válido.");
}

$id_despedido = $_GET['id_despedido'];

// Obtener los datos del finiquito
$query_finiquito = "
    SELECT 
        ID_SOLICITUD,
        FINIQUITO,
        FECHA_SOLICITUD,
        ID_USUARIOSOLICITUD,
        DIAS_TRABAJADOS,
        DIAS_VACACIONES
    FROM JS_FS_FV_VV_SOLICITUD_DESPIDO
    WHERE ID_DESPEDIDO = :id_despedido AND FINIQUITO IS NOT NULL";

$stmt_finiquito = oci_parse($conn, $query_finiquito);
oci_bind_by_name($stmt_finiquito, ':id_despedido', $id_despedido);

if (!oci_execute($stmt_finiquito)) {
    $error = oci_error($stmt_finiquito);
    die("Error al obtener el finiquito: " . $error['message']);
}

$finiquito = oci_fetch_assoc($stmt_finiquito);

if (!$finiquito) {
    die("Error: No se encontró un finiquito válido para el ID proporcionado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finiquito del Empleado</title>
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

        .finiquito-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 600px;
            text-align: left;
        }

        h1 {
            color: #0056b3;
            margin-bottom: 1rem;
            text-align: center;
        }

        .finiquito-details {
            line-height: 1.8;
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
    <a href="ver_finiquito.php" class="back-button">← Volver a Finiquitos</a>
    <div class="finiquito-container">
        <h1>Finiquito del Empleado</h1>
        <div class="finiquito-details">
            <p><strong>ID Solicitud:</strong> <?php echo htmlspecialchars($finiquito['ID_SOLICITUD']); ?></p>
            <p><strong>ID Usuario Solicitante:</strong> <?php echo htmlspecialchars($finiquito['ID_USUARIOSOLICITUD']); ?></p>
            <p><strong>Días Trabajados:</strong> <?php echo htmlspecialchars($finiquito['DIAS_TRABAJADOS']); ?></p>
            <p><strong>Días de Vacaciones:</strong> <?php echo htmlspecialchars($finiquito['DIAS_VACACIONES']); ?></p>
            <p><strong>Monto del Finiquito:</strong> $<?php echo number_format($finiquito['FINIQUITO'], 2); ?></p>
            <p><strong>Fecha de Solicitud:</strong> <?php echo htmlspecialchars($finiquito['FECHA_SOLICITUD']); ?></p>
        </div>
    </div>
</body>
</html>
