<?php
// Verificar si se ha enviado el cliente ID a través de GET
if (isset($_GET['clientID'])) {
    $clientID = $_GET['clientID'];

    // Incluir el archivo de conexión a la base de datos
    include 'server.php';

    // Consulta para buscar contratos del cliente seleccionado
    $query = "SELECT c.subject, CONCAT('Contrato: ', LPAD(c.subject, 6, '0')) AS contract_number
                FROM tblcontracts c
                INNER JOIN tblclients cl ON c.client = cl.userid
                INNER JOIN tblcontacts co ON cl.userid = co.userid
                WHERE co.userid = $clientID AND c.payment_plan_exist IS NULL;";

    // Ejecutar la consulta
    $resultado = $conn->query($query);

    // Verificar si la consulta fue exitosa

    if (mysqli_num_rows($resultado) > 0) {
        // Mostrar resultados en un select
        echo "<label for='Contrato'>Seleccionar Contrato:</label>";
        echo "<select name='Contrato' id='Contrato' onchange='seleccionarContrato(this.value)'>";
        while ($fila = mysqli_fetch_assoc($resultado)) {
            echo "<option value='' disabled selected hidden>Seleccione el contrato</option>";
            $valor = $fila['subject'];
            $texto = $fila['contract_number'];
            echo "<option value='$valor'>$texto</option>";
        }
        echo "</select>";
    } else {
        echo "No se encontraron clientes que coincidan con la búsqueda.";
    }

    // Cerrar la conexión a la base de datos
    $conn->close();
}
?>
