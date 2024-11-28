<?php
session_start();
include 'conexion.php'; // Conexión a la base de datos

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtener los datos del usuario desde la sesión
$usuario = $_SESSION['usuario'];
$rutUsuario = $usuario['RUT_USUARIO']; // RUT del usuario autenticado
$poderUsuario = $usuario['PODER']; // Nivel de poder del usuario

// Establecer redirección según el rol
if ($poderUsuario == 1 || $poderUsuario == 3) {
    $redirectUrl = "ver_registrados.php"; // Administradores o Líderes
} else {
    $redirectUrl = "accediste.php"; // Vendedores
}

// Comprobar si el RUT también llega por URL y tiene un valor válido
if (isset($_GET['rut']) && !empty($_GET['rut'])) {
    $rutUsuario = htmlspecialchars($_GET['rut']);
}

try {
    // Consulta con los JOINs necesarios para obtener nombres asociados
    $queryVerificar = "SELECT 
                            c.ID_CONTRATO, 
                            c.DURACION_MESES, 
                            c.RENOVACION_AUTOMATICA, 
                            car.NOMBRE_CARGO, 
                            hd.HORAS AS HORAS_ASIGNADAS, 
                            TO_CHAR(c.FECHA_CONTRATACION, 'DD/MM/YYYY') AS FECHA_CONTRATACION, 
                            d.DEPORTE AS NOMBRE_DEPORTE, 
                            tc.NOMBRE_TIPO_CONTRATO AS TIPO_CONTRATO, 
                            c.SALARIO_ASIGNADO 
                       FROM JS_FS_FV_VV_CONTRATOS c
                       LEFT JOIN JS_FS_FV_VV_CARGO car ON c.ID_CARGO = car.ID_CARGO
                       LEFT JOIN JS_FS_FV_VV_HORAS_DISPONIBLES hd ON c.HORAS_ASIGNADAS = hd.ID_HORAS
                       LEFT JOIN JS_FS_FV_VV_DEPORTES d ON c.DEPORTE_ASIGNADO = d.ID_DEPORTE
                       LEFT JOIN JS_FS_FV_VV_TIPO_CONTRATOS tc ON c.CONTRATO = tc.ID_TIPO_CONTRATO
                       WHERE c.RUT_CANDIDATO = :rutUsuario";
    
    $stmtVerificar = oci_parse($conn, $queryVerificar);
    oci_bind_by_name($stmtVerificar, ':rutUsuario', $rutUsuario);
    oci_execute($stmtVerificar);

    if ($contrato = oci_fetch_assoc($stmtVerificar)) {
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Detalle del Contrato</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    margin: auto;
                    background: white;
                    border-radius: 8px;
                    padding: 20px;
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
                }
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    border-bottom: 2px solid #007bff;
                    padding-bottom: 10px;
                }
                .header h1 {
                    color: #007bff;
                    margin: 0;
                }
                .header p {
                    margin: 5px 0;
                    color: #555;
                }
                .section {
                    margin-top: 20px;
                }
                .section h2 {
                    color: #007bff;
                    border-bottom: 1px solid #007bff;
                    padding-bottom: 5px;
                    margin-bottom: 10px;
                }
                .section p {
                    margin: 5px 0;
                }
                .highlight {
                    font-weight: bold;
                    color: #007bff;
                }
                .back-button {
                    position: absolute;
                    top: 20px;
                    left: 20px;
                    background-color: #007bff;
                    color: white;
                    padding: 10px 20px;
                    text-decoration: none;
                    font-size: 16px;
                    border-radius: 5px;
                }
                .back-button:hover {
                    background-color: #0056b3;
                }
            </style>
        </head>
        <body>
            <a href="<?php echo $redirectUrl; ?>" class="back-button">← Volver al menú</a>
            <div class="container">
                <div class="header">
                    <h1>Detalle del Contrato</h1>
                    <p>Información detallada sobre el contrato de trabajo</p>
                </div>
                <div class="section">
                    <h2>Detalles del Contrato</h2>
                    <p><strong>ID Contrato:</strong> <span class="highlight"><?php echo htmlspecialchars($contrato['ID_CONTRATO']); ?></span></p>
                    <p><strong>Duración (meses):</strong> <?php echo htmlspecialchars($contrato['DURACION_MESES']); ?> meses</p>
                    <p><strong>Renovación Automática:</strong> <?php echo $contrato['RENOVACION_AUTOMATICA'] == 1 ? 'Sí' : 'No'; ?></p>
                    <p><strong>Fecha de Contratación:</strong> <?php echo htmlspecialchars($contrato['FECHA_CONTRATACION']); ?></p>
                    <p><strong>Tipo de Contrato:</strong> <?php echo htmlspecialchars($contrato['TIPO_CONTRATO']); ?></p>
                </div>
                <div class="section">
                    <h2>Detalles de Trabajo</h2>
                    <p><strong>Cargo:</strong> <?php echo htmlspecialchars($contrato['NOMBRE_CARGO']); ?></p>
                    <p><strong>Horas Asignadas:</strong> <?php echo htmlspecialchars($contrato['HORAS_ASIGNADAS']); ?> horas/semana</p>
                    <p><strong>Deporte Asignado:</strong> <?php echo htmlspecialchars($contrato['NOMBRE_DEPORTE'] ?? 'N/A'); ?></p>
                </div>
                <div class="section">
                    <h2>Compensación</h2>
                    <p><strong>Salario Asignado:</strong> <span class="highlight">$<?php echo number_format($contrato['SALARIO_ASIGNADO'], 2); ?></span></p>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "<h1>Error</h1>";
        echo "<p>No se encontró un contrato asociado a tu cuenta.</p>";
        echo "<a href=\"$redirectUrl\" class=\"back-button\">← Volver</a>";
    }
} catch (Exception $e) {
    echo "<h1>Error al obtener el contrato</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<a href=\"$redirectUrl\" class=\"back-button\">← Volver</a>";
}
?>
