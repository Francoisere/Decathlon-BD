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

// Manejar acciones de contratación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_candidato = $_POST['RUT_CANDIDATO'];
    $RUT_ENTREVISTADOR = $_SESSION['usuario']['RUT_USUARIO'];
    
    if (isset($_POST['proceso1'])) {
        $estado_proceso1 = ($_POST['estado_proceso1'] === 'Aceptado') ? 1 : 3; // Aceptado o Rechazado
        $comentario = $_POST['comentario_proceso1'];
    
        $query_check = "SELECT COUNT(*) AS TOTAL FROM JS_FS_FV_VV_Proceso_Contratacion1 WHERE RUT_Candidato = :RUT_CANDIDATO";
        $stmt_check = oci_parse($conn, $query_check);
        oci_bind_by_name($stmt_check, ':RUT_CANDIDATO', $rut_candidato);
        oci_execute($stmt_check);
        $result = oci_fetch_assoc($stmt_check);
    
        if ($result['TOTAL'] > 0) {
            // Si ya existe, realizar un UPDATE
            $query_update = "UPDATE JS_FS_FV_VV_Proceso_Contratacion1
                             SET Estado = :estado, Comentario = :comentario, Fecha_Inicio = SYSDATE, RUT_ENTREVISTADOR = :RUT_ENTREVISTADOR
                             WHERE RUT_Candidato = :RUT_CANDIDATO";
            $stmt_update = oci_parse($conn, $query_update);
            oci_bind_by_name($stmt_update, ':estado', $estado_proceso1);
            oci_bind_by_name($stmt_update, ':comentario', $comentario);
            oci_bind_by_name($stmt_update, ':RUT_ENTREVISTADOR', $RUT_ENTREVISTADOR);
            oci_bind_by_name($stmt_update, ':RUT_CANDIDATO', $rut_candidato);
    
            if (!oci_execute($stmt_update)) {
                $e = oci_error($stmt_update);
                echo "Error al actualizar el Proceso 1: " . htmlspecialchars($e['message']);
            }
        } else {
            // Si no existe, realizar un INSERT
            $query_insert = "INSERT INTO JS_FS_FV_VV_Proceso_Contratacion1 
                             (ID_Proceso1, Fecha_Inicio, Estado, RUT_Candidato, RUT_ENTREVISTADOR, Comentario) 
                             VALUES (SEQ_PROCESO1.NEXTVAL, SYSDATE, :estado, :RUT_CANDIDATO, :RUT_ENTREVISTADOR, :comentario)";
            $stmt_insert = oci_parse($conn, $query_insert);
            oci_bind_by_name($stmt_insert, ':estado', $estado_proceso1);
            oci_bind_by_name($stmt_insert, ':RUT_CANDIDATO', $rut_candidato);
            oci_bind_by_name($stmt_insert, ':RUT_ENTREVISTADOR', $RUT_ENTREVISTADOR);
            oci_bind_by_name($stmt_insert, ':comentario', $comentario);
    
            if (!oci_execute($stmt_insert)) {
                $e = oci_error($stmt_insert);
                echo "Error al completar el Proceso 1: " . htmlspecialchars($e['message']);
            }
        }
    }  elseif (isset($_POST['proceso2'])) {
        $estado_proceso2 = ($_POST['estado_proceso2'] === 'Aceptado') ? 1 : 3; // Aceptado o Rechazado
        $comentario = $_POST['comentario_proceso2'];

        // Verificar si el candidato ya tiene un registro en Proceso 2
        $query_check = "SELECT COUNT(*) AS TOTAL FROM JS_FS_FV_VV_Proceso_Contratacion2 WHERE RUT_Candidato = :RUT_CANDIDATO";
        $stmt_check = oci_parse($conn, $query_check);
        oci_bind_by_name($stmt_check, ':RUT_CANDIDATO', $rut_candidato);
        oci_execute($stmt_check);
        $result = oci_fetch_assoc($stmt_check);

        if ($result['TOTAL'] > 0) {
            // Si ya existe, realizar un UPDATE
            $query_update = "UPDATE JS_FS_FV_VV_Proceso_Contratacion2
                             SET Estado = :estado, Comentario = :comentario, Fecha_Inicio = SYSDATE, RUT_ENTREVISTADOR = :RUT_ENTREVISTADOR
                             WHERE RUT_Candidato = :RUT_CANDIDATO";
            $stmt_update = oci_parse($conn, $query_update);
            oci_bind_by_name($stmt_update, ':estado', $estado_proceso2);
            oci_bind_by_name($stmt_update, ':comentario', $comentario);
            oci_bind_by_name($stmt_update, ':RUT_ENTREVISTADOR', $RUT_ENTREVISTADOR);
            oci_bind_by_name($stmt_update, ':RUT_CANDIDATO', $rut_candidato);

            if (!oci_execute($stmt_update)) {
                $e = oci_error($stmt_update);
                echo "Error al actualizar el Proceso 2: " . htmlspecialchars($e['message']);
            }
        } else {
            // Si no existe, realizar un INSERT
            $query_insert = "INSERT INTO JS_FS_FV_VV_Proceso_Contratacion2 
                             (ID_Proceso2, Fecha_Inicio, Estado, RUT_Candidato, RUT_ENTREVISTADOR, Comentario) 
                             VALUES (SEQ_PROCESO2.NEXTVAL, SYSDATE, :estado, :RUT_CANDIDATO, :RUT_ENTREVISTADOR, :comentario)";
            $stmt_insert = oci_parse($conn, $query_insert);
            oci_bind_by_name($stmt_insert, ':estado', $estado_proceso2);
            oci_bind_by_name($stmt_insert, ':RUT_CANDIDATO', $rut_candidato);
            oci_bind_by_name($stmt_insert, ':RUT_ENTREVISTADOR', $RUT_ENTREVISTADOR);
            oci_bind_by_name($stmt_insert, ':comentario', $comentario);

            if (!oci_execute($stmt_insert)) {
                $e = oci_error($stmt_insert);
                echo "Error al completar el Proceso 2: " . htmlspecialchars($e['message']);
            }
        }

        // Si se aceptó el proceso 2, redirigir a la página de creación de contrato
        if ($estado_proceso2 == 1) {
            header("Location: crear_contrato.php?rut_candidato=$rut_candidato");
            exit;
        }
    }
}

// Consulta de candidatos pendientes
$query_candidatos = "SELECT c.RUT_CANDIDATO, c.Nombre1, c.Apellido1, ca.Nombre_Cargo, 
                            p1.Estado AS Estado_Proceso1, p1.Comentario AS Comentario_Proceso1,
                            p2.Estado AS Estado_Proceso2, p2.Comentario AS Comentario_Proceso2
                     FROM JS_FS_FV_VV_Candidato c
                     LEFT JOIN JS_FS_FV_VV_Cargo ca ON c.Cargo_Postular = ca.ID_Cargo
                     LEFT JOIN JS_FS_FV_VV_Proceso_Contratacion1 p1 ON c.RUT_CANDIDATO = p1.RUT_Candidato
                     LEFT JOIN JS_FS_FV_VV_Proceso_Contratacion2 p2 ON c.RUT_CANDIDATO = p2.RUT_Candidato
                     WHERE (p2.Estado IS NULL OR p2.Estado != 1)";
$stmt_candidatos = oci_parse($conn, $query_candidatos);
oci_execute($stmt_candidatos);
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratar Personal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #007bff;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table th {
            background-color: #007bff;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contratar Personal</h1>
        <table>
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cargo Postulado</th>
                    <th>Proceso 1</th>
                    <th>Proceso 2</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt_candidatos)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['RUT_CANDIDATO']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE1']); ?></td>
                        <td><?php echo htmlspecialchars($row['APELLIDO1']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE_CARGO']); ?></td>
                        <td>
                            Estado: <?php echo htmlspecialchars($row['ESTADO_PROCESO1'] ?? 'Pendiente'); ?><br>
                            Comentario: <?php echo htmlspecialchars($row['COMENTARIO_PROCESO1'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            Estado: <?php echo htmlspecialchars($row['ESTADO_PROCESO2'] ?? 'Pendiente'); ?><br>
                            Comentario: <?php echo htmlspecialchars($row['COMENTARIO_PROCESO2'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            <form method="POST" style="margin-bottom: 10px;">
                                <input type="hidden" name="RUT_CANDIDATO" value="<?php echo $row['RUT_CANDIDATO']; ?>">
                                <select name="estado_proceso1" required>
                                    <option value="Aceptado">Aceptar</option>
                                    <option value="Rechazado">Rechazar</option>
                                </select>
                                <input type="text" name="comentario_proceso1" placeholder="Comentario">
                                <button type="submit" name="proceso1">Actualizar Proceso 1</button>
                            </form>
                            <form method="POST">
                                <input type="hidden" name="RUT_CANDIDATO" value="<?php echo $row['RUT_CANDIDATO']; ?>">
                                <select name="estado_proceso2" required>
                                    <option value="Aceptado">Aceptar</option>
                                    <option value="Rechazado">Rechazar</option>
                                </select>
                                <input type="text" name="comentario_proceso2" placeholder="Comentario">
                                <button type="submit" name="proceso2">Actualizar Proceso 2</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>