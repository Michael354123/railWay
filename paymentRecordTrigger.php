<?php
include 'server.php';

// Obtener la fecha del sistema
$fecha_sistema = date("Y-m-d");

// Calcular la fecha un día antes
$fecha_anterior = date("Y-m-d", strtotime("-1 day", strtotime($fecha_sistema)));

// Calcular la fecha 30 días antes
$fecha_30dias_antes = date("Y-m-d", strtotime("-30 day", strtotime($fecha_sistema)));

// Realizar la consulta para actualizar la columna note en tblinvoicepaymentrecords
$consulta_retraso = mysqli_prepare($conn, "UPDATE tblinvoicepaymentrecords SET note = 'retraso' WHERE date = ? AND note != 'pagado'");
mysqli_stmt_bind_param($consulta_retraso, "s", $fecha_anterior);
mysqli_stmt_execute($consulta_retraso);

// Realizar la consulta para actualizar la columna note en tblinvoicepaymentrecords a 'mora' para registros con más de 30 días
$consulta_mora = mysqli_prepare($conn, "UPDATE tblinvoicepaymentrecords SET note = 'mora' WHERE date <= ? AND note != 'pagado'");
mysqli_stmt_bind_param($consulta_mora, "s", $fecha_30dias_antes);
mysqli_stmt_execute($consulta_mora);

// Verificar si se ejecutaron las consultas correctamente
if (mysqli_stmt_affected_rows($consulta_retraso) > 0 || mysqli_stmt_affected_rows($consulta_mora) > 0) {
    echo "Se actualizaron los registros con éxito.";
} else {
    echo "No se realizaron actualizaciones.";
}

// Cerrar las consultas y la conexión a la base de datos
mysqli_stmt_close($consulta_retraso);
mysqli_stmt_close($consulta_mora);
mysqli_close($conn);
?>
