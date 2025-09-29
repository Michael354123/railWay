<?php
    // Verificar si se ha proporcionado un parámetro de ID en la URL
    if(isset($_GET['id']) && !empty($_GET['id'])) {
        // Obtener el ID de la URL
        $id = $_GET['id'];

        // Tu conexión y consulta SQL aquí
        include 'server.php';

        // Consulta SQL para obtener los datos del contrato
        $query_contract = "SELECT c.datestart, 
                                    ct.name, 
                                    i.discount_type,
                                    i.prefix, 
                                    cl.company,
                                    cl.vat, 
                                    cl.address, 
                                    cl.phonenumber, 
                                    cn.email, 
                                    CONCAT(s.firstname, ' ', s.lastname) AS staff_name,
                                    CONCAT(LEFT(r.name, 2), '-', LEFT(s.lastname, 1), LEFT(s.firstname, 1), '-', s.phonenumber) AS staff_code,
                                    c.description, 
                                    c.contract_value, 
                                    i.discount_percent,
                                    i.subtotal AS convenid_price,
                                    (select count(*) from tblinvoicepaymentrecords where invoiceid = c.subject) AS payment_records_count,
                                    (select amount from tblinvoicepaymentrecords WHERE invoiceid = c.subject LIMIT 1) AS initial_pay,
                                    SUM(pd.paid_amount)
                            FROM tblcontracts c
                            INNER JOIN tblcontracts_types ct ON ct.id = c.contract_type
                            INNER JOIN tblinvoices i ON i.number = c.subject
                            INNER JOIN tblclients cl ON cl.userid = c.client
                            INNER JOIN tblcontacts cn ON cn.userid = cl.userid
                            LEFT JOIN tblcustomer_admins ca ON ca.customer_id = cl.userid
                            LEFT JOIN tblstaff s ON s.staffid = ca.staff_id
                            LEFT JOIN tblroles r ON r.roleid = s.role
                            INNER JOIN tblinvoicepaymentrecords pr ON pr.invoiceid = c.subject
                            INNER JOIN tblpaymentrecordsdetails pd ON pd.idpaymentrecord = pr.id
                            WHERE c.subject = $id
                            GROUP BY c.datestart, 
                                    ct.name, 
                                    i.discount_type,
                                    i.prefix,
                                    convenid_price,
                                    cl.company,
                                    cl.vat, 
                                    cl.address, 
                                    cl.phonenumber, 
                                    cn.email, 
                                    staff_name,
                                    staff_code,
                                    c.description, 
                                    c.contract_value, 
                                    i.discount_percent;";
        
        // Consulta SQL utilizando el ID proporcionado en la URL
        $query = "SELECT 
        ip.id,
        ip.num_record,
        CONCAT(i.prefix, LPAD(i.number, 6, '0')) AS invoice_number, 
        ip.amount, 
        COALESCE(SUM(pd.paid_amount), 0) AS paid_mount, 
        COALESCE(MAX(pd.proof_payment), '-') AS proof, 
        COALESCE(MAX(pd.payment_method), '-') AS payment_method, 
        ip.date, 
        COALESCE(MAX(pd.payment_date), '-') AS pay_date, 
        ip.note 
    FROM 
        tblinvoicepaymentrecords ip
    INNER JOIN 
        tblinvoices i ON ip.invoiceid = i.number
    LEFT JOIN 
        tblpaymentrecordsdetails pd ON pd.idpaymentrecord = ip.id
    WHERE 
        ip.invoiceid = $id
    GROUP BY 
        ip.id, 
        invoice_number, 
        ip.amount, 
        ip.num_record,
        ip.date, 
        ip.note;";

        $resultado_contract = mysqli_query($conn, $query_contract);
        $resultado = mysqli_query($conn, $query);

        if (!$resultado_contract || !$resultado) {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
        $saldo = 0;
    } else {
        // Si no se proporcionó un ID en la URL, mostrar un mensaje de error
        echo "No se ha proporcionado un ID válido.";
        exit();
    }
?>
<!DOCTYPE html>
<html>
<head>
<title>PAGOS ATC</title>
    <!-- Agregar la fuente Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
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
        #datos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        #datos th,
        #datos td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        #datos th {
            background-color: #f2f2f2;
            font-weight: bold; /* Texto en negrita */
        }

        /* Estilos para resaltar el título */
        .main p {
            font-size: 24px; /* Tamaño grande de fuente */
            font-weight: bold; /* Texto en negrita */
            margin-bottom: 10px; /* Espacio inferior */
        }
        #datos_contrato {
        border-collapse: collapse;
        width: 100%;
        }

        #datos_contrato th, #datos_contrato td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            font-size: 12px; /* Tamaño de fuente más pequeño */
        }

        #datos_contrato th {
            background-color: #f2f2f2; /* Color de fondo para las cabeceras */
        }
        #orden {
        width: 100%;
        border-collapse: collapse;
    }

    #orden th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    #orden ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }

    #orden li {
        padding: 5px 0;
    }

    #orden li strong {
        font-weight: bold;
    }

    /* Estilo específico para los datos de servicio 2 */
    #datos_servicio2 {
        padding-left: 20px;
    }

    /* Estilo específico para los datos de servicio 3 */
    #datos_servicio3 {
        padding-left: 20px;
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
        <h2>ESTADO DE CUENTA - Ctto Nº: <?php echo $id; ?></h2>
        <p style="font-size: 12px;">Fecha: <?php echo date("d-m-Y"); ?></p>

    <table id="orden">
    <tr>
        <th><h3>Detalles del Contrato:</h3>
            <ul id="datos_contrato">
                <?php            
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            echo "<li>";
                            // Mostrar los datos en cada elemento de lista
                                echo "<strong>Nº Contrato :</strong> " . $id . "<br>";
                                echo "<li><strong>Fecha Contrato :</strong> " . $fila['datestart'] . "<br>";
                                echo "<li><strong>Modalidad :</strong>" . $fila['prefix'] . "<br>";
                                echo "<li><strong>Moneda :</strong>    BOLIVIANOS <br>";
                                echo "<li><strong>Promocion :</strong>" . $fila['discount_type'] . "<br>";
                            echo "</li>";
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
        <th><h3>Detalles del Cliente:</h3>
            <ul id="datos_cliente">
                <?php
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            echo "<li>";
                            // Mostrar los datos en cada elemento de lista
                                echo "<strong>Nombre del Cliente :</strong> " . $fila['company'] . "<br>";
                                echo "<li><strong>Carnet de Identidad :</strong> " . $fila['vat'] . "<br>";
                                echo "<li><strong>Dirección Domicilio :</strong> " . $fila['address'] . "<br>";
                                echo "<li><strong>Dirección Laboral :</strong> " . $fila['vat'] . "<br>";
                                echo "<li><strong>Telefono / Celular :</strong> " . $fila['phonenumber'] . "<br>";
                                echo "<li><strong>eMail :</strong> " . $fila['email'] . "<br>";
                            echo "</li>";
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
    </tr>
</table>
<h3>Detalles del Asesor:</h3>
<table id="orden">
    <tr>
        <th>
            <ul id="datos_asesor">
                <?php
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            echo "<li>";
                            // Mostrar los datos en cada elemento de lista
                                echo "<strong>Nombre del Asesor Comercial :</strong> " . $fila['staff_name'] . "<br>";
                                echo "<li><strong>Codigo del Asesor :</strong> " . $fila['staff_code'] . "<br>";
                                echo "<li><strong>Cobrador :</strong>    OFICINA <br>";
                            echo "</li>";
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
    </tr>
</table>

<h3>Detalles del Servicio:</h3>
    <table id="orden">
    <tr>
        <th>
            <ul id="datos_servicio">
                <?php
                    function splitTexts($inputString) {
                        // Dividir la cadena utilizando "/" como delimitador
                        $parts = explode('/', $inputString);
                        
                        // Verificar si hay al menos una división
                        if (count($parts) >= 2) {
                            // Retornar solo las dos primeras opciones
                            return array_slice($parts, 0, 2);
                        } else {
                            // Si no hay al menos dos partes, retornar la primera parte y una cadena vacía
                            return array($parts[0], '');
                        }
                    }
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            $parts = splitTexts($fila['description']);
                            echo "<li>";
                            // Mostrar los datos en cada elemento de lista
                                echo "<strong>Paquete Funerario :</strong> " . $parts[0] . "<br>";
                                echo "<li><strong>Valor de Contrato :</strong> " . $fila['contract_value'] . "<br>";
                                echo "<li><strong>Descuento :</strong> " . $fila['discount_percent'] . "<br>";
                                echo "<li><strong>Precio Convenido :</strong> " . $fila['convenid_price'] . "<br>";
                                echo "<li><strong>Plazo en Meses :</strong> " . $fila['payment_records_count'] . "<br>";
                            echo "</li>";
                            $saldo = $fila['convenid_price'];
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
        <th>
            <ul id="datos_servicio2">
                <?php
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            echo "<li>";
                            // Mostrar los datos en cada elemento de lista
                                echo "<strong>Cuota Inicial:</strong> " . $fila['initial_pay'] . "<br>";
                                echo "<li><strong>Interés:</strong> 0% <br>";
                                echo "<li><strong>Valor Pagado:</strong> " . $fila['SUM(pd.paid_amount)'] . "<br>";
                                echo "<li><strong>Saldo por Pagar:</strong> " . $fila['convenid_price']-$fila['SUM(pd.paid_amount)']. "<br>";
                                echo "<li><strong>Mora:</strong> 0.00 <br>";
                            echo "</li>";
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
    </tr>
</table>
<table id="orden">
    <tr>
        <th>
            <ul id="datos_servicio3">
                <?php
                    // Obtener los resultados de la consulta
                    $resultado_contract = mysqli_query($conn, $query_contract);
                    
                    // Verificar si hay resultados
                    if (mysqli_num_rows($resultado_contract) > 0) {
                        // Iterar sobre cada fila de resultados
                        while ($fila = mysqli_fetch_assoc($resultado_contract)) {
                            $parts2 = splitTexts($fila['description']);
                            echo "<li>";
                                echo "<strong>Descripción:</strong> " . $parts2[1] . "<br>";
                            echo "</li>";
                        }
                    } else {
                        // Si no hay resultados, mostrar un mensaje
                        echo "<li>No se encontraron datos.</li>";
                    }

                    // Liberar el conjunto de resultados
                    mysqli_free_result($resultado_contract);
                ?>
            </ul>
        </th>
    </tr>
    <table id="datos">
    <tr>
        <th>Nº Cuota</th>
        <th>Día de Cobro</th>
        <th>Capital</th>
        <th>Interes</th>
        <th>Valor de Cuota</th>
        <th>% Mora</th>
        <th>Fecha Pagado</th>
        <th>Comprobante de pago</th>
        <th>Método de Pago</th> 
        <th>Monto Pagado</th>
        <th>Saldo Cuota</th> 
        <th>Saldo Contrato</th>                                  
        <th>Estado</th>
        <th>Acción</th>
    </tr>
    <?php
        $num = 0;
        $total_valor_cuota = 0;
        $total_monto_pagado = 0;
        $saldo_cuota = '-';
        
        // Generar filas de tabla con datos de la consulta
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $saldo = $saldo - $fila['paid_mount'];       
            if($fila['paid_mount'] > 0){
                $saldo_cuota = $fila['amount'] - $fila['paid_mount'];
            }
            else{
                $saldo_cuota = '-';
            }
            echo "<tr>";
            echo "<td>" . $fila['num_record'] . "</td>";
            echo "<td>" . $fila['date'] . "</td>";
            echo "<td>" . $fila['paid_mount'] . "</td>";
            echo "<td>" . 0 . "</td>";
            echo "<td>" . $fila['amount'] . "</td>"; 
            echo "<td>" . 0 . "</td>"; 
            echo "<td>" . $fila['pay_date'] . "</td>";  
            echo "<td>" . $fila['proof'] . "</td>";    
            echo "<td>" . $fila['payment_method'] . "</td>";
            echo "<td>" . $fila['paid_mount'] . "</td>";  
            echo "<td>" . $saldo_cuota . "</td>";          
            echo "<td>" . $saldo . "</td>";                  
            echo "<td>" . $fila['note'] . "</td>";
            if ($fila['note'] == 'pagado') {
                // Cambiar estilo y desactivar el enlace
                echo "<td><a class='payment-button paid' style='pointer-events: none; background-color: #ccc;'>Pagar</a></td>";
            } else {
                // Botón de pago normal
                echo "<td><a class='payment-button' href='pagoEdit.php?id=" . $fila['id'] . "&saldo=" . $saldo . "'>Pagar</a></td>";

            }
            echo "</tr>";
            
            // Sumar los valores de "Valor de Cuota" y "Monto Pagado"
            $total_valor_cuota += $fila['amount'];
            $total_monto_pagado += $fila['paid_mount'];
        }
        
        // Imprimir la fila de totales
        echo "<tr>";
        echo "<td colspan='4'>Total:</td>";
        echo "<td>" . $total_valor_cuota . "</td>";
        echo "<td colspan='4'></td>";
        echo "<td>" . $total_monto_pagado . "</td>";
        echo "<td colspan='5'></td>"; // Colspan para ocupar el espacio de las columnas restantes
        echo "</tr>";
        
        // Liberar el conjunto de resultados
        mysqli_free_result($resultado);
        // Cerrar la conexión
        mysqli_close($conn);
    ?>
</table>

        <button id="generarPDF" onclick="generarPDF()">Generar PDF</button>

        <script>
            function generarPDF() {
                var tabla = document.getElementById('datos');
                var columnas = tabla.getElementsByTagName('th');
                for (var i = 0; i < columnas.length; i++) {
                    if (columnas[i].innerText === 'Acción') {
                        columnas[i].style.display = 'none'; // Ocultar el encabezado de la columna
                        var filas = tabla.getElementsByTagName('tr');
                        for (var j = 0; j < filas.length; j++) {
                            var celdas = filas[j].getElementsByTagName('td');
                            // Asegúrate de que haya suficientes celdas antes de intentar ocultarlas
                            if (celdas.length > i) {
                                celdas[i].style.display = 'none'; // Ocultar la celda correspondiente en cada fila
                            }
                        }
                        break; // No es necesario seguir buscando más columnas
                    }
                }
                // Obtener el contenido HTML de todas las tablas
                var tableHTML1 = document.getElementById('datos_contrato').outerHTML;
                var tableHTML2 = document.getElementById('datos_cliente').outerHTML;
                var tableHTML3 = document.getElementById('datos_asesor').outerHTML;
                var tableHTML4 = document.getElementById('datos_servicio').outerHTML;
                var tableHTML5 = document.getElementById('datos_servicio2').outerHTML;
                var tableHTML6 = document.getElementById('datos_servicio3').outerHTML;
                var tableHTML7 = document.getElementById('datos').outerHTML;

                // Crear un formulario dinámico
                var form = document.createElement('form');
                form.setAttribute('method', 'post');
                form.setAttribute('action', 'generar_pdf.php');

                // Crear campos ocultos para cada tabla y agregarlos al formulario
                var input1 = document.createElement('input');
                input1.setAttribute('type', 'hidden');
                input1.setAttribute('name', 'tableHTML1');
                input1.setAttribute('value', tableHTML1);
                form.appendChild(input1);

                var input2 = document.createElement('input');
                input2.setAttribute('type', 'hidden');
                input2.setAttribute('name', 'tableHTML2');
                input2.setAttribute('value', tableHTML2);
                form.appendChild(input2);

                var input3 = document.createElement('input');
                input3.setAttribute('type', 'hidden');
                input3.setAttribute('name', 'tableHTML3');
                input3.setAttribute('value', tableHTML3);
                form.appendChild(input3);

                var input4 = document.createElement('input');
                input4.setAttribute('type', 'hidden');
                input4.setAttribute('name', 'tableHTML4');
                input4.setAttribute('value', tableHTML4);
                form.appendChild(input4);

                var input5 = document.createElement('input');
                input5.setAttribute('type', 'hidden');
                input5.setAttribute('name', 'tableHTML5');
                input5.setAttribute('value', tableHTML5);
                form.appendChild(input5);

                var input6 = document.createElement('input');
                input6.setAttribute('type', 'hidden');
                input6.setAttribute('name', 'tableHTML6');
                input6.setAttribute('value', tableHTML6);
                form.appendChild(input6);

                var input7 = document.createElement('input');
                input7.setAttribute('type', 'hidden');
                input7.setAttribute('name', 'tableHTML7');
                input7.setAttribute('value', tableHTML7);
                form.appendChild(input7);

                // Agregar el formulario al cuerpo del documento y enviarlo
                document.body.appendChild(form);
                form.submit();
            }
        </script>

    </div>
    <script>
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
    </script>
</body>
</html>
<?php
// Llamar a la función de actualización al final de la página principal
$actializar = file_get_contents('http://localhost/asd/pagos/paymentRecordTrigger.php');
?>

