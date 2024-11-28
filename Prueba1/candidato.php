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

// Manejar la creación del candidato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_candidato = $_POST['rut_candidato'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $nombre1 = $_POST['nombre1'];
    $nombre2 = $_POST['nombre2'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $cargo_postular = $_POST['cargo_postular'];
    $deporte_interes = $_POST['deporte_interes'];

    // Insertar el candidato en la base de datos
    $query_insert_candidato = "INSERT INTO JS_FS_FV_VV_CANDIDATO 
                               (RUT_CANDIDATO, CORREO, FECHA_NACIMIENTO, NOMBRE1, NOMBRE2, APELLIDO1, APELLIDO2, CARGO_POSTULAR, DEPORTE_INTERES)
                               VALUES (:rut_candidato, :correo, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :nombre1, :nombre2, :apellido1, :apellido2, :cargo_postular, :deporte_interes)";
    $stmt_insert = oci_parse($conn, $query_insert_candidato);
    oci_bind_by_name($stmt_insert, ':rut_candidato', $rut_candidato);
    oci_bind_by_name($stmt_insert, ':correo', $correo);
    oci_bind_by_name($stmt_insert, ':fecha_nacimiento', $fecha_nacimiento);
    oci_bind_by_name($stmt_insert, ':nombre1', $nombre1);
    oci_bind_by_name($stmt_insert, ':nombre2', $nombre2);
    oci_bind_by_name($stmt_insert, ':apellido1', $apellido1);
    oci_bind_by_name($stmt_insert, ':apellido2', $apellido2);
    oci_bind_by_name($stmt_insert, ':cargo_postular', $cargo_postular);
    oci_bind_by_name($stmt_insert, ':deporte_interes', $deporte_interes);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Candidato creado con éxito: " . htmlspecialchars($nombre1) . " " . htmlspecialchars($apellido1);
    } else {
        $e = oci_error($stmt_insert);
        $mensaje = "Error al crear el candidato: " . htmlspecialchars($e['message']);
    }
}

// Obtener la lista de cargos disponibles
$query_cargos = "SELECT ID_CARGO, NOMBRE_CARGO FROM JS_FS_FV_VV_Cargo ORDER BY NOMBRE_CARGO";
$stmt_cargos = oci_parse($conn, $query_cargos);
oci_execute($stmt_cargos);

// Obtener la lista de deportes disponibles
$query_deportes = "SELECT ID_Deporte, Deporte FROM JS_FS_FV_VV_Deportes ORDER BY Deporte";
$stmt_deportes = oci_parse($conn, $query_deportes);
oci_execute($stmt_deportes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Candidato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-body">
                <h1 class="text-center text-primary">Crear Candidato</h1>
                <?php if (isset($mensaje)): ?>
                    <div class="alert <?php echo strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> mt-3">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="rut_candidato" class="form-label">RUT del Candidato:</label>
                        <input type="number" id="rut_candidato" name="rut_candidato" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre1" class="form-label">Primer Nombre:</label>
                        <input type="text" id="nombre1" name="nombre1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre2" class="form-label">Segundo Nombre:</label>
                        <input type="text" id="nombre2" name="nombre2" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="apellido1" class="form-label">Primer Apellido:</label>
                        <input type="text" id="apellido1" name="apellido1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido2" class="form-label">Segundo Apellido:</label>
                        <input type="text" id="apellido2" name="apellido2" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="cargo_postular" class="form-label">Cargo al que Postula:</label>
                        <select id="cargo_postular" name="cargo_postular" class="form-select" required>
                            <option value="">-- Seleccione un cargo --</option>
                            <?php while ($cargo = oci_fetch_assoc($stmt_cargos)): ?>
                                <option value="<?php echo htmlspecialchars($cargo['ID_CARGO']); ?>">
                                    <?php echo htmlspecialchars($cargo['NOMBRE_CARGO']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deporte_interes" class="form-label">Deporte de Interés:</label>
                        <select id="deporte_interes" name="deporte_interes" class="form-select" required>
                            <option value="">-- Seleccione un deporte --</option>
                            <?php while ($deporte = oci_fetch_assoc($stmt_deportes)): ?>
                                <option value="<?php echo htmlspecialchars($deporte['ID_DEPORTE']); ?>">
                                    <?php echo htmlspecialchars($deporte['DEPORTE']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Crear Candidato</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
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

// Manejar la creación del candidato
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut_candidato = $_POST['rut_candidato'];
    $correo = $_POST['correo'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $nombre1 = $_POST['nombre1'];
    $nombre2 = $_POST['nombre2'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $cargo_postular = $_POST['cargo_postular'];
    $deporte_interes = $_POST['deporte_interes'];

    // Insertar el candidato en la base de datos
    $query_insert_candidato = "INSERT INTO JS_FS_FV_VV_CANDIDATO 
                               (RUT_CANDIDATO, CORREO, FECHA_NACIMIENTO, NOMBRE1, NOMBRE2, APELLIDO1, APELLIDO2, CARGO_POSTULAR, DEPORTE_INTERES)
                               VALUES (:rut_candidato, :correo, TO_DATE(:fecha_nacimiento, 'YYYY-MM-DD'), :nombre1, :nombre2, :apellido1, :apellido2, :cargo_postular, :deporte_interes)";
    $stmt_insert = oci_parse($conn, $query_insert_candidato);
    oci_bind_by_name($stmt_insert, ':rut_candidato', $rut_candidato);
    oci_bind_by_name($stmt_insert, ':correo', $correo);
    oci_bind_by_name($stmt_insert, ':fecha_nacimiento', $fecha_nacimiento);
    oci_bind_by_name($stmt_insert, ':nombre1', $nombre1);
    oci_bind_by_name($stmt_insert, ':nombre2', $nombre2);
    oci_bind_by_name($stmt_insert, ':apellido1', $apellido1);
    oci_bind_by_name($stmt_insert, ':apellido2', $apellido2);
    oci_bind_by_name($stmt_insert, ':cargo_postular', $cargo_postular);
    oci_bind_by_name($stmt_insert, ':deporte_interes', $deporte_interes);

    if (oci_execute($stmt_insert)) {
        $mensaje = "Candidato creado con éxito: " . htmlspecialchars($nombre1) . " " . htmlspecialchars($apellido1);
    } else {
        $e = oci_error($stmt_insert);
        $mensaje = "Error al crear el candidato: " . htmlspecialchars($e['message']);
    }
}

// Obtener la lista de cargos disponibles
$query_cargos = "SELECT ID_CARGO, NOMBRE_CARGO FROM JS_FS_FV_VV_Cargo ORDER BY NOMBRE_CARGO";
$stmt_cargos = oci_parse($conn, $query_cargos);
oci_execute($stmt_cargos);

// Obtener la lista de deportes disponibles
$query_deportes = "SELECT ID_Deporte, Deporte FROM JS_FS_FV_VV_Deportes ORDER BY Deporte";
$stmt_deportes = oci_parse($conn, $query_deportes);
oci_execute($stmt_deportes);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Candidato</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow-lg">
            <div class="card-body">
                <h1 class="text-center text-primary">Crear Candidato</h1>
                <?php if (isset($mensaje)): ?>
                    <div class="alert <?php echo strpos($mensaje, 'Error') !== false ? 'alert-danger' : 'alert-success'; ?> mt-3">
                        <?php echo $mensaje; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="rut_candidato" class="form-label">RUT del Candidato:</label>
                        <input type="number" id="rut_candidato" name="rut_candidato" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="correo" class="form-label">Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento:</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre1" class="form-label">Primer Nombre:</label>
                        <input type="text" id="nombre1" name="nombre1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="nombre2" class="form-label">Segundo Nombre:</label>
                        <input type="text" id="nombre2" name="nombre2" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="apellido1" class="form-label">Primer Apellido:</label>
                        <input type="text" id="apellido1" name="apellido1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="apellido2" class="form-label">Segundo Apellido:</label>
                        <input type="text" id="apellido2" name="apellido2" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="cargo_postular" class="form-label">Cargo al que Postula:</label>
                        <select id="cargo_postular" name="cargo_postular" class="form-select" required>
                            <option value="">-- Seleccione un cargo --</option>
                            <?php while ($cargo = oci_fetch_assoc($stmt_cargos)): ?>
                                <option value="<?php echo htmlspecialchars($cargo['ID_CARGO']); ?>">
                                    <?php echo htmlspecialchars($cargo['NOMBRE_CARGO']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="deporte_interes" class="form-label">Deporte de Interés:</label>
                        <select id="deporte_interes" name="deporte_interes" class="form-select" required>
                            <option value="">-- Seleccione un deporte --</option>
                            <?php while ($deporte = oci_fetch_assoc($stmt_deportes)): ?>
                                <option value="<?php echo htmlspecialchars($deporte['ID_DEPORTE']); ?>">
                                    <?php echo htmlspecialchars($deporte['DEPORTE']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Crear Candidato</button>
                </form>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
