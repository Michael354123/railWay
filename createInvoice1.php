<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'server.php';

    $clientid = $_POST['client_id'];
    $prefix = $_POST['tipo_servicio'];
    $total = $_POST['valorTotal'];
    
    $cycles = $_POST['numCuotas'];
    $amount = $_POST['cuotaIni'];
    $paymentmode = $_POST['metodo_pago'];
    $on_account = $_POST['cuota_inicial_cuenta'];
    $monto_cuota = $_POST['valorCuota'];
    $dia_pago = $_POST['fecha_pago_cuota'];
    $duedate = $_POST['pago_limite'];
    $number = $_POST['number'];
    $discount_type = $_POST['descuento_name'];
    $discount_percent = $_POST['descuento_porcentaje'];
    $discount_total  = $_POST['descuento_total'];
    $subtotal = $_POST['precioConvenido'];
    $saldo = $_POST['cuota_inicial_saldo'];
    
    $proof_payment = $_POST['nrorecibo'];
    $prefix = $prefix . '-';
    $datecreated = date('Y-m-d H:i:s');
    $date = date('Y-m-d H:i:s');
    $currency = 1;
    $status = 1;
    $sale_agent = 1;
    $project_id = 0;
    $hash = '';
    $billing_country = 27;
    $include_shipping = 0;

    $currentDate = date('Y-m-d');
            $sql_invoices = "INSERT INTO tblinvoices (
                clientid,
                number,
                prefix,
                datecreated,
                date,
                duedate,
                currency,
                subtotal,
                total,
                hash,
                discount_percent,
                discount_total,
                discount_type,
                cycles,
                sale_agent,
                billing_country,
                include_shipping,
                project_id,
                payment_date
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? 
            )";

    $stmt_invoices = $conn->prepare($sql_invoices);

    if ($stmt_invoices === false) {
        echo "Error en la preparación de la consulta para tblinvoices: " . $conn->error;
        exit;
    }

    $stmt_invoices->bind_param("iissssiddsddsiiiiii",
    $clientid,
    $number,
    $prefix,
    $datecreated,
    $date,
    $duedate,
    $currency,
    $subtotal,
    $total,
    $hash,    
    $discount_percent,
    $discount_total,
    $discount_type,
    $cycles,
    $sale_agent,
    $billing_country,
    $include_shipping,
    $project_id,
    $dia_pago
);

    if ($stmt_invoices->execute()) {
        echo "Datos insertados correctamente en tblinvoices.";

        $sql_contract = "UPDATE tblcontracts SET payment_plan_exist = 1 WHERE subject = ?";

        $stmt_contract = $conn->prepare($sql_contract);

        if ($stmt_contract === false) {

            echo "Error en la preparación de la consulta para tblcontracts: " . $conn->error;
            exit;
        }
        $stmt_contract->bind_param("s", $number);

        if (!$stmt_contract->execute()) {
            echo "Error al actualizar tblcontracts: " . $stmt_contract->error;
            exit;
        }

        $stmt_contract->close();

        $sql_payment = "INSERT INTO tblinvoicepaymentrecords (invoiceid, amount, paymentmethod, date, daterecorded, note, num_record) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt_payment = $conn->prepare($sql_payment);

        if ($stmt_payment === false) {
            echo "Error en la preparación de la consulta para tblinvoicepaymentrecords: " . $conn->error;
            exit;
        }

        $daterecorded = date('Y-m-d H:i:s');
        $note = '';
        $on_account_value = 1;

        for ($i = 0; $i <= $cycles; $i++) {
            $date = date('Y-m-d', strtotime("$currentDate +$i month"));

            $date = date('Y-m-', strtotime($date)) . $dia_pago;

            $amount_value = ($i == 0) ? $amount : $monto_cuota;

            if ($i == 0) {
                $note = ($on_account < $amount_value) ? "parcialmente pagado" : "pagado";

            } else {
                $note = "por pagar";
            }

            $on_account_value = ($note == "pagado") ? 0 : $amount_value - $on_account;
            if ($i == 0) {

                $onacs = ($on_account < $amount_value) ? $on_account : $amount_value;
                $stmt_payment->bind_param("idssssd", $number, $onacs, $paymentmode, $date, $daterecorded, $note, $i);

            } else {
                $stmt_payment->bind_param("idssssd", $number, $amount_value, $paymentmode, $date, $daterecorded, $note, $i);
            }
            
            if ($stmt_payment->execute()) {
                echo "Datos insertados correctamente en tblinvoicepaymentrecords para la iteración " . ($i + 1) . ".";
                
                $idpaymentrecord = $stmt_payment->insert_id;
                $staffID = 1;
                if ($i == 0) {             
                    $detail = 'Primer pago';
                    $sql_paymentDetail = "INSERT INTO tblpaymentrecordsdetails (idpaymentrecord, paid_amount, payment_date, detail, staffID, proof_payment, payment_method) 
                        VALUES (?, ?, ?, ?, ?, ?, ?);";

                    $stmt_paymentDetails = $conn->prepare($sql_paymentDetail);

                    if ($stmt_paymentDetails === false) {
                        echo "Error en la preparación de la consulta para tblpaymentrecordsdetails: " . $conn->error;
                        exit; 
                    }
                    $paymentType= "";
                    switch ($paymentmode) {
                        case 1:
                            $paymentType = "EFECTIVO";
                            break;
                        
                        case 2:
                            $paymentType = "QR";
                            break;
                        case 3:
                            $paymentType = "TRANSFERENCIA";
                            break;
                        case 4:
                            $paymentType = "CHEQUE";
                            break;
                        default:
                            $paymentType = "asd";
                            break;
                    }
                    $stmt_paymentDetails->bind_param("idssiss",
                        $idpaymentrecord, $on_account, $datecreated, $detail, $staffID, $proof_payment, $paymentType
                    );

                    if ($stmt_paymentDetails->execute()) {
                        $idpaymentrecords_inserted = [];
                        $newPaymentRecord = $idpaymentrecord;
                        $idpaymentrecords_inserted[] = $newPaymentRecord;
                        echo "Datos insertados correctamente en tblpaymentrecordsdetails.";
                        if ($i == 0 && $saldo > 0) {
                            $num = 0;
                            $newNote = "por pagar";
                            $stmt_payment->bind_param("idssssd", $number, $saldo, $paymentmode, $date, $daterecorded, $newNote, $num);
                            
                            if ($stmt_payment->execute()) {
                                echo "Datos adicionales insertados correctamente en tblinvoicepaymentrecords.";
                            } else {
                                echo "Error al insertar datos adicionales en tblinvoicepaymentrecords: " . $stmt_payment->error;
                            }
                        }
                    }
                    else {
                        echo "Datos insertados correctamente en tblpaymentrecordsdetails.";
                    }

                    $stmt_paymentDetails->close();
                }
            } else {
                echo "Error al insertar los datos en tblinvoicepaymentrecords para la iteración " . ($i + 1) . ": " . $stmt_payment->error;
            }
        }
        $stmt_payment->close();

    } else {
        echo "Error al insertar los datos en tblinvoices: " . $stmt_invoices->error;
    }

    $stmt_invoices->close();
    $conn->close();

    $idpaymentrecords_param = implode(',', $idpaymentrecords_inserted);
    header("Location: payTrigger.php?idpaymentrecords=$idpaymentrecords_param". "&contr=" . $number);
    exit();
}
?>
