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

// Obtener información del vendedor desde la sesión
$vendedor = $_SESSION['user'];
$id_sucursal = $vendedor['id_sucursal'];

// Verificar si se ha enviado una moto para agregar al carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_moto'])) {
    $id_moto = intval($_POST['id_moto']);

    $sqlInventario = "SELECT cantidad_disponible FROM inventario WHERE id_moto = ? AND id_sucursal = ?";
    $stmt = $conn->prepare($sqlInventario);
    $stmt->bind_param("ii", $id_moto, $id_sucursal);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $inventario = $result->fetch_assoc();
        if ($inventario['cantidad_disponible'] > 0) {
            $_SESSION['carrito'][] = $id_moto;
            $mensaje = "La moto ha sido agregada al carrito.";
        } else {
            $mensaje = "No hay suficiente inventario de esta moto en tu sucursal.";
        }
    } else {
        $mensaje = "Esta moto no está disponible en tu sucursal.";
    }
    $stmt->close();
}

// Consulta para obtener los detalles de las motos en el carrito
$carrito_motos = [];
$total_costo = 0;
if (isset($_SESSION['carrito']) && !empty($_SESSION['carrito'])) {
    $ids_motos = implode(",", array_map('intval', $_SESSION['carrito']));
    $sqlMotos = "SELECT id_moto, marca, modelo, anio, precio FROM moto WHERE id_moto IN ($ids_motos)";
    $resultMotos = $conn->query($sqlMotos);

    while ($moto = $resultMotos->fetch_assoc()) {
        $carrito_motos[] = $moto;
        $total_costo += $moto['precio'];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="style_vendedor.css">
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <h3>Menú</h3>
            <ul class="category-list">
                <li><a href="inicio_vendedor.php">Inicio</a></li>
                <li><a href="perfil_vendedor.php">Perfil</a></li>
                <li><a href="logout.php">Cerrar sesión</a></li>
            </ul>
        </aside>
        <main class="main-content">
            <div class="header">
                <div class="logo-container">
                    <img src="logo-removebg-preview.png" alt="Logo" class="logo">
                </div>
                <h2>Carrito de Compras</h2>
            </div>

            <?php if (isset($mensaje)): ?>
                <div class="message"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <div class="cart-container">
                <?php if (count($carrito_motos) > 0): ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>SKU</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Año</th>
                                <th>Precio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($carrito_motos as $moto): ?>
                                <tr>
                                    <td>
                                        <?php
                                        $imagePath = htmlspecialchars($moto['id_moto']) . ".jpeg";
                                        if (file_exists($imagePath)): ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Imagen de <?php echo htmlspecialchars($moto['marca']); ?>" class="product-image">
                                        <?php else: ?>
                                            <img src="default14.jpeg" alt="Imagen no disponible" class="product-image">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($moto['id_moto']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['marca']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['modelo']); ?></td>
                                    <td><?php echo htmlspecialchars($moto['anio']); ?></td>
                                    <td>$<?php echo number_format($moto['precio'], 2); ?> MXN</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="cart-summary">
                        <h3>Resumen de costos</h3>
                        <div class="summary-details">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($total_costo, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Envío:</span>
                                <span>$0.00</span>
                            </div>
                            <div class="summary-row total">
                                <strong>Total a pagar:</strong>
                                <strong>$<?php echo number_format($total_costo, 2); ?></strong>
                            </div>
                        </div>
                        <a href="datos_cliente.php" class="checkout-btn">Continuar</a>
                    </div>
                <?php else: ?>
                    <p>No tienes artículos en el carrito.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

