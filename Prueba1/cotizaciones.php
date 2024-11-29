<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación de Sueldo</title>
    <style>
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            position: relative;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #007bff;
            margin: 0;
        }

        .header p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .section p {
            margin: 5px 0;
            font-size: 14px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 5px 0;
        }

        .row:last-child {
            border-bottom: none;
        }

        .row strong {
            font-weight: bold;
        }

        .totals {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }

        .signature {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #333;
        }

        .signature-line {
            margin-top: 20px;
            border-top: 1px solid #333;
            width: 50%;
            margin-left: auto;
            margin-right: auto;
        }

        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php
session_start();
include 'conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit;
}

// Obtener el RUT del usuario autenticado y el RUT pasado como parámetro
$usuario = $_SESSION['usuario'];
$rutAutenticado = $usuario['RUT_USUARIO']; // RUT del usuario autenticado
$poderUsuario = $usuario['PODER']; // Poder del usuario (1 = Administrador, 3 = Líder, 2 = Vendedor)

// Establecer la URL dinámica de redirección
if ($poderUsuario == 1 || $poderUsuario == 3) {
    $redirectUrl = "ver_registrados.php"; // Administrador o líder
} else {
    $redirectUrl = "accediste.php"; // Vendedor
}

// Verificar si el RUT se ha pasado como parámetro en la URL
if (isset($_GET['rut'])) {
    $rutParametro = htmlspecialchars($_GET['rut']); // Sanitizar el RUT recibido por GET
} else {
    // Si no se proporciona RUT en la URL, usar el del usuario autenticado
    $rutParametro = $rutAutenticado;
}

// Configurar la consulta para obtener las cotizaciones del usuario correspondiente
$query = "SELECT 
            u.RUT_USUARIO AS Rut_Usuario,
            u.nombre1 || ' ' || u.apellido1 AS Nombre,
            ca.nombre_cargo AS Cargo,
            u.salario AS Salario_Base,
            u.salario * 0.115 AS Gratificacion,
            (u.salario + (u.salario * 0.115)) * cc.porcentaje AS Fondo_Pensiones,
            (u.salario + (u.salario * 0.115)) * tp.porcentaje AS Isapre,
            JS_FS_FV_VV_CALCULAR_IMPUESTO_UNICO(u.salario) AS Impuesto_Unico,
            (u.salario * 0.006) AS Seguro_Cesantia,
            (u.salario * 0.016) AS Seguro_Cesantia_Empleador,
            (u.salario * 0.008) AS Seguro_Cesantia_Solidario, 
            20800 AS Movilizacion,
            42900 AS Colacion,
            u.salario + 20800 + 42900 + u.salario * 0.115 AS Total_Haberes,
            ((u.salario + (u.salario * 0.115)) * cc.porcentaje) + ((u.salario + (u.salario * 0.115)) * tp.porcentaje) + JS_FS_FV_VV_CALCULAR_IMPUESTO_UNICO(u.salario) + (u.salario * 0.006) AS Total_Descuentos,
            (u.salario + 20800 + 42900 + u.salario * 0.115) - (((u.salario + (u.salario * 0.115)) * cc.porcentaje) + ((u.salario + (u.salario * 0.115)) * tp.porcentaje)) - JS_FS_FV_VV_CALCULAR_IMPUESTO_UNICO(u.salario) - (u.salario * 0.006) AS Liquido
        FROM JS_FS_FV_VV_Usuario u
        JOIN JS_FS_FV_VV_Cargo ca ON u.ID_Cargo = ca.ID_Cargo
        JOIN JS_FS_FV_VV_CC cc ON u.ID_CC = cc.ID_CC
        JOIN JS_FS_FV_VV_Tipo_Previsiones tp ON u.ID_Prevision = tp.ID_TIPO_PREVISION
        WHERE u.RUT_USUARIO = :rut"; // Filtrar por el RUT proporcionado

// Preparar y ejecutar la consulta
$stmt = oci_parse($conn, $query);
oci_bind_by_name($stmt, ':rut', $rutParametro);
oci_execute($stmt);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liquidación de Sueldo</title>
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
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            position: relative;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #007bff;
            margin: 0;
        }

        .header p {
            margin: 0;
            color: #555;
            font-size: 14px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h2 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .section p {
            margin: 5px 0;
            font-size: 14px;
        }

        .row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #ddd;
            padding: 5px 0;
        }

        .row:last-child {
            border-bottom: none;
        }

        .row strong {
            font-weight: bold;
        }

        .totals {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #777;
        }

        .signature {
            margin-top: 40px;
            text-align: center;
            font-size: 14px;
            color: #333;
        }

        .signature-line {
            margin-top: 20px;
            border-top: 1px solid #333;
            width: 50%;
            margin-left: auto;
            margin-right: auto;
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
        }
    </style>
</head>
<body>

<!-- Botón de volver -->
<a href="<?php echo $redirectUrl; ?>" class="back-button">← Volver a los registros</a>

<?php while ($row = oci_fetch_assoc($stmt)): ?>
    <div class="container">
        <div class="header">
            <h1>Liquidación de Sueldo</h1>
            <p>Empleado: Decathlon Chile SpA (76.507.443-6)</p>
            <p>Mes: <?php echo date('F Y'); ?></p>
        </div>

        <div class="section">
            <h2>Detalles del Empleado</h2>
            <p><strong>Sr(a):</strong> <?php echo htmlspecialchars($row['NOMBRE']); ?></p>
            <p><strong>RUT:</strong> <?php echo htmlspecialchars($row['RUT_USUARIO']); ?></p>
            <p><strong>Cargo:</strong> <?php echo htmlspecialchars($row['CARGO']); ?></p>
            <p><strong>Sueldo Base:</strong> $<?php echo number_format((float)$row['SALARIO_BASE'], 2); ?></p>
        </div>

        <div class="section">
            <h2>Haberes Imponibles</h2>
            <div class="row">
                <p>Sueldo Base</p>
                <p>$<?php echo number_format((float)$row['SALARIO_BASE'], 2); ?></p>
            </div>
            <div class="row">
                <p>Gratificación</p>
                <p>$<?php echo number_format((float)$row['GRATIFICACION'], 2); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Haberes No Imponibles</h2>
            <div class="row">
                <p>Movilización</p>
                <p>$<?php echo number_format((float)$row['MOVILIZACION'], 2); ?></p>
            </div>
            <div class="row">
                <p>Colacion</p>
                <p>$<?php echo number_format((float)$row['COLACION'], 2); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Descuentos Legales</h2>
            <div class="row">
                <p>Fondo de Pensiones</p>
                <p>$<?php echo number_format((float)$row['FONDO_PENSIONES'], 2); ?></p>
            </div>
            <div class="row">
                <p>Isapre</p>
                <p>$<?php echo number_format((float)$row['ISAPRE'], 2); ?></p>
            </div>
            <div class="row">
                <p>Impuesto Único</p>
                <p>$<?php echo number_format((float)$row['IMPUESTO_UNICO'], 2); ?></p>
            </div>
            <div class="row">
                <p>Seguro Cesantía</p>
                <p>$<?php echo number_format((float)$row['SEGURO_CESANTIA'], 2); ?></p>
            </div>
        </div>

        <div class="totals">
            <p>Total Haberes:</p>
            <p>$<?php echo number_format((float)$row['TOTAL_HABERES'], 2); ?></p>
        </div>

        <div class="totals">
            <p>Total Descuentos:</p>
            <p>$<?php echo number_format((float)$row['TOTAL_DESCUENTOS'], 2); ?></p>
        </div>

        <div class="totals" style="border-top: 2px solid #007bff; padding-top: 10px;">
            <p>Líquido a Recibir:</p>
            <p style="color: #007bff;">$<?php echo number_format((float)$row['LIQUIDO'], 2); ?></p>
        </div>

        <div class="footer">
            <p>Certifico que he recibido de Decathlon Chile SpA (76.507.443-6) a mi entera satisfacción el saldo indicado en la presente Liquidación y no tengo cargo ni cobro posterior que hacer.</p>
        </div>

        <div class="signature">
            <p>Firma Conforme</p>
            <div class="signature-line"></div>
        </div>
    </div>
<?php endwhile; ?>
</body>
</html>