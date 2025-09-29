<?php
    // Verificar si se ha proporcionado un parámetro de ID en la URL
    if(isset($_GET['id']) && !empty($_GET['id'])) {
        // Obtener el ID de la URL
        $id = $_GET['id'];
        $saldo = $_GET['saldo'];

        // Tu conexión y consulta SQL aquí
        include 'server.php';
        
        // Consulta SQL utilizando el ID proporcionado en la URL
        $query = "SELECT ip.id, i.number, CONCAT(i.prefix, LPAD(i.number, 6, '0')) AS invoice_number, ip.amount
                    FROM tblinvoicepaymentrecords ip
                    INNER JOIN tblinvoices i ON ip.invoiceid = i.number
                    WHERE ip.id = $id";

        $resultado = mysqli_query($conn, $query);

        $query2 = "SELECT IFNULL(SUM(paid_amount), 0) as 'total'
                    FROM tblpaymentrecordsDetails 
                    WHERE idpaymentrecord = $id;";
        $resultado2 = mysqli_query($conn, $query2);

        if (!$resultado) {
            if (!$resultado2) {
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
                exit();
            }
        }

        // Obtener los datos de la consulta
        $fila = mysqli_fetch_assoc($resultado);
        $fila2 = mysqli_fetch_assoc($resultado2);
        $invoice_number = $fila['invoice_number'];
        $amount = $fila['amount'];
        $invoiceid = $fila['number'];
        $total= $fila2['total'];   
        $paymenRecordID = $id;
        $total_to_paid = $amount - $total;

    } else {
        // Si no se proporcionó un ID en la URL, mostrar un mensaje de error
        echo "No se ha proporcionado un ID válido.";
        exit();
    }
?>
<script>
        function searchStaff(str) {
            if (str.length == 0) { 
                document.getElementById("searchResult").innerHTML = "";
                return;
            } else {
                var xmlhttp = new XMLHttpRequest();
                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        document.getElementById("searchResult").innerHTML = this.responseText;
                    }
                };
                xmlhttp.open("GET", "search_staff.php?q=" + str, true);
                xmlhttp.send();
            }
        }
    </script>
<!DOCTYPE html>
<html>
<head>
<title>REGISTRO DE PAGOS</title>
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
    .main {
        width: 50%;
        margin: auto;
    }

    .form-group {
        margin-bottom: 15px;
    }

    label {
        display: block;
        margin-bottom: 5px;
    }

    input[type="text"],
    input[type="date"] {
        width: 100%;
        padding: 5px;
        font-family: 'Montserrat', sans-serif;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    input[type="radio"] {
        margin-right: 5px;
    }

    button {
        padding: 5px 10px;
        background-color: #828635;
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #6c722b;
    }

    .block {
        float: left;
        width: 50%;
        margin-right: 10px; /* Espacio entre bloques */
    }

    .clear {
        clear: both;
    }
        /* Estilo para el input */
        #staff_name {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin-bottom: 10px;
    }

    /* Estilo para el div de resultados */
    #searchResult {
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 5px;
        background-color: #ffff;
        max-height: 100px;
        overflow-y: auto;
    }

    /* Estilo para los resultados dentro del div */
    #searchResult p {
        margin: 5px 0;
        cursor: pointer;
    }

    /* Estilo para el texto resaltado en los resultados */
    #searchResult .highlight {
        background-color: yellow;
    }
    /* Estilo para los botones */
    button[type="submit"],
    button[type="reset"] {
        padding: 8px 16px;
        margin-right: 10px; /* Separación entre botones */
        border: none;
        border-radius: 4px;
        background-color: #007bff; /* Color de fondo */
        color: #fff; /* Color del texto */
        font-size: 14px;
        cursor: pointer;
    }

    /* Estilo cuando se pasa el mouse sobre los botones */
    button[type="submit"]:hover,
    button[type="reset"]:hover {
        background-color: #0056b3; /* Color de fondo al pasar el mouse */
    }

    /* Estilo para el botón "Limpiar" */
    button[type="reset"] {
        background-color: #dc3545; /* Color de fondo para el botón "Limpiar" */
    }

    /* Estilo cuando se pasa el mouse sobre el botón "Limpiar" */
    button[type="reset"]:hover {
        background-color: #c82333; /* Color de fondo al pasar el mouse sobre el botón "Limpiar" */
    }
    /* Estilo para el select */
    select {
            padding: 8px;
            font-size: 16px;
            font-family: 'Montserrat', sans-serif;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        /* Estilo para los option */
        option {
            font-size: 16px;
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
    <a href="invoice.php" class="active">PLANES DE PAGO</a>
    <a href="historialPagos.php">HISTORIAL DE PAGOS</a>
    <a href="#" onclick="closeNav()">REPORTES</a>
    <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/" >VOLVER AL CRM</a>
</div>


<div class="main">
    <h2>REALIZAR PAGOS</h2>
    <br>

    <form method="post" action="createPay.php">
            <label for="invoice_number">Nº CONTRATO :</label>
            <input type="text" id="invoice_number" name="invoice_number" value="<?php echo $invoice_number; ?>" readonly>
            <br><br><br>
            <table id="orden">
            <tr>
                <th>
                    <label for="amount">MONTO A COBRAR :</label>
                    <input type="text" id="amount" name="amount" value="<?php echo $amount; ?>" readonly>
                    <input type="text" id="paymenRecordID" name="paymenRecordID" value="<?php echo $paymenRecordID; ?>" readonly style="display: none">

                    <input type="text" id="idpaymentRecord" name="idpaymentRecord" value="<?php echo $id; ?>" style="display:none">
                    <br>
                    <input type="text" id="invoiceid" name="invoiceid" value="<?php echo $invoiceid; ?>" style="display:none">
                    <br>
                    <div class="form-group">
                        <label for="pago">MONTO A PAGAR :</label>
                        <input type="text" id="pago" name="pago" onkeypress="return soloNumeros(event)" onkeyup="actualizarSaldo();Saldoalert();">
                    </div>
                    <div class="form-group">
                        <label for="acount">PAGO A CUENTA :</label>
                        <input type="text" id="acount" name="acount" readonly value="<?php echo $total_to_paid; ?>">
                    </div>
                    <div class="form-group">
                        <label for="saldo">PAGO EXEDENTE (AMORTIZACIÓN):</label>
                        <input type="text" id="saldo" name="saldo" value="0.00" readonly>
                    </div>
                    <div class="form-group">
                        <label for="transactionid">Nº COMPROBANTE:</label>
                        <input type="text" id="transactionid" name="transactionid" onkeypress="return soloNumeros(event)">
                    </div>
                </th>
                <th>
                    <div class="form-group">
                    <label for="staff_name">COBRADOR :</label>
                    <input type="text" id="staff_name" name="staff_name" onkeyup="searchStaff(this.value)">
                    <div id="searchResult"></div>
                    </div>

                    <div class="form-group" style="display: flex; align-items: center;">
                        <label for="paymentmethod">METODO DE PAGO:</label>
                        <div>
                            <input type="radio" id="qr" name="paymentmethod" value="QR" checked style="display: inline-block;">
                            <label for="QR" style="display: inline-block;">QR</label>
                            <input type="radio" id="efectivo" name="paymentmethod" value="Efectivo" style="display: inline-block;">
                            <label for="Efectivo" style="display: inline-block;">Efectivo</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="daterecorded">FECHA DE PAGO:</label>
                        <input type="date" id="daterecorded" name="daterecorded">
                    </div>
                    <div class="form-group">
                        <label for="detail">OBSERVACIONES:</label>
                        <input type="text" id="detail" name="detail">
                    </div>
                </th>
            </tr>
            </table>
            <div class="form-group">
                <button type="submit">REGISTRAR PAGO</button>
                
                <button type="reset">LIMPIAR DATOS</button>
            </div>
        </form>
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

        function soloNumeros(event) {
            var charCode = event.which ? event.which : event.keyCode;

            // Permitir solo números y el punto decimal
            if ((charCode >= 48 && charCode <= 57) || charCode === 46) {
                // Si ya hay un punto decimal en el valor, no permitir otro
                if (charCode === 46 && event.target.value.indexOf('.') !== -1) {
                    return false;
                }
                // Si el punto decimal está al final, agregar ".00"
                if (charCode === 46 && event.target.value.indexOf('.') === event.target.value.length - 1) {
                    event.target.value += '00';
                }
                // Permitir solo dos decimales después del punto
                if (event.target.value.indexOf('.') !== -1 && event.target.value.split('.')[1].length >= 2) {
                    return false;
                }
                return true;
            }

            return false;
        }

        function actualizarSaldo() {
            var pago = parseFloat(document.getElementById("pago").value);
            var total_to_paid = <?php echo $total_to_paid; ?>;
            var saldo = 0;
            var rest = 0;
            if(pago > 0){
                rest = total_to_paid - pago;
                if(rest < 0){
                    rest = 0;
                }
                saldo = total_to_paid - pago;
                if(saldo > 0){
                    saldo = 0;
                }   
                else 
                    saldo = saldo*-1;
                }
            else{
                rest = total_to_paid;
            }             
            
            document.getElementById("saldo").value = saldo.toFixed(2);
            document.getElementById("acount").value = rest.toFixed(2);
        }

        function Saldoalert() {
            var pagoInput = document.getElementById('pago');
            var saldo = parseFloat(<?php echo $saldo; ?>); // Obtener el saldo como número decimal desde PHP
            
            if (pagoInput.value.trim() !== '') {
                var pago = parseFloat(pagoInput.value);
                
                if (pago > saldo) {
                    // Mostrar una alerta si el monto de pago es mayor que el saldo
                    alert('El monto de pago no puede ser mayor que el saldo actual.');
                    // Restaurar el valor del campo de pago
                    pagoInput.value = saldo.toFixed(2); // Establecer el valor máximo como el saldo actual
                }
            }
        }
    </script>
</body>
</html>
