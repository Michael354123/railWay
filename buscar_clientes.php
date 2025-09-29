<?php
if (isset($_GET['q'])) {
    $searchQuery = $_GET['q'];

    include 'server.php';

    // Segunda consulta para buscar clientes
    $query2 = "SELECT c.userid, CONCAT(c.firstname, ' ', c.lastname) AS fullname
               FROM tblcontacts c
               WHERE c.firstname LIKE '%$searchQuery%' OR c.lastname LIKE '%$searchQuery%'";
    $resultado2 = mysqli_query($conn, $query2);

    if (!$resultado2) {
        echo "Error al ejecutar la consulta: " . mysqli_error($conn);
        exit();
    }

    // Verificar si se encontraron resultados
    if (mysqli_num_rows($resultado2) > 0) {
        // Mostrar resultados en un select
        echo "<label for='client'>Seleccionar Cliente:</label>";
        echo "<select name='client' id='client' onchange='mostrarContratos(this.value)'>";
        while ($fila = mysqli_fetch_assoc($resultado2)) {
            echo "<option value='' disabled selected hidden>Seleccione el cliente</option>";
            $valor = $fila['userid'];
            $texto = $fila['fullname'];
            echo "<option value='$valor'>$texto</option>";
        }
        echo "</select>";
    } else {
        echo "No se encontraron clientes que coincidan con la búsqueda.";
    }

    mysqli_close($conn);
}
?>
