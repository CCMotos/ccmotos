<?php
session_start();

// Verificar si el vendedor está logueado
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

// Consultar motocicletas en la categoría "Motocicleta"
$categoria = "Motoneta";
$motos = [];
$sql = "SELECT id_moto, marca, modelo, anio, precio FROM moto 
        INNER JOIN clasificacion_moto ON moto.id_clasificacion = clasificacion_moto.id_clasificacion 
        WHERE clasificacion_moto.nombre_clasificacion = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $categoria);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $motos[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categoría: <?php echo htmlspecialchars($categoria); ?></title>
    <link rel="stylesheet" href="style_motocicleta.css">
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h3>Categorías</h3>
            <ul class="category-list">
                <li><a href="#">Motonetas</a></li>
                <li><a href="#">Deportivas</a></li>
                <li><a href="#">Choppers</a></li>
                <li><a href="#">Scooters</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="header">
                <h2>Motocicletas en la categoría: <?php echo htmlspecialchars($categoria); ?></h2>
                <div class="profile-cart-container">
                    <div class="profile"><a href="perfil_vendedor.php">Perfil</a></div>
                    <div class="cart"><a href="carrito.php">Carrito (0)</a></div>
                    <div class="logout"><a href="logout.php">Cerrar sesión</a></div>
                </div>
            </div>

            <!-- Contenedor de productos con flechas -->
            <?php if (empty($motos)): ?>
                <p>No se encontraron motocicletas en esta categoría.</p>
            <?php else: ?>
                <div class="products-wrapper">
                    <button class="scroll-button left">&lt;</button>
                    <div class="scroll-container">
                        <?php foreach ($motos as $moto): ?>
                            <div class="product-card">
                                <?php 
                                    $imagePath = htmlspecialchars($moto['id_moto']) . ".jpeg";
                                    if (file_exists($imagePath)): ?>
                                        <img src="<?php echo $imagePath; ?>" alt="Imagen de <?php echo htmlspecialchars($moto['marca']); ?>" class="product-image">
                                <?php else: ?>
                                        <img src="default1.jpeg" alt="Imagen no disponible" class="product-image">
                                <?php endif; ?>
                                <h3>SKU: <?php echo htmlspecialchars($moto['id_moto']); ?></h3>
                                <h4><?php echo htmlspecialchars($moto['marca']) . ' ' . htmlspecialchars($moto['modelo']) . ' ' . htmlspecialchars($moto['anio']); ?></h4>
                                <p>Precio: $<?php echo number_format($moto['precio'], 2); ?> MXN</p>
                                
                                <!-- Formulario para agregar al carrito -->
                                <form action="carrito.php" method="post">
                                    <input type="hidden" name="id_moto" value="<?php echo htmlspecialchars($moto['id_moto']); ?>">
                                    <button type="submit" class="add-to-cart">Agregar al Carrito</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="scroll-button right">&gt;</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
