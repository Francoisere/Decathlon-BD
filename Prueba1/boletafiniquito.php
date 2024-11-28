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
<a href="ver_registrados.php" class="back-button">← Volver</a>

<div class="container">
    <div class="header">
        <h1>Liquidación de Sueldo</h1>
        <p>Empleado: Decathlon Chile SpA (76.507.443-6)</p>
        <p>Mes: Noviembre 2024</p>
    </div>

    <div class="section">
        <h2>Detalles del Empleado</h2>
        <p><strong>Sr(a):</strong> Nombre del Empleado</p>
        <p><strong>RUT:</strong> 12.345.678-9</p>
        <p><strong>Cargo:</strong> Cargo del Empleado</p>
        <p><strong>Sueldo Base:</strong> $500,000</p>
    </div>

    <div class="section">
        <h2>Haberes Imponibles</h2>
        <div class="row">
            <p>Sueldo Base</p>
            <p>$500,000</p>
        </div>
        <div class="row">
            <p>Gratificación</p>
            <p>$57,500</p>
        </div>
    </div>

    <div class="section">
        <h2>Haberes No Imponibles</h2>
        <div class="row">
            <p>Movilización</p>
            <p>$20,800</p>
        </div>
        <div class="row">
            <p>Colación</p>
            <p>$42,900</p>
        </div>
    </div>

    <div class="section">
        <h2>Descuentos Legales</h2>
        <div class="row">
            <p>Fondo de Pensiones</p>
            <p>$50,000</p>
        </div>
        <div class="row">
            <p>Isapre</p>
            <p>$40,000</p>
        </div>
        <div class="row">
            <p>Impuesto Único</p>
            <p>$30,000</p>
        </div>
        <div class="row">
            <p>Seguro Cesantía</p>
            <p>$10,000</p>
        </div>
    </div>

    <div class="totals">
        <p>Total Haberes:</p>
        <p>$621,200</p>
    </div>

    <div class="totals">
        <p>Total Descuentos:</p>
        <p>$130,000</p>
    </div>

    <div class="totals" style="border-top: 2px solid #007bff; padding-top: 10px;">
        <p>Líquido a Recibir:</p>
        <p style="color: #007bff;">$491,200</p>
    </div>

    <div class="footer">
        <p>Certifico que he recibido de Decathlon Chile SpA (76.507.443-6) a mi entera satisfacción el saldo indicado en la presente Liquidación y no tengo cargo ni cobro posterior que hacer.</p>
    </div>

    <div class="signature">
        <p>Firma Conforme</p>
        <div class="signature-line"></div>
    </div>
</div>

</body>
</html>