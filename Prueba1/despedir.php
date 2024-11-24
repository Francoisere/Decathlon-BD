<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión y tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    header('Location: login.php');
    exit;
}

// Manejar el despido del empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = $_POST['rut'];

    // Consulta para eliminar al usuario
    $query = "DELETE FROM JS_FS_FV_VV_Usuario WHERE Rut_Usuario = :rut";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':rut', $rut);

    if (oci_execute($stmt)) {
        $mensaje = "Empleado despedido correctamente.";
    } else {
        $mensaje = "Error al despedir al empleado.";
    }
}

// Obtener lista de empleados
$query = "SELECT Rut_Usuario, Nombre1, Apellido1 FROM JS_FS_FV_VV_Usuario";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despedir Personal</title>
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
        .container select {
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
        <h1>Despedir Personal</h1>
        <?php if (isset($mensaje)): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </p>
        <?php endif; ?>
        <form method="POST">
            <label for="rut">Selecciona el RUT:</label>
            <select name="rut" id="rut" required>
                <option value="" disabled selected>Seleccione un empleado</option>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <option value="<?php echo $row['RUT_USUARIO']; ?>">
                        <?php echo $row['RUT_USUARIO'] . ' - ' . $row['NOMBRE1'] . ' ' . $row['APELLIDO1']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Despedir</button>
        </form>
    </div>
</body>
</html>
