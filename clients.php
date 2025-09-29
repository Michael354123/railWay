
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

<title>CLIENTES ATC</title>
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

        $query = "SELECT c.subject, cl.userid, cl.vat, CONCAT(cn.firstname, ' ', cn.lastname) as 'name', cn.phonenumber, cl.company, c.payment_plan_exist
                    FROM tblcontracts c
                    INNER JOIN tblclients cl ON c.client=cl.userid
                    INNER JOIN tblcontacts cn ON cn.userid = cl.userid";
        
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
        <a href="clients.php" class="active">CLIENTES y CONTRATOS</a>
        <a href="invoice.php">PLANES DE PAGO</a>
        <a href="historialPagos.php">HISTORIAL DE PAGOS</a>
        <a href="#" onclick="closeNav()">REPORTES</a>
        <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/" >VOLVER AL CRM</a>
    </div>

    <div class="main">
        <!-- Contenido principal de la página -->
        <h2>CLIENTES Y CONTRATOS</h2>
        <br>
        <form id="exportarForm" action="exportar_tabla_excel.php" method="post">
            <input type="hidden" id="tablaHtml" name="tablaHtml" value="">
            <button type="button" onclick="exportarTabla()">Exportar a Excel</button>
        </form>
        <br>
        <!-- Tabla para mostrar los resultados -->
        <table id="miTabla" class="display">
            <thead>
                <tr>
                    <th>Nro Contrato</th>
                    <th>NIT</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th>Titular</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Generar filas de tabla con datos de la consulta
                while ($fila = mysqli_fetch_assoc($resultado)) {
                    echo "<tr>";
                    echo "<td>" . $fila['subject'] . "</td>";
                    echo "<td>" . $fila['vat'] . "</td>";
                    echo "<td>" . $fila['name'] . "</td>";
                    echo "<td>" . $fila['phonenumber'] . "</td>";
                    echo "<td>" . $fila['company'] . "</td>";
                    // Convertir el número de estado a texto
                    echo "<td>" . convertirEstado($fila['payment_plan_exist']) . "</td>";
                    if(convertirEstado($fila['payment_plan_exist'])=="Con plan"){
                        echo "<td><a class='payment-button' href='pago.php?id=" . $fila['subject'] . "'>Ver Plan</a></td>";
                    }else{
                        echo "<td><a class='payment-button' href='invoiceEdit.php?id=" . $fila['subject']. "&uid=" . $fila['userid'] . "'>Crear Plan</a></td>";
                    }
                    
                    echo "</tr>";
                }

                // Función para convertir el número de estado a texto
                function convertirEstado($estado_num) {
                    switch ($estado_num) {
                        case 1:
                            return "Con plan";
                            break;   
                        default:
                            return "Sin plan";
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

        $(document).ready(function() {
                $('#miTabla').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
                    }
                });
            });

        $(document).ready(function() {
            $('#miTabla').DataTable();
        });

        $(document).ready(function() {
            $('#miTabla').DataTable();
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
    </script>
</body>
</html>
