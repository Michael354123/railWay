<?php
if(isset($_POST['tablaHtml'])) {
    // Recibir la tabla HTML enviada desde el formulario
    $tablaHtml = $_POST['tablaHtml'];

    // Encabezados para forzar la descarga de un archivo Excel
    header("Pragma: public");
    header("Expires: 0");
    $filename = "report.xls";
    header("Content-type: application/vnd.ms-excel"); // Cambiado a application/vnd.ms-excel para Excel
    header("Content-Disposition: attachment; filename=$filename");
    header("Pragma: no-cache");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

    // Comenzar la salida HTML
    echo '<table>';
    echo $tablaHtml; // Imprimir la tabla HTML recibida
    echo '</table>';
    exit; // Salir del script después de generar el archivo Excel
}