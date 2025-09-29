<?php
// Verificar si se han enviado los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir el archivo de conexión a la base de datos
    include 'server.php';

    $subject = $_POST['number'];
    $seccondSubject = $_POST['seccond_contract_ID'];
    $discount_percent = $_POST['discount_percent'];
    $total_discount = $_POST['total_discount'];
    $new_contract_value = $_POST['new_contract_value'];
    $sale_agent = 1;

    // Preparar la consulta SQL para insertar en tblinvoices
    $sql_contract = "UPDATE tblcontracts SET seccond_contract_id = ?,
                        seccond_contract_discount_percent = ?,
                        seccond_contract_discount_total = ?,
                        new_contract_value = ? 
                        WHERE subject = ?;";

    // Preparar la sentencia para tblinvoices
    $stmt_contract = $conn->prepare($sql_contract);

    // Verificar si la preparación de la consulta tuvo éxito
    if ($stmt_contract === false) {
        // Si la preparación falló, mostrar el mensaje de error
        echo "Error en la preparación de la consulta para tblinvoices: " . $conn->error;
        exit; // Salir del script
    }

    // Vincular los parámetros con los valores recuperados del formulario para tblinvoices
    $stmt_contract->bind_param("iidds",
    $seccondSubject,
    $discount_percent,  
    $total_discount,
    $new_contract_value,
    $subject
);
                              
    // Ejecutar la sentencia para tblinvoices
    if ($stmt_contract->execute()) {
        echo "Datos insertados correctamente en tblinvoices.";

        $sql_invoices = "UPDATE tblinvoices SET stauts = 6
                            WHERE number = ?;";
        // Preparar la sentencia para tblinvoices
        $stmt_invoices = $conn->prepare($sql_invoices);
        // Verificar si la preparación de la consulta tuvo éxito
        if ($stmt_invoices === false) {
            // Si la preparación falló, mostrar el mensaje de error
            echo "Error en la preparación de la consulta para tblcontracts: " . $conn->error;
            exit; // Salir del script
        }

        // Vincular el parámetro con el valor recuperado del formulario para tblcontracts
        $stmt_invoices->bind_param("i", $subject);

        // Ejecutar la sentencia para tblcontracts
        if ($stmt_invoices->execute()) {

            $sql_invoices2 = "UPDATE tblinvoices SET stauts = 6
                            WHERE number = ?;";
            // Preparar la sentencia para tblinvoices
            $stmt_invoices2 = $conn->prepare($sql_invoices2);
            // Verificar si la preparación de la consulta tuvo éxito
            if ($stmt_invoices2 === false) {
                // Si la preparación falló, mostrar el mensaje de error
                echo "Error en la preparación de la consulta para tblcontracts: " . $conn->error;
                exit; // Salir del script
            }

            // Vincular el parámetro con el valor recuperado del formulario para tblcontracts
            $stmt_invoices2->bind_param("i", $seccondSubject); 
            if ($stmt_invoices2->execute()) {     
            }
            else{
                echo "Error al eliminar las cuotas: " . $stmt_invoices2->error;
            }
            $stmt_invoices2->close();
        }
        else{
            echo "Error al eliminar las cuotas: " . $stmt_invoices->error;
        }

        $stmt_invoices->close();

    } else {
        // Si la ejecución de la consulta falló, mostrar el mensaje de error
        echo "Error al insertar los datos en tblinvoices: " . $stmt_invoices->error;
    }

    // Cerrar la conexión
    $stmt_contract->close();
    $conn->close();

    header("Location: invoiceEdit.php?id=$subject");
    exit();
}
?>
