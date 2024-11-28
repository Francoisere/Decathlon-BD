<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Manejar la selección del empleado para el despido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = $_POST['rut'];

    // Redirigir a la página de finiquito pasando el RUT
    header("Location: finiquito.php?rut_usuario=" . urlencode($rut));
    exit;
}

// Obtener lista de empleados que no sean administradores
$query = "SELECT RUT_USUARIO, NOMBRE1, APELLIDO1 
          FROM JS_FS_FV_VV_USUARIO 
          WHERE ID_CARGO != 1"; // Excluir administradores (ID_CARGO = 1)
$stmt = oci_parse($conn, $query);

if (!oci_execute($stmt)) {
    $error = oci_error($stmt);
    die("Error al obtener la lista de empleados: " . htmlspecialchars($error['message']));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Despedir Personal</title>
    <style>
        /* Mantener la estética original */
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
            position: relative;
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
        .back-button {
            position: fixed;
            top: 10px;
            left: 10px;
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
    <div class="container">
        <a href="accediste.php" class="back-button">← Volver al menú</a>
        <h1>Despedir Personal</h1>
        <form method="POST">
            <label for="rut">Selecciona el RUT:</label>
            <select name="rut" id="rut" required>
                <option value="" disabled selected>Seleccione un empleado</option>
                <?php while ($row = oci_fetch_assoc($stmt)): ?>
                    <option value="<?php echo htmlspecialchars($row['RUT_USUARIO']); ?>">
                        <?php echo htmlspecialchars($row['RUT_USUARIO'] . ' - ' . $row['NOMBRE1'] . ' ' . $row['APELLIDO1']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Crear Finiquito</button>
        </form>
    </div>
</body>
</html>
