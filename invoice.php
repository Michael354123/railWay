
<?php
// Iniciar la sesión
session_start();

// Verificar si la variable de sesión 'user_id' no está establecida
if (!isset($_SESSION['user_id'])) {
    // El usuario no ha iniciado sesión, redirigir a la página de inicio de sesión
    header('Location: index.php');
    exit(); // Detener la ejecución del script después de la redirección
}
?>
<!DOCTYPE html>
<html>
<head>
<!-- CSS de DataTables -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">

<!-- jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>

<title>PLANES DE PAGO ATC</title>
    <!-- Agregar la fuente Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* Estilo personalizado para las filas de la tabla */
        .stripe tbody tr.odd,
        .stripe tbody tr.even {
            background-color: #f2f2f2; /* Color de fondo alternado */
        }
        body {
            font-family: 'Montserrat', sans-serif; /* Aplicar la fuente Montserrat */
            margin: 0;
            padding: 0;
        }
        .sidenav {
            height: 100vh;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0; /* Cambiado para mantener la barra lateral visible */
            background-color: #333;
            overflow-y: auto;
            padding-top: 20px;
            transition: left 0.5s; /* Agregar transición para animación */
        }
        .sidenav a {
            padding: 10px 15px;
            text-decoration: none;
            font-size: 18px;
            color: #fff;
            display: block;
            transition: 0.3s;
        }
        /* Estilos para el enlace activo */
        .sidenav a.active {
            background-color: #828635; /* Color de fondo para el enlace activo */
            color: white; /* Color del texto para el enlace activo */
        }
        .sidenav a:hover {
            background-color: #555;
        }
        .main {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.5s;
        }
        .payment-button {
            padding: 8px 12px;
            background-color: #4CAF50; /* Color de fondo */
            color: white; /* Color del texto */
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none; /* Quitar subrayado del enlace */
            border-radius: 5px; /* Bordes redondeados */
            transition: background-color 0.3s, color 0.3s; /* Transición suave */
        }
        .payment-button:hover {
            background-color: #45a049; /* Cambiar color de fondo al pasar el ratón por encima */
        }
        .create-plan-button {
            padding: 8px 12px;
            background-color: #4CAF50; /* Color de fondo */
            color: white; /* Color del texto */
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none; /* Quitar subrayado del enlace */
            border-radius: 5px; /* Bordes redondeados */
            transition: background-color 0.3s, color 0.3s; /* Transición suave */
        }
        .create-plan-button {
            background-color: #45a049; /* Cambiar color de fondo al pasar el ratón por encima */
        }
        /* Estilos para la tabla */
        #miTabla {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #miTabla th,
        #miTabla td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        #miTabla th {
            background-color: #f2f2f2;
            font-weight: bold; /* Texto en negrita */
        }

        /* Estilos para resaltar el título */
        .main p {
            font-size: 24px; /* Tamaño grande de fuente */
            font-weight: bold; /* Texto en negrita */
            margin-bottom: 10px; /* Espacio inferior */
        }
        h2 {
            color: #fff; /* Color verde para el H2 */
            background-color: #828635; /* Fondo blanco para el H2 */
            padding: 10px 20px; /* Ajuste del padding */
            margin: 0; /* Eliminar margen */
        }
        h3, p {
            color: #828635;
        }
    </style>
</head>
<body>
<?php
        // Tu conexión y consulta SQL aquí
        include 'server.php';

        $query = "SELECT 
        c.id, 
        COALESCE(CONCAT(i.prefix, LPAD(c.subject, 6, '0')), LPAD(c.subject, 6, '0')) AS 'invoice_number',
        cl.userid,
        CONCAT(ct.firstname, ' ', ct.lastname) AS 'name',
        c.datestart, 
        i.payment_date, 
        MAX(ir.amount) AS last_paid_amount,
        i.subtotal,
        SUM(pr.paid_amount) AS total_paid,
        (SELECT ir.num_record 
         FROM tblinvoicepaymentrecords ir 
         WHERE ir.note = 'por pagar' && ir.invoiceid = c.subject 
         LIMIT 1) AS num_record,
        i.status,
        c.payment_plan_exist,
        c.subject,
        i.id as invoideId
    FROM 
        tblcontracts AS c
    INNER JOIN 
        tblinvoices AS i ON i.number = c.subject
    LEFT JOIN 
        tblclients AS cl ON c.client = cl.userid
    LEFT JOIN 
        tblcontacts AS ct ON cl.userid = ct.userid
    LEFT JOIN 
        tblinvoicepaymentrecords AS ir ON i.number = ir.invoiceid
    LEFT JOIN 
        tblpaymentrecordsdetails AS pr ON ir.id = pr.idpaymentrecord
    GROUP BY 
        c.id, 
        invoice_number,
        cl.userid, 
        name, 
        datestart, 
        payment_date, 
        subtotal,
        status,
        payment_plan_exist,
        invoideId;";
        
        $resultado = mysqli_query($conn, $query);

        if (!$resultado) {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
    ?>
    <!-- Botón para abrir/cerrar la barra lateral -->
    <button class="openbtn" onclick="toggleNav()">☰</button>

    <div class="sidenav" id="mySidenav">
        <!-- Lista de enlaces para la navegación -->
        <a href="#"></a>
        <a href="index.php">INICIO</a>
        <a href="clients.php">CLIENTES y CONTRATOS</a>
        <a href="invoice.php"  class="active">PLANES DE PAGO</a>
        <a href="historialPagos.php">HISTORIAL DE PAGOS</a>
        <a href="#" onclick="closeNav()">REPORTES</a>
        <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/" >VOLVER AL CRM</a>
    </div>

    <div class="main">
        <!-- Contenido principal de la página -->
        <h2>LISTA DE CONTRATOS (PLANES DE PAGO)</h2>
        <br>
        <br>
        <!-- Botón para redirigir a la vista de crear plan de pagos -->
        <button class="create-plan-button" onclick="redirectToCreatePlan()">Crear Plan de Pagos</button>
        <br>
        <form id="exportarForm" action="exportar_tabla_excel.php" method="post">
            <input type="hidden" id="tablaHtml" name="tablaHtml" value="">
            <button type="button" onclick="exportarTabla()">Exportar a Excel</button>
        </form>
        <br>
        <!-- Tabla para mostrar los resultados -->
        <!-- crear nuevo campo diaPago en invoice -->
        <table id="miTabla" class="display">
            <thead>
                <tr>
                    <th>Nro Contrato</th>
                    <th>Nombre del Cliente</th>
                    <th>Fecha de Contrato</th>
                    <th>Dia de Pago</th>
                    <th>Valor de Cuota</th>
                    <th>Precio Convenido</th>
                    <th>Valor Pagado</th>
                    <th>Cuota a pagar</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Refinanciar</th>
                    <th>Fusionar</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Generar filas de tabla con datos de la consulta
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $fila['invoice_number'] . "</td>";
                    echo "<td>" . $fila['name'] . "</td>";
                    echo "<td>" . date('d/m/Y', strtotime($fila['datestart'])) . "</td>";
                    echo "<td>" . $fila['payment_date'] . "</td>";  
                    echo "<td>" . $fila['last_paid_amount'] . "</td>";                             
                    echo "<td>" . $fila['subtotal'] . "</td>";
                    echo "<td>" . $fila['total_paid'] . "</td>";
                    echo "<td>" . $fila['num_record'] . "</td>";
                    echo "<td>" . convertirEstado($fila['status']). "</td>";  
                    //echo "<td><a class='payment-button' href='pago.php?id=" . $fila['subject'] . "'>Pagar</a></td>";                
                    if ($fila['payment_plan_exist'] == 1) {
                        if(convertirEstado($fila['status']) == "FUSIONADO"){
                            echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Pagar</a></td>";
                        }
                        else{
                            echo "<td><a class='payment-button' href='pago.php?id=" . $fila['subject'] . "'>Pagar</a></td>";
                        }
                        //echo "<td><a class='payment-button' href='invoiceEdit.php?id=" . $fila['subject'] . "'>Editar</a></td>";
                        if(convertirEstado($fila['status']) == "MORA"){           
                            
                            echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Refinanciar</a></td>";
                            echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Fusionar</a></td>";
                        }else{
                            echo "<td><a class='payment-button' href='refinanciamiento.php?id=" . $fila['subject'] . "'>Refinanciar</a></td>";
                            echo "<td><a class='payment-button' href='fusionar.php?id=" . $fila['subject'] . "'>Fusionar</a></td>";
                        }                                               
                    } else {    
                        echo "<td><a class='payment-button' href='invoiceEdit.php?id=" . $fila['subject']. "&uid=" . $fila['userid'] . "'>Crear</a></td>";
                        echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Refinanciar</a></td>";
                        echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Fusionar</a></td>";
                    }                    
                    echo "</tr>";
                }
                // Función para convertir el número de estado a texto
                function convertirEstado($estado_num) {
                    switch ($estado_num) {
                        case 1:
                            return "POR COBRAR";
                            break;
                        case 2:
                            return "PAGADO";
                            break;
                        case 3:
                            return "PARCIALMENTE";
                            break;
                        case 4:
                            return "RETRASADO";
                            break;
                        case 5:
                            return "MORA";
                            break;
                        case 6:
                            return "FUSIONADO";
                            break;
                        default:
                            return "";
                    }
                }

                // Liberar el conjunto de resultados
                mysqli_free_result($resultado);
                // Cerrar la conexión
                mysqli_close($conn);
                ?>
            </tbody>
        </table>

    </div>

    <script>

function exportarTabla() {
    var tablaOriginal = document.getElementById('miTabla');
    var filasOriginales = tablaOriginal.getElementsByTagName('tr');
    
    // Crear una tabla nueva
    var nuevaTabla = document.createElement('table');
    
    // Copiar las filas y columnas de la tabla original a la nueva tabla, excluyendo las dos últimas columnas
    for (var i = 0; i < filasOriginales.length; i++) {
        var filaOriginal = filasOriginales[i];
        var nuevaFila = document.createElement('tr');
        var columnasOriginales = filaOriginal.getElementsByTagName('td');
        
        for (var j = 0; j < columnasOriginales.length - 2; j++) { // Excluir las dos últimas columnas
            var nuevaColumna = document.createElement('td');
            nuevaColumna.innerHTML = columnasOriginales[j].innerHTML;
            nuevaFila.appendChild(nuevaColumna);
        }
        
        // Excluir las dos últimas celdas del encabezado
        if (i === 0) {
            var encabezadosOriginales = filaOriginal.getElementsByTagName('th');
            for (var k = 0; k < encabezadosOriginales.length - 2; k++) { // Excluir las dos últimas columnas
                var nuevoEncabezado = document.createElement('th');
                nuevoEncabezado.innerHTML = encabezadosOriginales[k].innerHTML;
                nuevaFila.appendChild(nuevoEncabezado);
            }
        }
        
        // Agregar la fila a la nueva tabla
        nuevaTabla.appendChild(nuevaFila);
    }
    
    // Obtener el contenido HTML de la nueva tabla
    var tablaHtml = nuevaTabla.outerHTML;
    
    // Establecer el contenido HTML de la tabla en el input hidden del formulario
    document.getElementById("tablaHtml").value = tablaHtml;
    
    // Enviar el formulario
    document.getElementById("exportarForm").submit();
}


$(document).ready(function() {
    $('#miTabla').DataTable({
        "columnDefs": [{
            "targets": 8, // Índice de la columna que deseas colorear
            "render": function(data, type, row) {
                // Cambiar el color de fondo basado en el valor
                if (data === "MORA") {
                    return '<div style="background-color: #E85C5C; padding: 5px;">' + data + '</div>';
                }
                if (data === "RETRASADO") {
                    return '<div style="background-color: #F09500; padding: 5px;">' + data + '</div>';
                }
                // Retornar el valor normal si no se cumple ninguna condición
                return data;
            }
        }]
    });
});


        // Función para abrir la barra lateral
        function openNav() {
            document.getElementById("mySidenav").style.left = "0";
            document.getElementsByClassName("main")[0].style.marginLeft = "250px";
        }

        // Función para cerrar la barra lateral
        function closeNav() {
            document.getElementById("mySidenav").style.left = "-250px";
            document.getElementsByClassName("main")[0].style.marginLeft = "0";
        }

        // Función para alternar entre abrir y cerrar la barra lateral
        function toggleNav() {
            var sidenav = document.getElementById("mySidenav");
            if (sidenav.style.left === "0px") {
                closeNav();
            } else {
                openNav();
            }
        }
        function redirectToCreatePlan() {
            window.location.href = "invoiceEdit.php"; // Reemplaza "crear_plan_de_pagos.php" con la ruta correcta a tu vista de crear plan de pagos
        }
    </script>
</body>
</html>
<?php
// Realizar una solicitud HTTP al script update_invoicepaymentrecords.php en segundo plano
$actualizacion = file_get_contents('http://localhost/asd/pagos/contractTrigger.php');
$actializar = file_get_contents('http://localhost/asd/pagos/paymentRecordTrigger.php');
// No es necesario mostrar el resultado en la página principal
// Si deseas mostrar un mensaje de confirmación, puedes hacerlo de manera controlada, por ejemplo, utilizando logs o almacenando el resultado en una variable de sesión.
?>
