
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
<title>HISTORIAL DE PAGOS</title>
    <!-- Agregar la fuente Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <!-- CSS de DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- DataTables -->
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>
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

<?php
    // Tu conexión y consulta SQL aquí
    include 'server.php';

    $query = "SELECT CONCAT(ct.name, LPAD(c.subject, 6, '0')) as num_contract, c.subject
                FROM tblcontracts c
                INNER JOIN tblcontracts_types ct ON ct.id = c.contract_type;";
    
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
        <a href="invoice.php">PLANES DE PAGO</a>
        <a href="historialPagos.php" class="active">HISTORIAL DE PAGOS</a>
        <a href="#" onclick="closeNav()">REPORTES</a>
        <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/" >VOLVER AL CRM</a>
    </div>

    <div class="main">
    <!-- Contenido principal de la página -->
    <h2>HISTORIAL DE PAGOS</h2>
    <br>
    <form id="exportarForm" action="exportar_tabla_excel.php" method="post">
        <input type="hidden" id="tablaHtml" name="tablaHtml" value="">
        <button type="button" onclick="exportarTabla()">Exportar a Excel</button>
    </form>
    <br>

    <!-- Aquí se generará el select con los resultados de la consulta -->
    <select id="contractsSelect" onchange="handleSelectChange(this)">
        <option value="0" selected>Mostrar todos</option>
        <?php
        // Iterar sobre los resultados de la consulta
        while ($fila = mysqli_fetch_assoc($resultado)) {
            // Generar una opción para cada fila de resultados
            echo '<option value="' . $fila['subject'] . '">' . $fila['num_contract'] . '</option>';
        }
        ?>
    </select>

    <!-- Aquí se mostrará la tabla de resultados -->
    <div class="table-responsive" id="resultTable">
    </div>
</div>

<script>
     // Al cargar la página, seleccionar automáticamente el valor con contractId igual a 0
     $(document).ready(function() {
        $('#contractsSelect').val('0');
        // Llamar a la función handleSelectChange con el valor inicial
        handleSelectChange(document.getElementById('contractsSelect'));
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

    // Función para manejar el cambio en el select de contratos
    function handleSelectChange(selectElement) {
        var contractId = selectElement.value;
        if (contractId) {
            $.ajax({
                url: 'obtener_detalles.php', // Ruta al archivo PHP que manejará la solicitud
                method: 'POST',
                data: { contractId: contractId },
                success: function(response) {
                    // Mostrar la tabla de resultados y actualizar su contenido
                    $('#tableResp').DataTable();
                    $('#resultTable').html(response);
                    // Reinicializar DataTables para que funcione con los nuevos datos
                    $(document).ready(function() {
                        $('#tableResp').DataTable();
                    });
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        } else {
            $('#resultTable').hide(); // Ocultar la tabla si no se selecciona ningún contrato
        }
    }

    function exportarTabla(){
        var tablaHtml = document.getElementById("tableResp").innerHTML; // Cambiado de outerHTML a innerHTML
        // Establecer el contenido HTML de la tabla en el input hidden del formulario
        document.getElementById("tablaHtml").value = tablaHtml;
        // Enviar el formulario
        document.getElementById("exportarForm").submit();

    } 
</script>

</body>
</html>
