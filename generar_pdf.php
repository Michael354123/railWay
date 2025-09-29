<?php
// Incluir la biblioteca TCPDF
require_once('tcpdf/tcpdf.php');

// Verificar si se han proporcionado los datos de las tablas en la cadena de consulta
if (isset($_POST['tableHTML1']) && isset($_POST['tableHTML2']) && isset($_POST['tableHTML3']) && isset($_POST['tableHTML4']) && isset($_POST['tableHTML5']) && isset($_POST['tableHTML6']) && isset($_POST['tableHTML7']) && !empty($_POST['tableHTML1']) && !empty($_POST['tableHTML2']) && !empty($_POST['tableHTML3']) && !empty($_POST['tableHTML4']) && !empty($_POST['tableHTML5']) && !empty($_POST['tableHTML6']) && !empty($_POST['tableHTML7'])) {
    $tableHTML1 = $_POST['tableHTML1'];
    $tableHTML2 = $_POST['tableHTML2'];
    $tableHTML3 = $_POST['tableHTML3'];
    $tableHTML4 = $_POST['tableHTML4'];
    $tableHTML5 = $_POST['tableHTML5'];
    $tableHTML6 = $_POST['tableHTML6'];
    $tableHTML7 = $_POST['tableHTML7'];

    // Combinar el contenido HTML de todas las tablas en una sola cadena
    $combinedHTML = $tableHTML1 . "<br><br>" . $tableHTML2 . "<br><br>" . $tableHTML3 . "<br><br>" . $tableHTML4 . "<br><br>" . $tableHTML5 . "<br><br>" . $tableHTML6 . "<br><br>" . $tableHTML7;

    // Crear una instancia de TCPDF
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Configurar información del PDF
    $pdf->SetCreator('Mi Aplicación');
    $pdf->SetAuthor('Mi Autor');
    $pdf->SetTitle('Plan de Pago');

    // Agregar una página
    $pdf->AddPage();

    // Escribir el contenido HTML combinado en el PDF
    $pdf->writeHTML($combinedHTML, true, false, true, false, '');

    // Enviar el PDF al navegador
    $pdf->Output('plan_de_pago.pdf', 'I');
} else {
    echo "No se han proporcionado los datos de todas las tablas.";
}
?>
