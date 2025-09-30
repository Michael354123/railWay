<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

echo "🚀 Apache + PHP en Railway funciona!<br>";

// Comprobar variables de entorno de Railway
echo "PORT: " . getenv("PORT") . "<br>";
echo "DB_HOST: " . getenv("DB_HOST") . "<br>";
echo "DB_USER: " . getenv("DB_USER") . "<br>";
echo "DB_NAME: " . getenv("DB_NAME") . "<br>";
echo "DB_PORT: " . getenv("DB_PORT") . "<br>";

// Intentar conexión MySQL (opcional)
$conn = new mysqli(
    getenv("DB_HOST"),
    getenv("DB_USER"),
    getenv("DB_PASS"),
    getenv("DB_NAME"),
    getenv("DB_PORT")
);

if ($conn->connect_error) {
    die("❌ Error MySQL: " . $conn->connect_error);
}

echo "✅ Conexión MySQL exitosa";
