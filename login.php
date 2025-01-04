<?php
session_start(); // Iniciar sesión para almacenar mensajes

// Datos de conexión a la base de datos
$host = "localhost";
$dbname = "chrischarliemotos";
$username = "root";
$password = "";

try {
    // Crear la conexión
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    // Obtener datos del formulario
    $email = $_POST['email'] ?? null;
    $telefono = $_POST['password'] ?? null; // Aquí 'password' se refiere al teléfono

    // Verificar si el formulario fue enviado
    if ($email && $telefono) {
        // Verificar si el email existe en la base de datos
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE email = ? AND telefono = ?");
        $stmt->bind_param("ss", $email, $telefono);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Obtener los datos del usuario
            $user = $result->fetch_assoc();
            
            // Guardar los datos en la sesión
            $_SESSION['user'] = [
                'id_usuario' => $user['id_usuario'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
                'telefono' => $user['telefono'],
                'id_sucursal' => $user['id_sucursal'],
                'tipo_usuario' => $user['tipo_usuario'] // Añadir tipo de usuario a la sesión
            ];
        
            // Mensaje de éxito
            $_SESSION['success_message'] = "Bienvenido, " . htmlspecialchars($user['nombre']) . "!";
            
            // Limpiar mensaje de error si hubo
            unset($_SESSION['error_message']);
            
            // Redirigir según el tipo de usuario
            if ($user['tipo_usuario'] === 'vendedor') {
                header("Location: inicio_vendedor.php");
            } elseif ($user['tipo_usuario'] === 'inventariado') {
                header("Location: inicio_inventariador.php");
            }
            exit();
        } else {
            $_SESSION['error_message'] = "Usuario o contraseña incorrectos.";
            header("Location: index.php"); // Redirigir al formulario
            exit();
        }

        $stmt->close();
    }

    $conn->close();

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header("Location: index.php"); // Redirigir al formulario en caso de error general
    exit();
}
?>