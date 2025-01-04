<?php
// Conexión a la base de datos
$host = 'localhost';
$user = 'root'; 
$password = ''; 


$dbname = 'chrischarliemotos';

$conn = new mysqli($host, $user, $password, $dbname);

// Verifica si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibe los datos del formulario
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$tipo_usuario = $_POST['tipo_usuario'];
$telefono = $_POST['telefono'];
$id_sucursal = $_POST['id_sucursal'];
$codigo_seguridad = $_POST['codigo_seguridad'];

// Verifica si el correo electrónico o el teléfono ya existen
$sql = "SELECT * FROM usuario WHERE email = ? OR telefono = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $telefono);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Si ya existe un usuario con el mismo email o teléfono
    $error_message = "El correo electrónico o el teléfono ya están registrados.";
} else {
    // Verifica si el código de seguridad es válido para el tipo de usuario
    $sql = "SELECT * FROM cod_seg WHERE codigo = ? AND tipo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $codigo_seguridad, $tipo_usuario);
    $stmt->execute();
    $codigo_result = $stmt->get_result();

    if ($codigo_result->num_rows === 0) {
        // Si el código de seguridad no es válido
        $error_message = "El código de seguridad no es válido para el tipo de usuario seleccionado.";
    } else {
        // Si no existe, inserta el nuevo usuario
        $sql = "INSERT INTO usuario (nombre, tipo_usuario, email, telefono, id_sucursal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $nombre, $tipo_usuario, $email, $telefono, $id_sucursal);

        if ($stmt->execute()) {
            $success_message = "Usuario registrado exitosamente.";
        } else {
            $error_message = "Error al registrar el usuario: " . $conn->error;
        }
    }
}

// Cierra la conexión
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="style_nuevosuarios.css">
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="logo-floating">
        <img src="logo_gif.gif" alt="Logo Animado" class="logo">
    </div>
    
    <div class="login-container">
        <h2>Registro de Usuario</h2>
        
        <!-- Contenedor de mensajes de error y éxito -->
        <div class="message-container">
            <?php if (isset($error_message)) : ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($success_message)) : ?>
                <div class="success-message">
                    <strong>Éxito:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <form action="register.php" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_usuario">Tipo de Usuario</label>
                    <select id="tipo_usuario" name="tipo_usuario" required>
                        <option value="inventariado">Inventariado</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="id_sucursal">ID Sucursal</label>
                    <input type="number" id="id_sucursal" name="id_sucursal" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="codigo_seguridad">Código de Seguridad</label>
                    <input type="text" id="codigo_seguridad" name="codigo_seguridad" required>
                </div>
            </div>

            <button type="submit">Registrar</button>
        </form>
    </div>
</body>
</html>
