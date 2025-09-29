<?php
// Verificar si se ha enviado el cliente ID a través de GET
if (isset($_GET['subject'])) {
    $subject = $_GET['subject'];

    // Incluir el archivo de conexión a la base de datos
    include 'server.php';

    // Consulta para obtener el clientID basado en el subject proporcionado
    $queryClientID = "SELECT cl.userid AS clientID
                        FROM tblcontracts c
                        INNER JOIN tblclients cl ON c.client = cl.userid
                        WHERE c.subject = $subject;";

    // Ejecutar la consulta para obtener el clientID
    $resultadoClientID = $conn->query($queryClientID);

    // Verificar si la consulta fue exitosa
    if ($resultadoClientID->num_rows > 0) {
        // Obtener el resultado (debería ser único)
        $filaClientID = $resultadoClientID->fetch_assoc();
        $clientID = $filaClientID['clientID'];

        // Consulta para buscar contratos del cliente seleccionado
        $queryContratos = "SELECT c.subject, CONCAT('Contrato: ', LPAD(c.subject, 6, '0')) AS contract_number
                                FROM tblcontracts c
                                INNER JOIN tblclients cl ON c.client = cl.userid
                                INNER JOIN tblcontacts co ON cl.userid = co.userid
                                INNER JOIN tblinvoices i ON i.number = c.subject
                                WHERE co.userid = $clientID AND c.payment_plan_exist = 1 && i.status != 4 && i.status != 5 && c.subject != $subject
                                group by c.subject;";

        // Ejecutar la consulta de los contratos
        $resultadoContratos = $conn->query($queryContratos);

        // Verificar si se encontraron resultados
        if ($resultadoContratos->num_rows > 0) {
            // Mostrar resultados en un select
            echo "<label for='Contrato'>Seleccionar Contrato:</label>";
            echo "<select name='Contrato' id='Contrato' onchange='seleccionarContrato(this.value)'>";
            echo "<option value='' disabled selected hidden>Seleccione el contrato</option>";
            while ($fila = $resultadoContratos->fetch_assoc()) {
                $valor = $fila['subject'];
                $texto = $fila['contract_number'];
                echo "<option value='$valor'>$texto</option>";
            }
            echo "</select>";
        } else {
            echo "No se encontraron contratos para este cliente.";
        }
    } else {
        echo "No se encontró ningún cliente con el subject proporcionado.";
    }

    // Cerrar la conexión a la base de datos
    $conn->close();
}
?>
