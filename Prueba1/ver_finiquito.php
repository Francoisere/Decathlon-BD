<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Obtener los finiquitos de la tabla JS_FS_FV_VV_SOLICITUD_DESPIDO
$query_finiquitos = "
    SELECT 
        ID_SOLICITUD,
        ID_DESPEDIDO
    FROM JS_FS_FV_VV_SOLICITUD_DESPIDO
    WHERE FINIQUITO IS NOT NULL";

$stmt_finiquitos = oci_parse($conn, $query_finiquitos);

if (!oci_execute($stmt_finiquitos)) {
    $error = oci_error($stmt_finiquitos);
    die("Error al obtener los finiquitos: " . $error['message']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finiquitos de Empleados</title>
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
            max-width: 600px;
            overflow-x: auto;
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
            text-align: center;
        }

        table th {
            background-color: #0056b3;
            color: #ffffff;
        }

        .btn {
            background-color: #007bff;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn:hover {
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
            color: #ffffff;
        }
    </style>
</head>
<body>
    <a href="accediste.php" class="back-button">← Volver al menú</a>
    <div class="table-container">
        <h1>Finiquitos de Empleados</h1>
        <table>
            <thead>
                <tr>
                    <th>ID Despedido</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt_finiquitos)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['ID_DESPEDIDO']); ?></td>
                        <td>
                            <a class="btn" href="plantilla_finiquito.php?id_despedido=<?php echo htmlspecialchars($row['ID_DESPEDIDO']); ?>">Ver Finiquito</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>