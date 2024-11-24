<?php
// Configuración de la conexión
$usuario = "Prueba1";
$contrasena = "12345";
$cadena_conexion = "26.4.143.85/XEPDB1";

$conn = oci_connect($usuario, $contrasena, $cadena_conexion);

if (!$conn) {
    $e = oci_error();
    die("Error de conexión: " . $e['message']);
}
?>

