<?php
// Verificar si se han enviado los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir el archivo de conexión a la base de datos
    include 'server.php';
    // Variables para guardar el resultado del select
    $numCycles = 0;
    $last_record = 0;

    // Realizar el select
    $sql_select = "SELECT i.cycles, 
                          (SELECT ip.num_record 
                           FROM tblinvoicepaymentrecords ip 
                           WHERE ip.invoiceid = i.number && note != 'por pagar' 
                           ORDER BY ip.num_record DESC 
                           LIMIT 1) as last_record 
                   FROM tblinvoices i 
                   WHERE number = ?";

    // Preparar la sentencia para el select
    $stmt_select = $conn->prepare($sql_select);

    // Verificar si la preparación de la consulta tuvo éxito
    if ($stmt_select === false) {
        // Si la preparación falló, mostrar el mensaje de error
        echo "Error en la preparación de la consulta select: " . $conn->error;
        exit; // Salir del script
    }

    // Vincular el parámetro con el valor recuperado del formulario para el select
    $stmt_select->bind_param("i", $_POST['number']);

    // Ejecutar la consulta select
    $stmt_select->execute();

    // Vincular los resultados de la consulta select a variables
    $stmt_select->bind_result($numCycles, $last_record);

    // Obtener los resultados de la consulta select
    $stmt_select->fetch();

    // Cerrar la sentencia para el select
    $stmt_select->close();

    $cycles = $_POST['numCuotas'];
    $totalCycles = $last_record + $cycles;
    $paymentmode = $_POST['metodo_pago'];
    $monto_cuota = $_POST['valorCuota'];
    $dia_pago = $_POST['fecha_pago_cuota'];
    $duedate = $_POST['pago_limite'];
    $number = $_POST['number'];

    $status = 1;
    $sale_agent = 1;

    // Obtener solo la fecha actual del sistema
    $currentDate = date('Y-m-d');
    // Preparar la consulta SQL para insertar en tblinvoices
    $sql_invoices = "UPDATE tblinvoices SET number = ?,
                duedate = ?,
                cycles = ?,
                sale_agent = ?,
                payment_date = ? 
                WHERE number = ?";

    // Preparar la sentencia para tblinvoices
    $stmt_invoices = $conn->prepare($sql_invoices);

    // Verificar si la preparación de la consulta tuvo éxito
    if ($stmt_invoices === false) {
        // Si la preparación falló, mostrar el mensaje de error
        echo "Error en la preparación de la consulta para tblinvoices: " . $conn->error;
        exit; // Salir del script
    }

    // Vincular los parámetros con los valores recuperados del formulario para tblinvoices
    $stmt_invoices->bind_param("isiiii",
    $number,
    $duedate,  
    $totalCycles,
    $sale_agent,
    $dia_pago,
    $number
);
                              
    // Ejecutar la sentencia para tblinvoices
    if ($stmt_invoices->execute()) {
        echo "Datos insertados correctamente en tblinvoices.";

        $sql_contract = "DELETE FROM tblinvoicepaymentrecords
                            WHERE invoiceid = ? && note = 'por pagar';";
        // Preparar la sentencia para tblinvoices
        $stmt_contract = $conn->prepare($sql_contract);
        // Verificar si la preparación de la consulta tuvo éxito
        if ($stmt_contract === false) {
            // Si la preparación falló, mostrar el mensaje de error
            echo "Error en la preparación de la consulta para tblcontracts: " . $conn->error;
            exit; // Salir del script
        }

        // Vincular el parámetro con el valor recuperado del formulario para tblcontracts
        $stmt_contract->bind_param("s", $number);

        // Ejecutar la sentencia para tblcontracts
        if ($stmt_contract->execute()) {

            // Preparar la consulta SQL para insertar en tblinvoicepaymentrecords
            $sql_payment = "INSERT INTO tblinvoicepaymentrecords (invoiceid, amount, paymentmethod, date, daterecorded, note, num_record) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)";

            // Preparar la sentencia para tblinvoicepaymentrecords
            $stmt_payment = $conn->prepare($sql_payment);

            // Verificar si la preparación de la consulta tuvo éxito
            if ($stmt_payment === false) {
                // Si la preparación falló, mostrar el mensaje de error
                echo "Error en la preparación de la consulta para tblinvoicepaymentrecords: " . $conn->error;
                exit; // Salir del script
            }

            // Variables para la inserción en tblinvoicepaymentrecords
            $daterecorded = date('Y-m-d H:i:s');
            $note = 'por pagar';

            // Bucle para realizar múltiples inserciones según el valor de $cycles
            for ($i = 0; $i < $cycles; $i++) {
                // Sumar un mes a la fecha actual
                $date = date('Y-m-d', strtotime("$currentDate +$i month"));
                // Establecer el día de pago de la cuota
                $date = date('Y-m-', strtotime($date)) . $dia_pago;
                // Asignar el valor de $amount en la primera inserción y $monto_cuota en las siguientes
                $amount_value = $monto_cuota;
                $newNumRecord = $last_record+$i;
                // Vincular los parámetros con los valores recuperados del formulario para tblinvoicepaymentrecords
                $stmt_payment->bind_param("idssssi", $number, $amount_value, $paymentmode, $date, $daterecorded, $note, $newNumRecord);

                // Ejecutar la sentencia para tblinvoicepaymentrecords
                if ($stmt_payment->execute()) {
                    echo "Datos insertados correctamente en tblinvoicepaymentrecords para la iteración " . ($i + 1) . ".";       
                } else {
                    echo "Error al insertar los datos en tblinvoicepaymentrecords para la iteración " . ($i + 1) . ": " . $stmt_payment->error;
                }
            }
            // Cerrar la sentencia para tblinvoicepaymentrecords
            $stmt_payment->close();        
        }
        else{
            echo "Error al eliminar las cuotas: " . $stmt_contract->error;
        }

        $stmt_contract->close();

    } else {
        // Si la ejecución de la consulta falló, mostrar el mensaje de error
        echo "Error al insertar los datos en tblinvoices: " . $stmt_invoices->error;
    }

    // Cerrar la conexión
    $stmt_invoices->close();
    $conn->close();

    header("Location: pago.php?id=$number");
    exit();
}
?>
