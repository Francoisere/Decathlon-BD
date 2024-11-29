<?php
session_start();
include 'conexion.php';

// Verificar si el usuario tiene permisos para acceder
if (!isset($_SESSION['usuario'])) {
    die("Error: No has iniciado sesión.");
}

// Permitir acceso a administradores (PODER = 1) y líderes (PODER = 3)
if ($_SESSION['usuario']['PODER'] != 1 && $_SESSION['usuario']['PODER'] != 3) {
    die("Error: No tienes permisos para acceder a esta página.");
}

// Función para eliminar al candidato y sus registros asociados de manera ordenada
function eliminarCandidato($conn, $rut_candidato) {
    // Eliminar los registros en la tabla de procesos de contratación 1
    $query_delete_proceso1 = "DELETE FROM JS_FS_FV_VV_Proceso_Contratacion1 WHERE RUT_Candidato = :RUT_CANDIDATO";
    $stmt_delete_proceso1 = oci_parse($conn, $query_delete_proceso1);
    oci_bind_by_name($stmt_delete_proceso1, ':RUT_CANDIDATO', $rut_candidato);
    if (!oci_execute($stmt_delete_proceso1)) {
        $e = oci_error($stmt_delete_proceso1);
        die("Error al eliminar del proceso 1: " . htmlspecialchars($e['message']));
    }

    // Eliminar los registros en la tabla de procesos de contratación 2
    $query_delete_proceso2 = "DELETE FROM JS_FS_FV_VV_Proceso_Contratacion2 WHERE RUT_Candidato = :RUT_CANDIDATO";
    $stmt_delete_proceso2 = oci_parse($conn, $query_delete_proceso2);
    oci_bind_by_name($stmt_delete_proceso2, ':RUT_CANDIDATO', $rut_candidato);
    if (!oci_execute($stmt_delete_proceso2)) {
        $e = oci_error($stmt_delete_proceso2);
        die("Error al eliminar del proceso 2: " . htmlspecialchars($e['message']));
    }

    // Finalmente eliminar al candidato de la tabla de candidatos
    $query_delete_candidato = "DELETE FROM JS_FS_FV_VV_Candidato WHERE RUT_CANDIDATO = :RUT_CANDIDATO";
    $stmt_delete_candidato = oci_parse($conn, $query_delete_candidato);
    oci_bind_by_name($stmt_delete_candidato, ':RUT_CANDIDATO', $rut_candidato);
    if (!oci_execute($stmt_delete_candidato)) {
        $e = oci_error($stmt_delete_candidato);
        die("Error al eliminar el candidato: " . htmlspecialchars($e['message']));
    }
}


// Manejar acciones de contratación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_candidato = $_POST['RUT_CANDIDATO'];
    $RUT_ENTREVISTADOR = $_SESSION['usuario']['RUT_USUARIO'];

    if (isset($_POST['proceso1'])) {
        $estado_proceso1 = ($_POST['estado_proceso1'] === '1') ? 1 : 3; // Aceptado o Rechazado
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
            oci_execute($stmt_update);
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
            oci_execute($stmt_insert);
        }

        // Si rechazado, eliminar al candidato
        if ($estado_proceso1 == 3) {
            eliminarCandidato($conn, $rut_candidato);
        }
    } elseif (isset($_POST['proceso2'])) {
        $estado_proceso2 = ($_POST['estado_proceso2'] === '1') ? 1 : 3; // Aceptado o Rechazado
        $comentario = $_POST['comentario_proceso2'];

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
            oci_execute($stmt_update);
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
            oci_execute($stmt_insert);
        }

        // Si aceptado, redirigir a crear contrato
        if ($estado_proceso2 == 1) {
            header("Location: crear_contrato.php?rut_candidato=$rut_candidato");
            exit;
        }

        // Si rechazado, eliminar al candidato
        if ($estado_proceso2 == 3) {
            eliminarCandidato($conn, $rut_candidato);
        }
    }
}

// Consulta de candidatos pendientes
$query_candidatos = "
    SELECT 
        c.RUT_CANDIDATO, 
        c.Nombre1, 
        c.Apellido1, 
        c.Curriculum,
        ca.Nombre_Cargo AS Cargo_Postulado, 
        d.Deporte AS Deporte_Interesado,
        p1.Estado AS Estado_Proceso1, 
        p1.Comentario AS Comentario_Proceso1,
        p2.Estado AS Estado_Proceso2, 
        p2.Comentario AS Comentario_Proceso2
    FROM JS_FS_FV_VV_Candidato c
    LEFT JOIN JS_FS_FV_VV_Cargo ca ON c.Cargo_Postular = ca.ID_Cargo
    LEFT JOIN JS_FS_FV_VV_Deportes d ON c.Deporte_interes = d.ID_Deporte
    LEFT JOIN JS_FS_FV_VV_Proceso_Contratacion1 p1 ON c.RUT_CANDIDATO = p1.RUT_Candidato
    LEFT JOIN JS_FS_FV_VV_Proceso_Contratacion2 p2 ON c.RUT_CANDIDATO = p2.RUT_Candidato
    WHERE (p2.Estado IS NULL OR p2.Estado != 1)
";
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
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
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
        button:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
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
    <div class="container">
        <h1>Contratar Personal</h1>
        <table>
            <thead>
                <tr>
                    <th>RUT</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cargo Postulado</th>
                    <th>Deporte</th>
                    <th>Currículum</th>
                    <th>Aptitudes Físicas</th>
                    <th>Aptitudes Psicológicas</th>
                    <th>Comentarios</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = oci_fetch_assoc($stmt_candidatos)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['RUT_CANDIDATO']); ?></td>
                        <td><?php echo htmlspecialchars($row['NOMBRE1']); ?></td>
                        <td><?php echo htmlspecialchars($row['APELLIDO1']); ?></td>
                        <td><?php echo htmlspecialchars($row['CARGO_POSTULADO']); ?></td>
                        <td><?php echo htmlspecialchars($row['DEPORTE_INTERESADO']); ?></td>
                        <td>
                            <?php if (!empty($row['CURRICULUM'])): ?>
                                <a href="<?php echo htmlspecialchars($row['CURRICULUM']); ?>" target="_blank">Descargar</a>
                            <?php else: ?>
                                No disponible
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- Aptitudes Físicas -->
                            <form method="POST">
                                <input type="hidden" name="RUT_CANDIDATO" value="<?php echo $row['RUT_CANDIDATO']; ?>">
                                <select name="estado_proceso1" <?php echo ($row['ESTADO_PROCESO2'] == 1 || $row['ESTADO_PROCESO2'] == 3 || $row['ESTADO_PROCESO1'] == 1) ? 'disabled' : ''; ?>>
                                    <option value="1">Aceptar</option>
                                    <option value="3">Rechazar</option>
                                </select>
                                <input type="text" name="comentario_proceso1" placeholder="Comentario" <?php echo ($row['ESTADO_PROCESO2'] == 1 || $row['ESTADO_PROCESO2'] == 3 || $row['ESTADO_PROCESO1'] == 1) ? 'disabled' : ''; ?>>
                                <button type="submit" name="proceso1" <?php echo ($row['ESTADO_PROCESO2'] == 1 || $row['ESTADO_PROCESO2'] == 3 || $row['ESTADO_PROCESO1'] == 1) ? 'disabled' : ''; ?>>Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <!-- Aptitudes Psicológicas -->
                            <form method="POST">
                                <input type="hidden" name="RUT_CANDIDATO" value="<?php echo $row['RUT_CANDIDATO']; ?>">
                                <select name="estado_proceso2" <?php echo ($row['ESTADO_PROCESO1'] != 1 || $row['ESTADO_PROCESO2'] == 1) ? 'disabled' : ''; ?>>
                                    <option value="1">Aceptar</option>
                                    <option value="3">Rechazar</option>
                                </select>
                                <input type="text" name="comentario_proceso2" placeholder="Comentario" <?php echo ($row['ESTADO_PROCESO1'] != 1 || $row['ESTADO_PROCESO2'] == 1) ? 'disabled' : ''; ?>>
                                <button type="submit" name="proceso2" <?php echo ($row['ESTADO_PROCESO1'] != 1 || $row['ESTADO_PROCESO2'] == 1) ? 'disabled' : ''; ?>>Actualizar</button>
                            </form>
                        </td>
                        <td>
                            <strong>Aptitudes Físicas:</strong>
                            <?php echo htmlspecialchars($row['COMENTARIO_PROCESO1'] ?? 'Sin comentarios'); ?><br>
                            <strong>Aptitudes Psicológicas:</strong>
                            <?php echo htmlspecialchars($row['COMENTARIO_PROCESO2'] ?? 'Sin comentarios'); ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <a href="accediste.php" class="back-button" onclick="return confirmExit();">← Volver al menú</a>
    </div>
</body>
</html>