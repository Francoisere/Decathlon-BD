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
    </style>
</head>
<body>
    <?php
    session_start();
    include 'conexion.php';

    // Obtener mes y año actuales
    $mesActual = date('F'); // Nombre completo del mes (ejemplo: "November")
    $anioActual = date('Y'); // Año actual (ejemplo: "2024")

    // Verificar la conexión
    if (!$conn) {
        $e = oci_error();
        die("Error de conexión: " . htmlspecialchars($e['message']));
    }

    // Obtener la consulta para todos los usuarios
    $query = "SELECT 
                  u.RUT_USUARIO AS Rut_Usuario,
                  u.nombre1 || ' ' || u.apellido1 AS Nombre,
                  ca.nombre_cargo AS Cargo,
                  u.salario AS Salario_Base,
                  u.salario * 0.115 AS Gratificacion,
                  (u.salario + (u.salario * 0.115)) * cc.porcentaje AS Fondo_Pensiones,
                  (u.salario + (u.salario * 0.115)) * tp.porcentaje AS Isapre,
                  50000 AS Movilizacion,
                  u.salario + 50000 + u.salario * 0.115 AS Total_Haberes,
                  ((u.salario + (u.salario * 0.115)) * cc.porcentaje) + ((u.salario + (u.salario * 0.115)) * tp.porcentaje) AS Total_Descuentos,
                  (u.salario + 50000 + u.salario * 0.115) - (((u.salario + (u.salario * 0.115)) * cc.porcentaje) + ((u.salario + (u.salario * 0.115)) * tp.porcentaje)) AS Liquido
              FROM JS_FS_FV_VV_Usuario u
              JOIN JS_FS_FV_VV_Cargo ca ON u.ID_Cargo = ca.ID_Cargo
              JOIN JS_FS_FV_VV_CC cc ON u.ID_CC = cc.ID_CC
              JOIN JS_FS_FV_VV_Tipo_Previsiones tp ON u.ID_Prevision = tp.ID_TIPO_PREVISION";

    // Preparar la consulta
    $stmt = oci_parse($conn, $query);

    // Ejecutar la consulta
    if (!oci_execute($stmt)) {
        $e = oci_error($stmt);
        die("Error en oci_execute: " . htmlspecialchars($e['message']));
    }

    // Generar una liquidación para cada usuario
    while ($row = oci_fetch_assoc($stmt)): ?>
        <div class="container">
            <div class="header">
                <h1>Liquidación de Sueldo</h1>
                <p>Empleado: Decathlon Chile SpA (76.507.443-6)</p>
                <p>Mes: <?php echo $mesActual . ' ' . $anioActual; ?></p>
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

