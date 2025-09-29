<?php
// Datos de conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "ssm354123";
$dbname = "perfexcrm";

// Crear una conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
