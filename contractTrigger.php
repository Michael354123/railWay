<?php
include 'server.php';

// Realizar la consulta SELECT
$result = mysqli_query($conn, "SELECT * FROM tblcontracts");

if (!$result) {
    die("Error al ejecutar la consulta: " . mysqli_error($conn));
}

// Iterar sobre los resultados
while ($fila = mysqli_fetch_assoc($result)) {
    // Contar cuántos registros tienen la columna note como 'retraso' para cada contrato
    $consulta_contador_retraso = mysqli_prepare($conn, "SELECT COUNT(*) FROM tblinvoicepaymentrecords WHERE invoiceid = ? AND note = 'retraso'");
    mysqli_stmt_bind_param($consulta_contador_retraso, "i", $fila['subject']);
    mysqli_stmt_execute($consulta_contador_retraso);
    mysqli_stmt_bind_result($consulta_contador_retraso, $cantidad_retrasos);
    mysqli_stmt_fetch($consulta_contador_retraso);
    mysqli_stmt_close($consulta_contador_retraso);

    // Contar cuántos registros tienen la columna note como 'mora' para cada contrato
    $consulta_contador_mora = mysqli_prepare($conn, "SELECT COUNT(*) FROM tblinvoicepaymentrecords WHERE invoiceid = ? AND note = 'mora'");
    mysqli_stmt_bind_param($consulta_contador_mora, "i", $fila['subject']);
    mysqli_stmt_execute($consulta_contador_mora);
    mysqli_stmt_bind_result($consulta_contador_mora, $cantidad_mora);
    mysqli_stmt_fetch($consulta_contador_mora);
    mysqli_stmt_close($consulta_contador_mora);

    // Si hay 3 o más registros con la columna note como 'retraso', actualizar la columna status de tblcontracts
    if ($cantidad_retrasos >= 1) {
        $consulta_actualizar = mysqli_prepare($conn, "UPDATE tblinvoices SET status = 4 WHERE number = ?");
        mysqli_stmt_bind_param($consulta_actualizar, "i", $fila['subject']);
        mysqli_stmt_execute($consulta_actualizar);
        mysqli_stmt_close($consulta_actualizar);
    }

    // Si hay 3 o más registros con la columna note como 'mora', actualizar la columna status de tblcontracts
    if ($cantidad_mora >= 1) {
        $consulta_actualizar_mora = mysqli_prepare($conn, "UPDATE tblinvoices SET status = 5 WHERE number = ?");
        mysqli_stmt_bind_param($consulta_actualizar_mora, "i", $fila['subject']);
        mysqli_stmt_execute($consulta_actualizar_mora);
        mysqli_stmt_close($consulta_actualizar_mora);
    }

    // Otras acciones basadas en la lógica de tu aplicación...
}

// Cerrar la conexión a la base de datos
mysqli_close($conn);
?>
