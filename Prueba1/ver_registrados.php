<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos para acceder
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$poderUsuario = $usuario['PODER']; // Nivel de poder del usuario
$rutUsuario = $usuario['RUT_USUARIO']; // RUT del usuario autenticado

// Permitir solo a administradores y líderes (nivel 1 y 3)
if ($poderUsuario != 1 && $poderUsuario != 3) {
    header('Location: panel_usuario.php');
    exit;
}

// Obtener registros de usuarios según el nivel de poder
if ($poderUsuario == 1) {
    // Administrador: puede ver todos los usuarios excepto otros administradores
    $query = "SELECT Rut_Usuario, Nombre1, Nombre2, Apellido1, Apellido2, Telefono, Correo, ID_Cargo, Salario 
              FROM JS_FS_FV_VV_Usuario 
              WHERE ID_Cargo != 1 OR Rut_Usuario = :rutUsuario 
              ORDER BY ID_Cargo";
} elseif ($poderUsuario == 3) {
    // Líder: solo puede ver su propio registro y los registros de vendedores (ID_CARGO = 3)
    $query = "SELECT Rut_Usuario, Nombre1, Nombre2, Apellido1, Apellido2, Telefono, Correo, ID_Cargo, Salario 
              FROM JS_FS_FV_VV_Usuario 
              WHERE (Rut_Usuario = :rutUsuario) OR (ID_Cargo = 3) 
              ORDER BY ID_Cargo";
}

$stmt = oci_parse($conn, $query);

// Vincular el RUT del usuario para administradores y líderes
oci_bind_by_name($stmt, ':rutUsuario', $rutUsuario);

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
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #007bff;
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #007bff;
            color: white;
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
            color: white;
        }

        .view-button {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            text-decoration: none;
        }

        .view-button:hover {
            background-color: #218838;
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


        .disabled-button {
            background-color: #ccc;
            color: #777;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 5px;
            text-align: center;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="accediste.php" class="back-button">← Volver al menú</a>
        <h1>Usuarios Registrados</h1>
        <table>
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Cargo</th>
                    <th>Cotización</th>
                    <th>Contrato</th> <!-- Nueva columna para el botón de contrato -->
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['RUT_USUARIO']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE1'] . ' ' . $row['NOMBRE2'] . ' ' . $row['APELLIDO1'] . ' ' . $row['APELLIDO2']); ?></td>
                        <td><?php echo htmlspecialchars($row['TELEFONO']); ?></td>
                        <td><?php echo htmlspecialchars($row['CORREO']); ?></td>
                        <td>
                            <?php
                            // Mostrar el nombre del cargo
                            switch ($row['ID_CARGO']) {
                                case 1:
                                    echo 'Administrador';
                                    break;
                                case 2:
                                    echo 'Líder';
                                    break;
                                case 3:
                                    echo 'Vendedor';
                                    break;
                                default:
                                    echo 'Desconocido';
                            }
                            ?>
                        </td>
                        <td>
                            <!-- Botón de Cotización -->
                            <?php if ($poderUsuario == 1 && $row['ID_CARGO'] != 1): ?>
                                <!-- Administradores pueden ver cotizaciones de líderes y vendedores -->
                                <a href="cotizaciones.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Cotización</a>
                            <?php elseif ($poderUsuario == 3 && $row['ID_CARGO'] == 3): ?>
                                <!-- Líderes pueden ver cotizaciones de vendedores -->
                                <a href="cotizaciones.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Cotización</a>
                            <?php elseif ($row['RUT_USUARIO'] == $rutUsuario): ?>
                                <!-- Cualquier usuario puede ver su propia cotización -->
                                <a href="cotizaciones.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Cotización</a>
                            <?php else: ?>
                                <span class="disabled-button">No Disponible</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Botón de Contrato -->
                            <?php if ($poderUsuario == 1 && $row['ID_CARGO'] != 1): ?>
                                <!-- Administradores pueden ver contratos de líderes y vendedores -->
                                <a href="ver_contrato.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Contrato</a>
                            <?php elseif ($poderUsuario == 3 && $row['ID_CARGO'] == 3): ?>
                                <!-- Líderes pueden ver contratos de vendedores -->
                                <a href="ver_contrato.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Contrato</a>
                            <?php elseif ($row['RUT_USUARIO'] == $rutUsuario): ?>
                                <!-- Cualquier usuario puede ver su propio contrato -->
                                <a href="ver_contrato.php?rut=<?php echo urlencode($row['RUT_USUARIO']); ?>" class="view-button">Ver Contrato</a>
                            <?php else: ?>
                                <span class="disabled-button">No Disponible</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
