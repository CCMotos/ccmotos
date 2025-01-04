<?php
// Conexión a la base de datos
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'chrischarliemotos';
$conn = new mysqli($host, $user, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Registrar cliente y mostrar mensaje
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_cliente = $_POST['nombre_cliente'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];

    // Verificar si el teléfono o el email ya existen
    $check_sql = "SELECT * FROM cliente WHERE telefono='$telefono' OR email='$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        $message = "Error: El número de teléfono o el email ya están registrados.";
    } else {
        $sql = "INSERT INTO cliente (nombre_cliente, telefono, email, direccion) VALUES ('$nombre_cliente', '$telefono', '$email', '$direccion')";
        if ($conn->query($sql) === FALSE) {
            $message = "Error al registrar cliente: " . $conn->error;
        } else {
            $message = "Cliente registrado exitosamente";
        }
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Clientes</title>
    <link rel="stylesheet" href="style_registrarCliente.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Barra lateral de categorías -->
        <div class="sidebar">
            <h3>Registro de cliente</h3>
            <ul class="category-list">
                <li><a href="inicio_vendedor.php"><-- Regresar</a></li>
                <li><a href="#"></a></li>
                <li><a href="#"></a></li>
            </ul>
        </div>
        <!-- Área principal -->
        <div class="main-content">
            <div class="header">
                <div class="logo-container">
                    <img src="logo.jpeg" alt="Logo" class="logo">
                </div>
                <div class="profile-cart-container">
                    <div class="profile"><a href="perfil_vendedor.php">Perfil</a></div>
                    <div class="logout"><a href="#">Cerrar Sesión</a></div>
                </div>
            </div>
            <h2>Formulario</h2>
            <h5>Por favor capture bien los datos de acuerdo a la identificación del cliente.</h5>
            <?php if (!empty($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form method="post" action="">
                <div>
                    <label for="nombre_cliente">Nombre del Cliente:</label>
                    <input type="text" id="nombre_cliente" name="nombre_cliente" required>
                </div>
                <div>
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>

                <div>
                    <button type="submit" class="add-to-cart">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

