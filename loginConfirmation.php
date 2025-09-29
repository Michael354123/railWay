<?php
session_start(); // Iniciar la sesión para manejar variables de sesión

// Función para generar un hash seguro usando un algoritmo personalizado y un salt aleatorio
function generate_custom_hash($password) {
    $salt = bin2hex(random_bytes(16)); // Generar un salt aleatorio
    $hash = hash('sha256', $salt . $password); // Aplicar hashing usando SHA-256 y salt
    
    return $salt . $hash; // Concatenar salt y hash para almacenar en la base de datos
}

// Función para verificar una contraseña usando el método de hashing personalizado
function verify_custom_hash($password, $stored_hash) {
    $salt = substr($stored_hash, 0, 32); // Obtener el salt del hash almacenado
    $stored_hash_without_salt = substr($stored_hash, 32); // Obtener el hash sin salt
    
    // Generar el hash usando el mismo salt y contraseña proporcionada
    $input_hash = hash('sha256', $salt . $password);
    
    // Comparar el hash generado con el hash almacenado (sin salt)
    return $input_hash === $stored_hash_without_salt;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se han enviado los datos del formulario
    if (isset($_POST['email']) && isset($_POST['password'])) {
        // Obtener los datos del formulario
        $email = $_POST['email'];
        $password = $_POST['password'];

        // Incluir el archivo de conexión a la base de datos
        include 'server.php';

        // Preparar la consulta SQL utilizando parámetros preparados (prepared statement) para evitar SQL injection
        $query = "SELECT * FROM tblstaff WHERE email = ?";
        
        // Preparar la sentencia
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            // Asociar parámetros a la sentencia preparada
            mysqli_stmt_bind_param($stmt, "s", $email);

            // Ejecutar la consulta
            mysqli_stmt_execute($stmt);

            // Obtener el resultado de la consulta
            $resultado = mysqli_stmt_get_result($stmt);

            if ($resultado) {
                // Verificar si se encontró un usuario con el correo electrónico proporcionado
                if (mysqli_num_rows($resultado) > 0) {
                    $fila = mysqli_fetch_assoc($resultado);
                    $hashed_password = $fila['acceso_pagos']; // Obtener el hash de la contraseña almacenada en la base de datos

                    // Verificar la contraseña utilizando tu función de hashing personalizado
                    if (verify_custom_hash($password, $hashed_password)) {
                        // Inicio de sesión exitoso
                        $_SESSION['user_id'] = $fila['staffid']; // Almacenar el ID del usuario en la sesión
                        $_SESSION['user_email'] = $fila['email']; // Almacenar el correo electrónico del usuario en la sesión

                        // Redirigir a una página de inicio o dashboard después del inicio de sesión
                        header("Location: index.php");
                        exit();
                    } else {
                        // Contraseña incorrecta
                        echo "Contraseña incorrecta. Por favor, intenta de nuevo.";
                    }
                } else {
                    // Usuario no encontrado con el correo electrónico proporcionado
                    echo "Usuario no encontrado. Por favor, verifica tu correo electrónico.";
                }
            } else {
                // Error al obtener el resultado de la consulta
                echo "Error al ejecutar la consulta: " . mysqli_error($conn);
            }

            // Liberar el resultado de la consulta preparada
            mysqli_stmt_close($stmt);
        } else {
            // Error al preparar la consulta
            echo "Error al preparar la consulta: " . mysqli_error($conn);
        }

        // Cerrar la conexión a la base de datos
        mysqli_close($conn);
    } else {
        // No se recibieron los datos esperados del formulario
        echo "Por favor, completa todos los campos del formulario.";
    }
}
?>
