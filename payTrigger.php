<?php
if (isset($_GET['idpaymentrecords'])) {
    include 'server.php'; // Incluir archivo de conexión

    $idpaymentrecords = $_GET['idpaymentrecords'];
    $contractValue = $_GET['contr']; // Obtener los idpaymentrecords de la URL
    $idpaymentrecord_array = explode(',', $idpaymentrecords); // Convertir en array

    foreach ($idpaymentrecord_array as $idpaymentrecordd) {
        // Validar el idpaymentrecord como entero
        $idpaymentrecord = intval($idpaymentrecordd);

        // Consultar la suma de los montos pagados para la factura correspondiente de manera segura
        $sql = "SELECT SUM(ipd.paid_amount) AS total_paid, MAX(ipr.amount) AS invoice_amount
                FROM tblinvoicepaymentrecords ipr
                INNER JOIN tblpaymentrecordsDetails ipd ON ipd.idpaymentrecord = ipr.id
                WHERE ipr.id = ?;";

        // Intenta preparar la consulta
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("Error al preparar la consulta SQL: " . $conn->error);
        }

        // Vincular parámetro y ejecutar la consulta
        $stmt->bind_param("i", $idpaymentrecord);
        $stmt->execute();

        // Obtener resultados
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $totalPaid = $row["total_paid"];
            $invoiceAmount = $row["invoice_amount"];

            // Actualizar la nota de la factura según el estado de pago
            if ($totalPaid < $invoiceAmount) {
                $note = 'parcialmente pagado';
                $status = 3;
            } else {
                $note = 'pagado';
                $status = 2;
            }

            // Actualizar tblinvoicepaymentrecords de manera segura
            $sqlUpdateInvoicePayment = "UPDATE tblinvoicepaymentrecords
                                        SET note = ?
                                        WHERE id = ?";

            // Intenta preparar la consulta de actualización
            $stmtUpdateInvoicePayment = $conn->prepare($sqlUpdateInvoicePayment);
            if (!$stmtUpdateInvoicePayment) {
                die("Error al preparar la consulta de actualización: " . $conn->error);
            }

            // Vincular parámetros y ejecutar la consulta de actualización
            $stmtUpdateInvoicePayment->bind_param("si", $note, $idpaymentrecord);
            $stmtUpdateInvoicePayment->execute();

            // Verificar errores en la ejecución de la consulta de actualización
            if ($stmtUpdateInvoicePayment->error) {
                echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoicePayment->error;
            }

            // Cerrar statement de actualización
            $stmtUpdateInvoicePayment->close();

            // Actualizar tblinvoicepaymentrecords de manera segura
            $sqlUpdateInvoice = "UPDATE tblinvoices
                                    SET status = ?
                                    WHERE number = ?";

            // Intenta preparar la consulta de actualización
            $stmtUpdateInvoice = $conn->prepare($sqlUpdateInvoice);
            if (!$stmtUpdateInvoice) {
                die("Error al preparar la consulta de actualización: " . $conn->error);
            }

            // Vincular parámetros y ejecutar la consulta de actualización
            $stmtUpdateInvoice->bind_param("ii", $status, $idpaymentrecord);
            $stmtUpdateInvoice->execute();

            // Verificar errores en la ejecución de la consulta de actualización
            if ($stmtUpdateInvoice->error) {
                echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoice->error;
            }

            // Cerrar statement de actualización
            $stmtUpdateInvoice->close();
        } else {
            echo "No se encontraron registros para el idpaymentrecord: $idpaymentrecord";
        }

        // Cerrar statement de consulta principal
        $stmt->close();
    }

    // Cerrar la conexión
    $conn->close();
} else {
    echo "Parámetro idpaymentrecords no proporcionado.";
}
header("Location: pago.php?id=$contractValue");
exit();
?>
