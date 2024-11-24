<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos de administrador
if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

if ($_SESSION['usuario']['PODER'] != 1) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Verificar si el RUT del candidato ha sido pasado como parámetro
if (!isset($_GET['rut_candidato'])) {
    die("Error: No se proporcionó el RUT del candidato.");
}

$rut_candidato = $_GET['rut_candidato'];

// Consultar información del candidato
$query_candidato = "SELECT c.RUT_CANDIDATO, c.Nombre1, c.Apellido1, ca.Nombre_Cargo, ca.ID_CARGO
                    FROM JS_FS_FV_VV_Candidato c
                    LEFT JOIN JS_FS_FV_VV_Cargo ca ON c.Cargo_Postular = ca.ID_CARGO
                    WHERE c.RUT_CANDIDATO = :RUT_CANDIDATO";
$stmt_candidato = oci_parse($conn, $query_candidato);

oci_bind_by_name($stmt_candidato, ':RUT_CANDIDATO', $rut_candidato);

if (!oci_execute($stmt_candidato)) {
    $e = oci_error($stmt_candidato);
    die("Error al obtener información del candidato: " . htmlspecialchars($e['message']));
}

$candidato = oci_fetch_assoc($stmt_candidato);

if (!$candidato) {
    die("Error: No se encontró información del candidato.");
}

// Manejar la creación del contrato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $duracion_meses = $_POST['duracion_meses'];
    $horas_asignadas = $_POST['horas_asignadas'];
    $salario = $_POST['salario'];
    $renovacion_automatica = isset($_POST['renovacion_automatica']) ? 1 : 0;

    // Insertar el contrato en la base de datos
    $query_insert_contrato = "INSERT INTO JS_FS_FV_VV_Contratos
                              (ID_Contrato, Duracion_Meses, Horas_Asignadas, Fecha_Contratacion, ID_CARGO, Renovacion_Automatica, Salario, RUT_CANDIDATO)
                              VALUES (SEQ_CONTRATO.NEXTVAL, :duracion_meses, :horas_asignadas, SYSDATE, :id_cargo, :renovacion_automatica, :salario, :rut_candidato)";
    $stmt_insert = oci_parse($conn, $query_insert_contrato);
    oci_bind_by_name($stmt_insert, ':duracion_meses', $duracion_meses);
    oci_bind_by_name($stmt_insert, ':horas_asignadas', $horas_asignadas);
    oci_bind_by_name($stmt_insert, ':id_cargo', $candidato['ID_CARGO']);
    oci_bind_by_name($stmt_insert, ':renovacion_automatica', $renovacion_automatica);
    oci_bind_by_name($stmt_insert, ':salario', $salario);
    oci_bind_by_name($stmt_insert, ':rut_candidato', $rut_candidato);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Contrato creado con éxito para el candidato " . htmlspecialchars($candidato['NOMBRE1']) . " " . htmlspecialchars($candidato['APELLIDO1']) . ".";
    } else {
        $e = oci_error($stmt_insert);
        $mensaje = "Error al crear el contrato: " . htmlspecialchars($e['message']);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Contrato</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            padding: 20px;
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        h1 {
            color: #007bff;
        }
        form {
            margin-top: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .mensaje {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Crear Contrato</h1>
        <?php if (isset($mensaje)): ?>
            <p class="<?php echo strpos($mensaje, 'Error') !== false ? 'error' : 'mensaje'; ?>">
                <?php echo $mensaje; ?>
            </p>
        <?php endif; ?>
        <p><strong>Candidato:</strong> <?php echo htmlspecialchars($candidato['NOMBRE1'] . " " . $candidato['APELLIDO1']); ?></p>
        <p><strong>Cargo:</strong> <?php echo htmlspecialchars($candidato['NOMBRE_CARGO']); ?></p>
        <form method="POST">
            <label for="duracion_meses">Duración del Contrato (en meses):</label>
            <input type="number" id="duracion_meses" name="duracion_meses" min="1" required>
            
            <label for="horas_asignadas">Horas Asignadas:</label>
            <input type="number" id="horas_asignadas" name="horas_asignadas" min="1" required>

            <label for="salario">Salario:</label>
            <input type="number" id="salario" name="salario" min="1" required>
            
            <label for="renovacion_automatica">Renovación Automática:</label>
            <input type="checkbox" id="renovacion_automatica" name="renovacion_automatica">

            <button type="submit">Crear Contrato</button>
        </form>
    </div>
</body>
</html>
