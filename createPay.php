<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include 'server.php';
    $number = $_POST['invoice_number'];
    $idpaymentrecord = $_POST['idpaymentRecord'];
    $invoiceid = $_POST['invoiceid'];
    $staffID = $_POST['staffID'];
    $proof_payment = $_POST['transactionid'];
    $pago = $_POST['pago'];
    $saldo = $_POST['saldo'];
    $payment_method = $_POST['paymentmethod'];
    $payment_date = $_POST['daterecorded'];
    $detail = $_POST['detail'];

    $paid_amount = $pago - $saldo;

    $contractValue = null;

    $sql_invoiceid = "SELECT invoiceid FROM tblinvoicepaymentrecords WHERE id = ?";
    $stmt_invoiceid = $conn->prepare($sql_invoiceid);

    if ($stmt_invoiceid === false) {
        echo "Error en la preparación de la consulta para obtener invoiceid: " . $conn->error;
        exit;
    }

    $stmt_invoiceid->bind_param("i", $idpaymentrecord);
    $stmt_invoiceid->execute();
    $stmt_invoiceid->bind_result($contractValue);
    $stmt_invoiceid->fetch();
    $stmt_invoiceid->close();

    if ($contractValue !== null) {
        echo "El invoiceid recuperado es: " . $contractValue;

    } else {
        echo "No se pudo obtener el invoiceid para el idpaymentrecord proporcionado.";
    }

    $sql_invoices = "INSERT INTO tblpaymentrecordsDetails (idpaymentrecord, paid_amount, payment_date, detail, staffID, proof_payment, payment_method) 
                        VALUES (?, ?, ?, ?, ?, ?, ?);";

    $stmt_invoices = $conn->prepare($sql_invoices);
    if ($stmt_invoices === false) {
        echo "Error en la preparación de la consulta para tblinvoices: " . $conn->error;
        exit;
    }

    $stmt_invoices->bind_param("idssiss",
        $idpaymentrecord, $paid_amount, $payment_date, $detail, $staffID, $proof_payment, $payment_method
    );

      if ($stmt_invoices->execute() === TRUE) {

        $sql1 = "SELECT SUM(ipd.paid_amount) AS total_paid, MAX(ipr.amount) AS invoice_amount
                                FROM tblinvoicepaymentrecords ipr
                                INNER JOIN tblpaymentrecordsDetails ipd ON ipd.idpaymentrecord = ipr.id
                                WHERE ipr.id = ?;";
        // Intenta preparar la consulta
        $stmt1 = $conn->prepare($sql1);
        if (!$stmt1) {
            die("Error al preparar la consulta SQL: " . $conn->error);
        }

        // Vincular parámetro y ejecutar la consulta
        $stmt1->bind_param("i", $idpaymentrecord);
        $stmt1->execute();

        // Obtener resultados
        $result1 = $stmt1->get_result();

        if ($result1->num_rows > 0) {
            $row1 = $result1->fetch_assoc();
            $totalPaid1 = $row1["total_paid"];
            $invoiceAmount1 = $row1["invoice_amount"];

            // Actualizar la nota de la factura según el estado de pago
            if ($totalPaid1 < $invoiceAmount1) {
                $note1 = 'parcialmente pagado';
                $status1 = 3;
            } else {
                $note1 = 'pagado';
                $status1 = 2;
                }

            // Actualizar tblinvoicepaymentrecords de manera segura
            $sqlUpdateInvoicePayment1 = "UPDATE tblinvoicepaymentrecords
                                                        SET note = ?
                                                        WHERE id = ?";

            // Intenta preparar la consulta de actualización
            $stmtUpdateInvoicePayment1 = $conn->prepare($sqlUpdateInvoicePayment1);
            if (!$stmtUpdateInvoicePayment1) {
                die("Error al preparar la consulta de actualización: " . $conn->error);
            }

            // Vincular parámetros y ejecutar la consulta de actualización
            $stmtUpdateInvoicePayment1->bind_param("si", $note1, $idpaymentrecord);
            $stmtUpdateInvoicePayment1->execute();

            // Verificar errores en la ejecución de la consulta de actualización
            if ($stmtUpdateInvoicePayment1->error) {
                echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoicePayment1->error;
            }

            // Cerrar statement de actualización
            $stmtUpdateInvoicePayment1->close();

            // Actualizar tblinvoicepaymentrecords de manera segura
            $sqlUpdateInvoice1 = "UPDATE tblinvoices
                            SET status = ?
                            WHERE number = ?";

            // Intenta preparar la consulta de actualización
            $stmtUpdateInvoice1 = $conn->prepare($sqlUpdateInvoice1);
            if (!$stmtUpdateInvoice1) {
                die("Error al preparar la consulta de actualización: " . $conn->error);
            }

            // Vincular parámetros y ejecutar la consulta de actualización
            $stmtUpdateInvoice1->bind_param("ii", $status1, $idpaymentrecord);
            $stmtUpdateInvoice1->execute();

            // Verificar errores en la ejecución de la consulta de actualización
            if ($stmtUpdateInvoice1->error) {
                echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoice1->error;
            }

            // Cerrar statement de actualización
            $stmtUpdateInvoice1->close();

            if ($saldo > 0) {
                $max_iterations = 25; // o algún número razonable
                $iteration = 0;
                while ($saldo > 0 && $iteration < $max_iterations) {
                    $iteration++;
                    $sql_last_id = "SELECT ip.id, ip.amount
                        FROM tblinvoicepaymentrecords ip
                        WHERE ip.invoiceid = $invoiceid AND (ip.note = 'parcialmente pagado' OR ip.note = 'por pagar')
                        ORDER BY ip.id DESC
                        LIMIT 1";

                    $detailLast = "excedente de pago";
                    $result = $conn->query($sql_last_id);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $last_id = intval($row['id']);
                        $last_amount = doubleval($row['amount']);                  
    
                        $sql_excedente = "INSERT INTO tblpaymentrecordsDetails (idpaymentrecord, paid_amount, payment_date, detail, staffID, proof_payment, payment_method) 
                                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $stmt_excedente = $conn->prepare($sql_excedente);
                        if ($stmt_excedente === false) {
                            echo "Error en la preparación de la consulta para el excedente de pago: " . $conn->error;
                            exit;
                        }
                        $paid_amount_excedente = 0;                   
                        if($saldo > $last_amount){
                            $saldo = $saldo - $last_amount;
                            $paid_amount_excedente = $last_amount;
                        }
                        else{
                            $paid_amount_excedente = $saldo;
                            $saldo = 0;
                        }
                        $stmt_excedente->bind_param("idssiss", $last_id, $paid_amount_excedente, $payment_date, $detailLast, $staffID, $proof_payment, $payment_method);
                        if ($stmt_excedente->execute() === TRUE) {
    
    
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
                            $stmt->bind_param("i", $last_id);
                            $stmt->execute();
    
                            // Obtener resultados
                            $result2 = $stmt->get_result();
    
                            if ($result2->num_rows > 0) {
                                $row2 = $result2->fetch_assoc();
                                $totalPaid = $row2["total_paid"];
                                $invoiceAmount = $row2["invoice_amount"];
    
                                // Actualizar la nota de la factura según el estado de pago
                                if ($totalPaid < $invoiceAmount) {
                                    $note2 = 'parcialmente pagado';
                                    $status = 3;
                                } else {
                                    $note2 = 'pagado';
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
                                $stmtUpdateInvoicePayment->bind_param("si", $note2, $last_id);
                                $stmtUpdateInvoicePayment->execute();
    
                                // Verificar errores en la ejecución de la consulta de actualización
                                if ($stmtUpdateInvoicePayment->error) {
                                    echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoicePayment->error;
                                }
    
                                // Cerrar statement de actualización
                                $stmtUpdateInvoicePayment->close();
    
                                // Actualizar tblinvoicepaymentrecords de manera segura
                                $sqlUpdateInvoice2 = "UPDATE tblinvoices
                                SET status = ?
                                WHERE number = ?";
    
                                // Intenta preparar la consulta de actualización
                                $stmtUpdateInvoice2 = $conn->prepare($sqlUpdateInvoice2);
                                if (!$stmtUpdateInvoice2) {
                                die("Error al preparar la consulta de actualización: " . $conn->error);
                                }
    
                                // Vincular parámetros y ejecutar la consulta de actualización
                                $stmtUpdateInvoice2->bind_param("ii", $status2, $last_id);
                                $stmtUpdateInvoice2->execute();
    
                                // Verificar errores en la ejecución de la consulta de actualización
                                if ($stmtUpdateInvoice2->error) {
                                echo "Error al actualizar tblinvoicepaymentrecords: " . $stmtUpdateInvoice2->error;
                                }
    
                                // Cerrar statement de actualización
                                $stmtUpdateInvoice2->close();
                            } else {
                                echo "No se encontraron registros para el idpaymentrecord: $last_id";
                            }
    
                            // Cerrar statement de consulta principal
                            $stmt->close();
    
    
                        } else {
                            echo "Error al insertar el excedente de pago: " . $conn->error;
                        }
                        $stmt_excedente->close();
                    } else {
                        echo "No se pudo encontrar el último idpaymentrecord para el invoiceid proporcionado.";
                    }
                }
                
            }

            } else {
                echo "No se encontraron registros para el idpaymentrecord: $last_id";
            }

            // Cerrar statement de consulta principal
            $stmt1->close();
                        
        
        echo "Pago registrado exitosamente";
    } else {
        echo "Error al registrar el pago: " . $conn->error;
    }

    $stmt_invoices->close();
    $conn->close(); 

    header("Location: pago.php?id=$contractValue");
    exit();
}
?>