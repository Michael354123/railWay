<?php
// Datos de conexión a la base de datos desde variables de entorno
$servername = getenv("DB_HOST");
$username   = getenv("MYSQLUSER");        // Railway suele dar MYSQLUSER
$password   = getenv("MYSQLPASSWORD");    // Railway da MYSQLPASSWORD
$dbname     = getenv("MYSQL_DATABASE");   // Railway da MYSQL_DATABASE
$port       = getenv("MYSQLPORT");        // Railway da MYSQLPORT

// Crear una conexión a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
