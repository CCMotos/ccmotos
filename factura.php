<?php
session_start();

// Verificar si la sesión de usuario está activa
if (!isset($_POST['metodo_pago']) || !isset($_POST['total_pago']) || !isset($_POST['cliente'])) {
    header("Location: index.php");
    exit();
}

$metodo_pago = $_POST['metodo_pago'];
$total_pago = $_POST['total_pago'];
$cliente = $_POST['cliente'];

// Recuperar información del carrito
if (isset($_SESSION['factura']['motos']) && !empty($_SESSION['factura']['motos'])) {
    $articulos = $_SESSION['factura']['motos'];
} else {
    $articulos = [];
}

// Datos fiscales de la tienda
$empresa_nombre = "Moto Tienda S.A.";
$empresa_rfc = "RFC123456789";
$empresa_direccion = "Av. Las Motocicletas 123, Ciudad, México";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <style>
        /* Estilo general */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fff9e6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #d4a017;
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo-container {
            margin-right: 20px;
        }

        .logo {
            width: 50px;
            height: auto;
        }

        .header h2 {
            font-size: 24px;
        }

        /* Sección de contenido */
        .contenido {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        h3 {
            color: #d4a017;
        }

        /* Detalle de productos */
        .producto-lista {
            list-style-type: none;
            padding: 0;
        }

        .producto-lista li {
            margin-bottom: 15px;
        }

        /* Imagen de la moto */
        .imagen-producto {
            width: 100px;
            height: auto;
            margin-right: 15px;
        }

        /* Información fiscal */
        .info-fiscal {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #d4a017;
        }

        .info-fiscal p {
            margin: 5px 0;
        }

        /* Código de barras */
        img {
            width: 200px;
            height: auto;
            margin-top: 20px;
        }

        /* Botón de impresión */
        .btn-imprimir {
            padding: 12px 20px;
            background-color: #ffbf00;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin-top: 30px;
        }

        .btn-imprimir:hover {
            background-color: #d4a017;
        }
    </style>

    <div class="container">
        <header class="header">
            <div class="logo-container">
                <img src="logo-removebg-preview.png" alt="Logo" class="logo">
            </div>
            <h2>Factura - Copia sin valor</h2>
        </header>

        <div class="contenido">
            <h3>Cliente: <?php echo htmlspecialchars($cliente); ?></h3>
            <h3>Metodo de Pago: <?php echo htmlspecialchars($metodo_pago); ?></h3>
            <h3>Total: $<?php echo number_format($total_pago, 2); ?> MXN</h3>

            <h3>Artículos:</h3>
            <ul class="producto-lista">
                <?php if (!empty($articulos)): ?>
                    <?php foreach ($articulos as $moto): ?>
                        <li>
                            <img src="<?php echo htmlspecialchars($moto['imagen']); ?>" alt="Imagen de la moto" class="imagen-producto">
                            <strong><?php echo htmlspecialchars($moto['marca'] . ' ' . $moto['modelo']); ?></strong><br>
                            Precio: $<?php echo number_format($moto['precio'], 2); ?>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No se han seleccionado motos.</li>
                <?php endif; ?>
            </ul>

            <div class="info-fiscal">
                <h3>Datos Fiscales:</h3>
                <p><strong>Nombre de la empresa:</strong> <?php echo $empresa_nombre; ?></p>
                <p><strong>RFC:</strong> <?php echo $empresa_rfc; ?></p>
                <p><strong>Dirección:</strong> <?php echo $empresa_direccion; ?></p>
            </div>

            <hr>
            <p><strong>¡Esta es una copia sin valor de la factura original!</strong></p>
            <p>Gracias por tu compra.</p>

            <!-- Botón de impresión -->
            <button class="btn-imprimir" onclick="window.print();">Imprimir Factura</button>
        </div>
    </div>

</body>
</html>
