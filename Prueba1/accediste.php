<?php
session_start();


// Obtener los datos del usuario desde la sesión
$usuario = $_SESSION['usuario'];
$nombreCompleto = $usuario['NOMBRE1'] . ' ' . $usuario['APELLIDO1'];
$poderUsuario = $usuario['PODER']; // Nivel de poder: 1 (Administrador) o 2 (Usuario común)
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario</title>
    <style>
        /* Estilo general */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 800px;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header h1 {
            color: #007bff;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #555555;
            font-size: 1rem;
        }

        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .option {
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            width: 200px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .option:hover {
            background-color: #007bff;
            color: #ffffff;
            border-color: #007bff;
        }

        .logout-button {
            background-color: #007bff;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 2rem;
            display: block;
            width: 100%;
            text-align: center;
        }

        .logout-button:hover {
            background-color: #0056b3;
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            color: #555555;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bienvenido, <?php echo htmlspecialchars($nombreCompleto); ?>!</h1>
            <p> Tu cargo es: <strong><?php echo ($poderUsuario == 1) ? 'Administrador' : 'Usuario'; ?></strong></p>
        </div>

        <div class="options">
            <?php if ($poderUsuario == 1): ?>
                <!-- Opciones para Administrador (Nivel 1) -->
                <div class="option" onclick="location.href='ver_registrados.php'">
                    <h2>Ver Registrados</h2>
                    <p>Accede a los datos de todos los usuarios registrados.</p>
                </div>
                <div class="option" onclick="location.href='contratar.php'">
                    <h2>Contratar Personal</h2>
                    <p>Agrega nuevos empleados a la base de datos.</p>
                </div>
                <div class="option" onclick="location.href='despedir.php'">
                    <h2>Despedir Personal</h2>
                    <p>Elimina usuarios del sistema.</p>
                </div>
            <?php endif; ?>

            <!-- Opciones comunes para todos los usuarios -->
            <div class="option" onclick="location.href='cotizaciones.php'">
                <h2>Mis Cotizaciones</h2>
                <p>Consulta tus cotizaciones realizadas.</p>
            </div>
            <div class="option" onclick="location.href='solicitar_renuncia.php'">
                <h2>Solicitar Renuncia</h2>
                <p>Envía una solicitud para renunciar a tu puesto.</p>
            </div>
        </div>

        <form action="logout.php" method="POST">
            <button type="submit" class="logout-button">Cerrar Sesión</button>
        </form>

        <div class="footer">
            <p>Decathlon &copy; <?php echo date('Y'); ?>. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
