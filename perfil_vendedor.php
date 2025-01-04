<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'chrischarliemotos';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener el email del usuario desde la sesión
$vendedor = $_SESSION['user'];
$email_usuario = $vendedor['email'];

// Consulta SQL para obtener la información del perfil del usuario basado en el email
$sql = "SELECT id_usuario, nombre, tipo_usuario, email, telefono, id_sucursal FROM usuario WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si se encontró el usuario
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Usuario no encontrado.";
    exit;
}

// Cerrar conexión
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Vendedor</title>
    <link rel="stylesheet" href="style_vendedor.css">
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Barra lateral con el menú de categorías -->
        <aside class="sidebar">
            <h3>Menú</h3>
            <ul class="category-list">
                <li><a href="inicio_vendedor.php">Inicio</a></li>
                <li><a href="#">Configuración</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </aside>

        <!-- Área de perfil de usuario -->
        <main class="main-content">
            <div class="header">
                <div class="logo-container">
                    <img src="logo-removebg-preview.png" alt="Logo" class="logo">
                </div>
                <h2>Perfil de <?php echo htmlspecialchars($user['nombre']); ?></h2>
            </div>

            <div class="profile-container">
                <div class="profile-field">
                    <label>ID:</label> <span><?php echo htmlspecialchars($user['id_usuario']); ?></span>
                </div>
                <div class="profile-field">
                    <label>Nombre:</label> <span><?php echo htmlspecialchars($user['nombre']); ?></span>
                </div>
                <div class="profile-field">
                    <label>Tipo de Usuario:</label> <span><?php echo htmlspecialchars($user['tipo_usuario']); ?></span>
                </div>
                <div class="profile-field">
                    <label>Email:</label> <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="profile-field">
                    <label>Teléfono:</label> <span><?php echo htmlspecialchars($user['telefono']); ?></span>
                </div>
                <div class="profile-field">
                    <label>ID Sucursal:</label> <span><?php echo htmlspecialchars($user['id_sucursal']); ?></span>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
