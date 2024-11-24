<?php
include 'conexion.php';
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Consultar usuarios
$query = "SELECT Rut_Usuario, Nombre1, Apellido1, ID_Cargo FROM JS_FS_FV_VV_Usuario";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios Registrados</title>
</head>
<body>
    <h1>Usuarios Registrados</h1>
    <p>Bienvenido, <?php echo $_SESSION['usuario']['NOMBRE1']; ?>.</p>
    <table border="1">
        <thead>
            <tr>
                <th>Rut Usuario</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>ID Cargo</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = oci_fetch_assoc($stmt)): ?>
                <tr>
                    <td><?php echo $row['RUT_USUARIO']; ?></td>
                    <td><?php echo $row['NOMBRE1']; ?></td>
                    <td><?php echo $row['APELLIDO1']; ?></td>
                    <td><?php echo $row['ID_CARGO']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><a href="logout.php">Cerrar Sesión</a></p>
</body>
</html>
<?php
oci_free_statement($stmt);
?>
