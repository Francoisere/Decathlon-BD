<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    header('Location: login.php');
    exit;
}

// Obtener la lista de usuarios registrados
$query = "SELECT Rut_Usuario, Nombre1, Apellido1, Correo FROM JS_FS_FV_VV_Usuario";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Registrados</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        h1 {
            color: #007bff;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Usuarios Registrados</h1>
        <table>
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['RUT_USUARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE1']); ?></td>
                        <td><?php echo htmlspecialchars($row['APELLIDO1']); ?></td>
                        <td><?php echo htmlspecialchars($row['CORREO']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
