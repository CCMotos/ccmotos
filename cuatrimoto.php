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

// Obtener la marca seleccionada del filtro
$marcaSeleccionada = isset($_GET['marca']) ? $_GET['marca'] : '';

// Consultar marcas disponibles
$marcas = [];
$sqlMarcas = "SELECT DISTINCT marca FROM moto WHERE id_clasificacion = 5";
$resultMarcas = $conn->query($sqlMarcas);

if ($resultMarcas->num_rows > 0) {
    while ($row = $resultMarcas->fetch_assoc()) {
        $marcas[] = $row['marca'];
    }
}

// Consultar motocicletas en la categoría "Motoneta" y por marca si se seleccionó una
$categoria = "Cuatrimoto";
$motos = [];
$sql = "SELECT id_moto, marca, modelo, anio, precio FROM moto 
        INNER JOIN clasificacion_moto ON moto.id_clasificacion = clasificacion_moto.id_clasificacion 
        WHERE clasificacion_moto.nombre_clasificacion = ? AND moto.id_clasificacion = 5";
if ($marcaSeleccionada) {
    $sql .= " AND moto.marca = ?";
}
$stmt = $conn->prepare($sql);
if ($marcaSeleccionada) {
    $stmt->bind_param("ss", $categoria, $marcaSeleccionada);
} else {
    $stmt->bind_param("s", $categoria);
}
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
        <a href="inicio_vendedor.php"><img src="inicio_icon.png" alt="Inicio" width="40" height="40"></a>
        <hr>
            <h3>Más Categorías:</h3>
            <ul class="category-list">
                <li><a href="motoneta.php">Motoneta</a></li>
                <li><a href="motocicleta.php">Motocicleta</a></li>
                <li><a href="trimoto.php">Trimoto</a></li>
                <li><a href="electricas.php">Eléctricas</a></li>
            </ul>
            
            <form method="GET" action="">
                <hr>
                <h3>¿Alguna marca...?</h3>
                <select name="marca" id="marca" onchange="this.form.submit()">
                    <option value="">Todas</option>
                    <?php foreach ($marcas as $marca): ?>
                        <option value="<?php echo htmlspecialchars($marca); ?>" <?php if ($marcaSeleccionada == $marca) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($marca); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <div class="main-content">
        <h2>Motocicletas en la categoría: <?php echo htmlspecialchars($categoria); ?></h2>
            <div class="profile-cart-container">
                <div class="cart">
                    <img src="carrito.png" width="20" height="20">
                    <a href="carrito.php">Carrito</a>
                </div>
                <div class="logout">
                    <img src="cerrarsesion.png" width="20" height="20">
                    <a href="logout.php">Cerrar sesión</a>
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
                                     $imagePath = "img_product/" . htmlspecialchars($moto['id_moto']) . ".jpeg";
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