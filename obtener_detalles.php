<?php
// Incluir el archivo de conexión a la base de datos
include 'server.php';

// Verificar si se recibió un ID de contrato válido
if (isset($_POST['contractId'])) {
    $contractId = $_POST['contractId'];
    // Consulta para obtener los detalles del contrato seleccionado
    $query = "select pd.paid_amount, pd.payment_date, pd.detail, concat(s.firstname, ' ', s.lastname) as name, pd.proof_payment, pd.payment_method
            from tblpaymentrecordsdetails pd
            INNER JOIN tblstaff s ON s.staffid = pd.staffID
            INNER JOIN tblinvoicepaymentrecords pr ON pr.id = pd.idpaymentrecord";

    // Si $contractId es distinto de 0, agregamos la condición WHERE
    if ($contractId != 0) {
        $query .= " WHERE pr.invoiceid = $contractId";
    }

    // Ejecutar la consulta
    $result = mysqli_query($conn, $query);

    // Comprobar si hay resultados
    if ($result && mysqli_num_rows($result) > 0) {
        // Construir la tabla de resultados
        echo '<table id="tableResp" class="display">
                <thead>
                    <tr>
                        <th>Pago</th>
                        <th>Fecha Amount</th>
                        <th>Detalle</th>
                        <th>empleado</th>
                        <th>Comprobante</th>
                        <th>Metodo de pago</th>
                    </tr>
                </thead>
                <tbody>';

        // Iterar sobre los resultados y construir filas de tabla
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<tr>
                    <td>' . $row['paid_amount'] . '</td>
                    <td>' . $row['payment_date'] . '</td>
                    <td>' . $row['detail'] . '</td>
                    <td>' . $row['name'] . '</td>
                    <td>' . $row['proof_payment'] . '</td>
                    <td>' . $row['payment_method'] . '</td>
                   </tr>';
        }

        // Cerrar la tabla de resultados
        echo '</tbody></table>';
    } else {
        // No se encontraron resultados
        echo '<p>No se encontraron datos para este contrato.</p>';
    }
} else {
    // Si no se recibió un ID de contrato válido
    echo '<p>Por favor, seleccione un contrato válido.</p>';
}

// Cerrar la conexión a la base de datos
mysqli_close($conn);
?>
