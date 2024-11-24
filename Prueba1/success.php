<?php
session_start();

// Verificar si la sesión está activa
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

$usuario = $_SESSION['usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Concedido</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .success-container {
            text-align: center;
        }
        .success-container h1 {
            color: #00796b;
        }
        .success-container p {
            color: #004d40;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <h1>¡Inicio de Sesión Exitoso!</h1>
        <p>Bienvenido, <?php echo htmlspecialchars($usuario['Nombre1']); ?>.</p>
    </div>
</body>
</html>
