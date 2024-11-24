<?php
include 'conexion.php';

// Obtén todos los usuarios
$query = "SELECT Rut_Usuario, Clave FROM JS_FS_FV_VV_Usuario WHERE LENGTH(Clave) < 60";
$stmt = oci_parse($conn, $query);
oci_execute($stmt);

while ($row = oci_fetch_assoc($stmt)) {
    $clave_cifrada = password_hash($row['CLAVE'], PASSWORD_BCRYPT);

    // Actualiza la contraseña cifrada en la base de datos
    $update_query = "UPDATE JS_FS_FV_VV_Usuario SET Clave = :clave_cifrada WHERE Rut_Usuario = :rut_usuario";
    $update_stmt = oci_parse($conn, $update_query);
    oci_bind_by_name($update_stmt, ':clave_cifrada', $clave_cifrada);
    oci_bind_by_name($update_stmt, ':rut_usuario', $row['RUT_USUARIO']);
    oci_execute($update_stmt);
}

oci_free_statement($stmt);
echo "Contraseñas actualizadas correctamente.";
?>
