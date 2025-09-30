<?php
// Iniciar sesión
echo "🚀 Railway funciona en el puerto: " . getenv("PORT");


/*// Verificar si se ha proporcionado un parámetro de ID en la URL
if(isset($_GET['id']) && !empty($_GET['id'])) {
    // Obtener el ID de la URL
    $id = $_GET['id'];

    include 'server.php';
    // Consulta SQL utilizando el ID proporcionado en la URL
    $query = "SELECT staffid, acceso_pagos FROM tblstaff WHERE staffid = $id";

    $resultado = mysqli_query($conn, $query);

    if (!$resultado) {
        echo "Error al ejecutar la consulta: " . mysqli_error($conn);
        exit();
    }

    // Verificar si se encontraron filas
    if (mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $acceso = $fila['acceso_pagos'];

        // Verificar si el acceso es igual a 1
        if ($acceso == 1) {
            // Guardar el ID en una variable de sesión
            $_SESSION['user_id'] = $id;
        }
    }
} else {
    // No se proporcionó un ID en la URL, redirigir al usuario a la página anterior
    echo '<script>window.history.go(-1);</script>';
    exit();
}*/
?>

<!DOCTYPE html>
<html>
<head>
<title>GESTION DE ATC</title>
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
        .sidenav a:hover {
            background-color: #555;
        }
        .main {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.5s;
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
<button class="openbtn" onclick="toggleNav()">☰</button>

<div class="sidenav" id="mySidenav">
    <!-- Lista de enlaces para la navegación -->
    <a href="#"></a>
    <a href="index.php" class="active">INICIO</a>
    <a href="clients.php">CLIENTES y CONTRATOS</a>
    <a href="invoice.php">PLANES DE PAGO</a>
    <a href="historialPagos.php">HISTORIAL DE PAGOS</a>
    <a href="#" onclick="closeNav()">REPORTES</a>
    <a href="https://www.jesucristoserviciosfunerarios.com/legacy/admin/">VOLVER AL CRM</a>
</div>

<div class="main">
    <!-- Contenido principal de la página -->
    <h2>PAGINA PRINCIPAL</h2>
    <p>GESTION DE COBRANZAS</p>
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

    // Verificar acceso
    var acceso = <?php echo json_encode($acceso) ?>;
    if (parseInt(acceso) !== 1) {
        // Mostrar mensaje de alerta
        alert("Acceso denegado. No tienes permisos para acceder a esta página.");

        // Retroceder a la página anterior
        history.go(-1); // Esta línea redireccionará al usuario a la página anterior
    }
</script>

</body>
</html>
