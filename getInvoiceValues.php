<?php
// Verificar si se ha enviado el cliente ID a través de GET
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Incluir el archivo de conexión a la base de datos
    include 'server.php';

    // Consulta para buscar contratos del cliente seleccionado utilizando una consulta preparada
    $query = "SELECT note FROM tblinvoicepaymentrecords WHERE invoiceid = ?";
    
    // Preparar la consulta
    $stmt = $conn->prepare($query);
    if ($stmt) {
        // Vincular parámetros y ejecutar la consulta
        $stmt->bind_param("i", $subject);
        $stmt->execute();

        // Obtener resultados
        $resultado = $stmt->get_result();

        // Verificar si la consulta fue exitosa
        if ($resultado->num_rows > 0) {
            // Obtener el resultado como un array asociativo
            $row = $resultado->fetch_assoc();
            // Crear un array para enviar como respuesta
            $response = array(
                'note' => $row['description'],
            );
        } else {
            $response = array(
                'note' => 'no se encontraro pagos',
            );
        }
        
        // Convertir el array a JSON y enviarlo como respuesta
        echo json_encode($response);

        // Cerrar el statement
        $stmt->close();
    } else {
        // Manejo de errores si la preparación de la consulta falla
        echo json_encode(array('error' => 'Error al preparar la consulta.'));
    }

    // Cerrar la conexión a la base de datos
    $conn->close();
}
?>
