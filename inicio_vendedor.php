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

// Obtener los datos del vendedor desde la sesión
$vendedor = $_SESSION['user'];

// Consultar las clasificaciones de motocicletas
$sqlClasificaciones = "SELECT nombre_clasificacion FROM clasificacion_moto";
$resultClasificaciones = $conn->query($sqlClasificaciones);

$clasificaciones = [];
if ($resultClasificaciones->num_rows > 0) {
    while ($row = $resultClasificaciones->fetch_assoc()) {
        $clasificaciones[] = $row['nombre_clasificacion'];
    }
}

// Consultar detalles de la motocicleta con id_moto = 7383
$id_moto = 7383;
$sqlMoto = "SELECT id_moto, marca, modelo, anio, precio FROM moto WHERE id_moto = ?";
$stmtMoto = $conn->prepare($sqlMoto);
$stmtMoto->bind_param("i", $id_moto);
$stmtMoto->execute();
$resultMoto = $stmtMoto->get_result();
$moto = $resultMoto->fetch_assoc();

$stmtMoto->close();

// Buscar motocicletas
$searchResults = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchTerm = $_POST['search_term'] ?? '';

    $sqlSearch = "SELECT id_moto, marca, modelo, anio, precio FROM moto WHERE id_moto = ? OR marca LIKE ? OR modelo LIKE ?";
    $stmtSearch = $conn->prepare($sqlSearch);
    $searchTermLike = '%' . $searchTerm . '%';
    $stmtSearch->bind_param('iss', $searchTerm, $searchTermLike, $searchTermLike);

    $stmtSearch->execute();
    $resultSearch = $stmtSearch->get_result();

    while ($row = $resultSearch->fetch_assoc()) {
        $searchResults[] = $row;
    }

    $stmtSearch->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Vendedor</title>
    <link rel="stylesheet" href="style_vendedor.css">
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <script>
        function toggleSearch() {
            var searchForm = document.getElementById('searchForm');
            if (searchForm.style.display === 'none' || searchForm.style.display === '') {
                searchForm.style.display = 'block';
            } else {
                searchForm.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Barra lateral con el menú de categorías -->
        <aside class="sidebar">
            <h3>Categorías</h3>
            <ul class="category-list">
                <li><a href="#">Motocicletas</a>
                    <ul class="submenu">
                        <?php foreach ($clasificaciones as $clasificacion): ?>
                            <?php
                            // Crear el nombre de archivo en función de la clasificación
                            $filename = strtolower(str_replace(" ", "_", $clasificacion)) . ".php";
                            ?>
                            <li><a href="<?php echo htmlspecialchars($filename); ?>">
                                <?php echo htmlspecialchars($clasificacion); ?>
                            </a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="accesorios.php">Accesorios</a></li>
                <li><a href="ropa.php">Ropa</a></li>
            </ul>
        </aside>

        <!-- Área principal de productos -->
        <main class="main-content">
            <div class="header">
                <!-- Logo y Título -->
                <div class="logo-container">
                    <img src="logo-removebg-preview.png" alt="Logo" class="logo">
                    <link rel="icon" href="logo.ico" type="image/x-icon">
                </div>
                <h2>Bienvenido, <?php echo htmlspecialchars($vendedor['nombre']); ?></h2>
                
                <!-- Contenedor de perfil, carrito y cerrar sesión -->
                <div class="profile-cart-container">
                    <div class="cart">
                        <img src="buscar.png" width="20" height="20">
                        <a href="#" onclick="toggleSearch()">Buscar</a>
                    </div>
                    <div class="cart">
                        <img src="usuario_nuevo-removebg-preview.png" width="20" height="20">
                        <a href="registrar_cliente.php">Cliente (+)</a>
                    </div>
                    <div class="cart">
                        <img src="carrito.png" width="20" height="20">
                        <a href="carrito.php">Carrito</a>
                    </div>
                    <div class="profile">
                        <img src="perfil-removebg-preview.png" width="20" height="20">
                        <a href="perfil_vendedor.php">Perfil</a>
                    </div>
                    <div class="logout">
                        <img src="cerrarsesion.png" width="20" height="20">
                        <a href="logout.php">Cerrar sesión</a>
                    </div>
                </div>
            </div>
                    
            <div><h3>Novedades de la semana</h3></div>

            <div class="products">
                <!-- Formulario de búsqueda -->
                <form id="searchForm" method="post" action="" style="display:none;">
                    <label for="search_term">Buscar Moto:</label>
                    <input type="text" name="search_term" id="search_term">
                    <button type="submit">Buscar</button>
                </form>

                <!-- Resultados de la búsqueda -->
                <?php if ($searchResults): ?>
                    <?php foreach ($searchResults as $moto): ?>
                        <div class="product-card">
                        <img src="img_product/<?php echo htmlspecialchars($moto['id_moto']); ?>.jpeg" alt="Motocicleta">

                            <h3>SKU: <?php echo htmlspecialchars($moto['id_moto']); ?></h3>
                            <h4><?php echo htmlspecialchars($moto['marca']) . ' ' . htmlspecialchars($moto['modelo']) . ' ' . htmlspecialchars($moto['anio']); ?></h4>
                            <p>Precio: $<?php echo number_format($moto['precio'], 2); ?> MXN</p>
                            <form action="carrito.php" method="post">
                                <input type="hidden" name="id_moto" value="<?php echo htmlspecialchars($moto['id_moto']); ?>">
                                <button type="submit" class="add-to-cart">Agregar al Carrito</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Tarjeta de la motocicleta -->
                    <?php if ($moto): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($moto['id_moto']); ?>.jpeg" alt="Motocicleta">
                        <h3>SKU: <?php echo htmlspecialchars($moto['id_moto']); ?></h3>
                        <h4><?php echo htmlspecialchars($moto['marca']) . ' ' . htmlspecialchars($moto['modelo']) . ' ' . htmlspecialchars($moto['anio']); ?></h4>
                        <p>Precio: $<?php echo number_format($moto['precio'], 2); ?> MXN</p>
                        <form action="carrito.php" method="post">
                            <input type="hidden" name="id_moto" value="<?php echo htmlspecialchars($moto['id_moto']); ?>">
                            <button type="submit" class="add-to-cart">Agregar al Carrito</button>
                        </form>
                    </div>
                    <?php else: ?>
                        <p>Motocicleta no disponible.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>