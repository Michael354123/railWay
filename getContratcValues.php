<?php
// Verificar si se ha enviado el cliente ID a través de GET
if (isset($_GET['id'])) {
    $subject = $_GET['id'];

    // Incluir el archivo de conexión a la base de datos
    include 'server.php';

    // Consulta para buscar contratos del cliente seleccionado utilizando una consulta preparada
    $query = "SELECT c.description, c.contract_value, ct.name, c.datestart, c.new_contract_value
                FROM tblcontracts c
                LEFT JOIN tblcontracts_types ct On ct.id = c.contract_type
                WHERE c.subject = ?";
    
    // Preparar la consulta
    $stmt = $conn->prepare($query);
    if ($stmt) {
        // Vincular parámetros y ejecutar la consulta
        $stmt->bind_param("s", $subject);
        $stmt->execute();

        // Obtener resultados
        $resultado = $stmt->get_result();

        // Verificar si la consulta fue exitosa
        if ($resultado->num_rows > 0) {
            // Obtener el resultado como un array asociativo
            $row = $resultado->fetch_assoc();
            // Crear un array para enviar como respuesta
            $response = array(
                'description' => $row['description'],
                'contract_value' => $row['contract_value'],
                'tipo_servicio' => $row['name'],
                'fecha' => $row['datestart'],
                'new_contract_value' => $row['new_contract_value']
            );
        } else {
            $response = array(
                'description' => 'No se encontraron promociones',
                'contract_value' => 0.00,
                'tipo_servicio' => 'No se encontraron',
                'fecha' => '',
                'new_contract_value' => ''
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
