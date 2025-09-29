<?php
if (isset($_GET['q'])) {
    $searchQuery = $_GET['q'];

    include 'server.php';

    // Segunda consulta para buscar clientes
    $query2 = "SELECT s.staffid, CONCAT(s.firstname, ' ', s.lastname) AS fullname
                FROM tblstaff s
                WHERE s.firstname LIKE '%$searchQuery%' OR s.lastname LIKE '%$searchQuery%'";
    $resultado2 = mysqli_query($conn, $query2);

    if (!$resultado2) {
        echo "Error al ejecutar la consulta: " . mysqli_error($conn);
        exit();
    }

    // Verificar si se encontraron resultados
    if (mysqli_num_rows($resultado2) > 0) {
        // Mostrar resultados en un select
        echo "<label for='staffID'>Seleccionar Empleado:</label>";
        echo "<select name='staffID' id='staffID' onchange='mostrarContratos(this.value)'>";
        while ($fila = mysqli_fetch_assoc($resultado2)) {
            echo "<option value='' disabled selected hidden>Seleccione el Empleado</option>";
            $valor = $fila['staffid'];
            $texto = $fila['fullname'];
            echo "<option value='$valor'>$texto</option>";
        }
        echo "</select>";
    } else {
        echo "No se encontraron Empleados que coincidan con la búsqueda.";
    }

    mysqli_close($conn);
}
?>
