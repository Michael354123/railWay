<!DOCTYPE html>
<html>
<head>
    <title>CREACIÓN DE PLAN DE PAGOS</title>
    <meta charset="UTF-8">
    <!-- Agregar la fuente Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif; /* Aplicar la fuente Montserrat */
            margin: 0;
            padding: 0;
        }
        /* Estilos para la barra lateral */
        .sidenav {
            height: 100vh;
            width: 250px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: -250px; /* Mover la barra lateral fuera del área visible */
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
        .sidenav a.active {
            background-color: #000;
            color: white;
        }
        .sidenav a:hover {
            background-color: #555;
        }
        /* Estilos para el contenido principal */
        .main {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.5s;
        }
        /* Botón para abrir/cerrar la barra lateral */
        .openbtn {
            font-size: 20px;
            cursor: pointer;
            background-color: #333;
            color: white;
            border: none;
            padding: 10px 15px;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 2;
        }
        .main p {
            font-size: 24px; /* Tamaño grande de fuente */
            font-weight: bold; /* Texto en negrita */
            margin-bottom: 10px; /* Espacio inferior */
        }
            /* Estilos para el formulario de búsqueda */
        .search-form {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            font-size: 18px;
            margin-right: 60px;
        }

        input[type="text"] {
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 200px;
            max-width: 100%;
        }

        /* Estilos para los resultados */
        #resultado,
        #contratos {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        /* Estilo para el contenedor del formulario */
        .payment-form {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 20px;
            border: 1px solid #ccc;
            padding: 20px;
        }

        /* Estilo para los grupos de campos */
        .form-group {
            margin-bottom: 15px;
        }
        .form-group1 {
            margin-bottom: 15px;
        }
        .form-group2 {
            margin-bottom: 15px;
        }

        /* Estilo para los labels */
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Estilo para los inputs */
        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 5px;
            font-family: 'Montserrat', sans-serif;
            border: 1px solid #ccc;
            border-radius: 3px;
            box-sizing: border-box;
        }

        /* Estilo para los botones */
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button[type="reset"] {
            background-color: #dc3545;
            margin-left: 10px;
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
        #orden_h2 {
            width: 100%;
            border-collapse: collapse;
        }
        #contract_IDtext th {
            text-align: right;
        }
        #orden_h2 th {
            text-align: left;
        }
        #orden_h2 th, #orden_h2 td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        #orden_h2 h2 {
            margin: 0;
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
<?php
// Verificar si se ha proporcionado un parámetro de ID en la URL
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Obtener el ID de la URL
    $id = $_GET['id'];

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                mostrarContratos('$id');
            });
          </script>";
    
    ?>
<body>
<?php
        include 'server.php';
        $query = "SELECT name, amount FROM tbldiscount";

        $resultado = mysqli_query($conn, $query);
        
        if (!$resultado) {
            echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            exit();
        }
        
    ?>
    <script>
        function mostrarContratos(subject) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("contratos").innerHTML = this.responseText;
                }
            };
            xmlhttp.open("GET", "searchContractBysubject.php?subject=" + subject, true);
            xmlhttp.send();
        }

        function seleccionarContrato(contractID) {
            var id = document.getElementById('seccond_contract_ID');
            id.value = contractID;
            var contractValueElement = document.getElementById('seccond_contract_Value');

            // Realizar una solicitud AJAX
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'getRefinanciamientoValue.php?id=' + contractID, true);

            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Analizar la respuesta JSON
                    var data = JSON.parse(xhr.responseText);
                    // Actualizar los campos de entrada con los valores recibidos
                    contractValueElement.value = data.total_paid;
                    calcularDatos();

                } else {
                    console.log('Error al realizar la solicitud.');
                }
            };

            xhr.send();
        }
    </script>
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
        <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/">VOLVER AL CRM</a>
    </div>

    <div class="main">      

     <!-- FORMULARIO DE REGISTRO -->
     <table id="orden_h2">
            <tr>
                <th>
                    <h2>FORMULARIO DE REGISTRO</h2>
                </th>
                <th id="contract_IDtext">
                    <label>Nº Contrato:<input type="text" id="contract_ID" name="contract_ID" readonly></label>
                </th>
            </tr>
    </table>
        <!-- CONDICION DE PAGO -->
    <form method="post" action="createFusionContract.php" class="payment-form">
        <!-- 1 -->
        <p>INFORMACION DEL CONTRATO:</p>
            <br>
            <div class="form-group">
                <label for="tipo_servicio">TIPO DE SERVICIO:</label>
                <input type="text" id="tipo_servicio" name="tipo_servicio" value="" readonly>
            </div>

            <div class="form-group">
                <label for="fechaContrato">FECHA DE CONTRATO:</label>
                <input type="text" id="fechaContrato" name="fechaContrato">
            </div>

            <div class="form-group">
                <label for="total_pagar">VALOR DE CONTRATO:</label>
                <input placeholder="Valor Total" type="text" name="valorTotal" id="valorTotal" value="0.00" readonly required>
            </div>
            <div class="form-group">
                <label for="valuePayable">VALOR POR PAGAR:</label>
                <input placeholder="Valor por pagar" type="text" name="valuePayable" id="valuePayable" value="0.00" readonly required>
            </div>
            <input type="text" id="client_id" name="client_id" style="display: none;">
            <input type="text" id="number" name="number" style="display: none;"> 
            <input type="text" id="seccond_contract_ID" name="seccond_contract_ID" style="display: none;"> 
        <!-- 4 -->
        <p>ASIGNACION DE CUOTAS:</p>
        <br/>
            <div class="form-group">
                <div id="contratos"></div>
            </div>
            <div class="form-group">
                <label for="seccond_contract_Value">Monto por pagar:</label>
                <input type="text" id="seccond_contract_Value" name="seccond_contract_Value" readonly>
            </div>
            <div class="form-group">
                <label for="discount_percent">Descuento %:</label>
                <input type="text" id="discount_percent" name="discount_percent" value="0" onkeypress="return descuentoNum(event)" onchange="calcularDatos()">
            </div>
            <div class="form-group">
                <label for="total_discount">Descuento total:</label>
                <input type="text" id="total_discount" name="total_discount" readonly>
            </div>
            <div class="form-group">
                <label for="new_contract_value">Valor total:</label>
                <input type="text" id="new_contract_value" name="new_contract_value" readonly>
            </div>
        <br>
                
        <div class="form-group">
            <button type="submit">REGISTRAR</button>
            <button type="reset">LIMPIAR DATOS</button>
        </div>

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
</script>

<script>

        function descuentoNum(event) {
            var charCode = event.which ? event.which : event.keyCode;

            // Permitir solo números del 1 al 100
            if ((charCode >= 48 && charCode <= 57)) {
                var inputValue = parseInt(event.target.value + String.fromCharCode(charCode));
                if (inputValue >= 0 && inputValue <= 100) {
                    return true;
                }
            }

            return false;
        }

        function calcularDatos() {
            var valorTotal = document.getElementById('valuePayable');
            var seccondContract = document.getElementById('seccond_contract_Value');
            var discountPercent = document.getElementById('discount_percent');
            var totalDiscount = document.getElementById('total_discount');
            var newContract = document.getElementById('new_contract_value');
            if (valorTotal.value > 0 &&
                seccondContract.value > 0) {
                var porcentajeDescuento = parseFloat(discountPercent.value) / 100;
                var valorDescontado = parseFloat(seccondContract.value) * porcentajeDescuento;
                var resultado1 = parseFloat(seccondContract.value) - valorDescontado;

                var totalContract = parseFloat(valorTotal.value) + resultado1;
                totalDiscount.value = valorDescontado;
                newContract.value = totalContract;
            }
            else{
                newContract.value = 0;
            }
        
            
        }

    </script>
<?php
    

    // URL de la página PHP que quieres llamar
    $url = 'http://localhost/asd/pagos/getRefinanciamientoValue.php?id=' . $id;

    // Obtener los datos JSON de la página utilizando file_get_contents
    $json_data = file_get_contents($url);

    // Decodificar los datos JSON a un array asociativo
    $data = json_decode($json_data, true);

    // Verificar si se obtuvieron datos correctamente
    if($data && !isset($data['error'])) {
        // Usar los datos recibidos
        $contract_value = $data['contract_value'];
        $tipo_servicio = $data['tipo_servicio'];
        $fecha = $data['fecha'];
        $total_paid = $data['total_paid'];

        echo "<script>document.getElementById('contract_ID').value = '" . $id  . "';</script>";
        echo "<script>document.getElementById('number').value = '" . $id  . "';</script>";
        echo "<script>document.getElementById('valorTotal').value = '" . $contract_value  . "';</script>";
        echo "<script>document.getElementById('tipo_servicio').value = '" . $tipo_servicio  . "';</script>";
        echo "<script>document.getElementById('fechaContrato').value = '" . $fecha  . "';</script>";
        echo "<script>document.getElementById('valuePayable').value = '" . $total_paid  . "';</script>";
        
        // Continúa con el procesamiento de los datos según sea necesario
    } else {
        // Manejar errores si no se pudieron obtener los datos
        echo "Error: No se pudieron obtener los datos del contrato.";
    }
} else {
    // Si no se proporcionó un ID en la URL, mostrar un mensaje de error
    echo "No se ha proporcionado un ID válido.";
    exit();
}
?>
</body>
</html>