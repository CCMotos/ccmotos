<?php
session_start();

// Verificar si el usuario ha iniciado sesión y es inventariador
if (!isset($_SESSION['user']) || $_SESSION['user']['tipo_usuario'] !== 'inventariado') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Inventariador</title>
    <link rel="stylesheet" href="style_index.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Estructura general */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff9e6;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* Barra lateral de categorías */
        .sidebar {
            width: 20%;
            background-color: #f4f4f4;
            padding: 2rem;
        }

        .sidebar h3 {
            color: #d4a017;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .category-list {
            list-style: none;
            padding: 0;
        }

        .category-list li {
            margin-bottom: 1rem;
        }

        .category-list li a {
            color: #7d7d7d;
            text-decoration: none;
            transition: color 0.3s;
        }

        .category-list li a:hover {
            color: #d4a017;
        }

        /* Área principal */
        .main-content {
            flex: 1;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .logo-container .logo {
            height: 60px;
            margin-right: 20px;
        }

        .profile-cart-container {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-cart-container > div {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .profile-cart-container a {
            text-decoration: none;
            color: #333;
            font-size: 14px;
            margin-top: 5px;
        }

        .profile-cart-container img {
            width: 40px; /* Ajustar el tamaño del icono */
            height: 40px; /* Ajustar el tamaño del icono */
            margin-bottom: 5px;
            transition: transform 0.3s ease;
        }

        .profile-cart-container img:hover {
            transform: scale(1.1);
        }

        /* Estilos de tarjetas de productos */
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }

        .product-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }

        .product-card img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }

        .product-card h4 {
            color: #333;
            font-size: 1.1rem;
        }

        .product-card p {
            color: #7d7d7d;
            margin: 0.5rem 0;
        }

        .add-to-cart {
            padding: 0.5rem;
            background-color: #ffbf00;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .add-to-cart:hover {
            background-color: #d4a017;
        }

        .logo-inventory-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .logo-inventory-container img {
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Barra lateral de categorías -->
        <div class="sidebar">
            <h3>Categorías</h3>
            <ul class="category-list">
                <li><a href="ver_inventario.php">Ver Inventario</a></li>
                <li><a href="agregar_producto.php">Agregar Producto</a></li>
                <li><a href="actualizar_inventario.php">Actualizar Inventario</a></li>
                <li><a href="pagos.php">Pagar Referencia</a></li>
            </ul>
        </div>

        <!-- Área principal -->
        <div class="main-content">
            <div class="header">
                <div class="logo-container">
                <img src="logo_gif.gif" alt="Logo Animado" class="logo">
                <img src="inventario.png" alt="Inventario" class="inventory" width="130" height="80">
                </div>
                <div class="profile-cart-container">
                    <div class="profile">
                        <img src="perfil-removebg-preview.png" alt="Perfil">
                        <a href="perfil_inventariador.php">Perfil</a>
                    </div>
                    <div class="logout">
                        <img src="cerrarsesion.png" alt="Cerrar Sesión">
                        <a href="logout.php">Cerrar Sesión</a>
                    </div>
                </div>
            </div>

     

            <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']['nombre']); ?>!</h2>
            <p>El uso de este sistema está controlado y vigilado.</p>

            <!-- Aquí puedes añadir más contenido específico para el inventariador -->
            <div class="products">
                <div class="product-card">
                    <img src="product_image.jpg" alt="Producto">
                    <h4>Producto 1</h4>
                    <p>Descripción del producto 1</p>
                    <button class="add-to-cart">Agregar al carrito</button>
                </div>
                <div class="product-card">
                    <img src="product_image.jpg" alt="Producto">
                    <h4>Producto 2</h4>
                    <p>Descripción del producto 2</p>
                    <button class="add-to-cart">Agregar al carrito</button>
                </div>
                <!-- Añadir más tarjetas de productos según sea necesario -->
            </div>
        </div>
    </div>
</body>
</html>