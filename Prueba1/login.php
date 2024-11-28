<?php
include 'conexion.php';
session_start();

/**
 * Función para manejar el inicio de sesión del usuario.
 *
 * @param resource $conn Conexión a la base de datos.
 * @param string $correo Correo del usuario.
 * @param string $clave Contraseña del usuario.
 * @return array|bool Retorna los datos del usuario si se encuentra, o false si hay un error.
 */
function loginUsuario($conn, $correo, $clave)
{


    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $clave = $_POST['contraseña'];

    // Consulta para validar al usuario
    $query = "SELECT Rut_Usuario, Nombre1, Apellido1, Poder 
              FROM JS_FS_FV_VV_Usuario 
              WHERE Correo = :correo AND Contraseña = :clave";
    $stmt = oci_parse($conn, $query);
    oci_bind_by_name($stmt, ':correo', $correo);
    oci_bind_by_name($stmt, ':clave', $clave);
    oci_execute($stmt);

    $usuario = oci_fetch_assoc($stmt);

    if ($usuario) {
        // Guardar los datos del usuario en la sesión
        $_SESSION['usuario'] = [
            'Rut_Usuario' => $usuario['RUT_USUARIO'],
            'Nombre1' => $usuario['NOMBRE1'],
            'Apellido1' => $usuario['APELLIDO1'],
            'PODER' => $usuario['PODER'],
        ];
        header('Location: contratar.php');
        exit;
    } else {
        echo "Correo o contraseña incorrectos.";
    }
}

    if (!$conn) {
        error_log("Conexión no válida.");
        return false;
    }

    // Consulta para validar el usuario
    $query = "SELECT u.Rut_Usuario, u.Nombre1, u.Apellido1, c.Poder, u.Clave
              FROM JS_FS_FV_VV_Usuario u
              JOIN JS_FS_FV_VV_Cargo c ON u.ID_Cargo = c.ID_Cargo
              WHERE u.Correo = :correo";

    // Preparar la consulta
    $stmt = oci_parse($conn, $query);
    if (!$stmt) {
        $e = oci_error($conn);
        error_log("Error al preparar la consulta: " . $e['message']); // Registrar error en el log
        return false;
    }

    // Vincular parámetros
    oci_bind_by_name($stmt, ':correo', $correo);

    // Ejecutar la consulta
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        error_log("Error al ejecutar la consulta: " . $e['message']); // Registrar error en el log
        return false;
    }

    // Obtener los resultados
    $usuario = oci_fetch_assoc($stmt);
    oci_free_statement($stmt);

    // Verificar si la contraseña ingresada coincide con la almacenada (texto plano)
    if ($usuario && $usuario['CLAVE'] === $clave) {
        unset($usuario['CLAVE']); // No guardar la contraseña en sesión
        return $usuario;
    }

    return false;
}


// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL); // Sanitizar correo
    $clave = $_POST['contraseña']; // Contraseña

    // Llamar a la función para intentar el login
    $usuario = loginUsuario($conn, $correo, $clave);

    if ($usuario) {
        // Si el usuario existe, iniciar sesión y redirigir
        $_SESSION['usuario'] = $usuario;
        header('Location: accediste.php'); // Redirigir al panel de contratación
        exit;
    } else {
        // Si no se encontró el usuario, mostrar un mensaje de error
        $error = "Correo o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e3f2fd; /* Azul claro */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }
        .login-container img.logo {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 60px; /* Ajusta el tamaño de la imagen */
            height: auto;
        }
        .login-container h1 {
            color: #0056b3;
            margin-bottom: 1rem;
        }
        .login-container form {
            display: flex;
            flex-direction: column;
        }
        .login-container label {
            color: #0056b3;
            font-weight: bold;
            margin-bottom: 0.5rem;
            text-align: left;
        }
        .login-container input {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #cccccc;
            border-radius: 4px;
            font-size: 1rem;
        }
        .login-container input:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }
        .login-container button {
            background-color: #007bff;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .login-container button:hover {
            background-color: #0056b3;
        }
        .error {
            color: red;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }
        footer {
            margin-top: 2rem;
            text-align: center;
        }
        footer button {
            background-color: #28a745;
            color: #ffffff;
            font-size: 1rem;
            font-weight: bold;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        footer button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Imagen del logo -->
        <img
            src="https://1000marcas.net/wp-content/uploads/2020/01/Decathlon-Logo-1990-500x281.png"
            alt="Logo Decathlon"
            width="333,3"
            height="187,3"
            class="logo"
        />
        <h1>Inicio de Sesión</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="POST">
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" placeholder="Ingrese su correo" required>
            <label for="contraseña">Contraseña:</label>
            <input type="password" id="contraseña" name="contraseña" placeholder="Ingrese su contraseña" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
    <footer>
        <form action="trabaja_con_nosotros.php" method="get">
            <button type="submit">Trabaja con Nosotros</button>
        </form>
    </footer>
</body>
</html>
