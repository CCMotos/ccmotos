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
if (isset($_POST['id_moto'])) {
    $id_moto = $_POST['id_moto'];

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

// Procesar la búsqueda de cliente
$clienteEncontrado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email_cliente = $_POST['email'];

    // Buscar cliente por correo
    $sqlVerificarCliente = "SELECT id_cliente, nombre_cliente, email, telefono, direccion FROM cliente WHERE email = ?";
    $stmt = $conn->prepare($sqlVerificarCliente);
    $stmt->bind_param("s", $email_cliente);
    $stmt->execute();
    $resultCliente = $stmt->get_result();

    if ($resultCliente->num_rows > 0) {
        $clienteEncontrado = true;
        $cliente = $resultCliente->fetch_assoc();
        $mensajeCliente = "Se encontró un cliente con ese correo.";
        $_SESSION['cliente'] = $cliente; // Guardar información del cliente en la sesión
    } else {
        $mensajeCliente = "No se encontró un cliente con ese correo.";
    }
    $stmt->close();
}

// Verificar si se ha enviado una moto para eliminar del carrito
if (isset($_POST['id_moto_eliminar'])) {
    $id_moto_eliminar = $_POST['id_moto_eliminar'];

    if (($key = array_search($id_moto_eliminar, $_SESSION['carrito'])) !== false) {
        unset($_SESSION['carrito'][$key]);
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar el array
        $mensaje = "La moto ha sido eliminada del carrito.";
    } else {
        $mensaje = "La moto no se encontró en el carrito.";
    }
}

// Consultar detalles de las motos en el carrito
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['facturar'])) {
    $metodo_pago = $_POST['metodo_pago'];

    if (empty($metodo_pago)) {
        echo "Método de pago no seleccionado.";
        exit;
    }

    // Generar referencia de pago
    if ($metodo_pago === 'referencia_banco') {
        $referencia = "ReferenciaBanco" . rand(1000, 9999);
    } elseif ($metodo_pago === 'oxxo') {
        $referencia = "OXXO" . rand(1000, 9999);
    } elseif ($metodo_pago === 'efectivo') {
        $referencia = "EFECTIVO" . rand(1000, 9999);
    } elseif ($metodo_pago === 'ccm_financial') {
        $referencia = "CCM" . rand(1000, 9999);
    }

    $_SESSION['referencia_pago'] = $referencia;
    $_SESSION['factura'] = [
        'motos' => $carrito_motos,
        'total' => $total_costo,
        'metodo_pago' => $metodo_pago,
        'referencia' => $referencia,
        'cliente' => $_SESSION['cliente'] // Incluir información del cliente
    ];

    header("Location: generar_referencia.php");
    exit();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="carrito1.css">
    <link rel="icon" href="logo.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-container">
                <img src="logo-removebg-preview.png" alt="Logo">
            </div>
            <h2>Carrito de Compras</h2>
        </header>

        <div class="cart-container">
            <?php if (count($carrito_motos) > 0): ?>
                <h3>Motos en tu Carrito</h3>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Imagen</th>
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
                                     $imagePath = "img_product/" . htmlspecialchars($moto['id_moto']) . ".jpeg";
                                    if (file_exists($imagePath)): ?>
                                        <img src="<?php echo $imagePath; ?>" alt="Imagen de <?php echo htmlspecialchars($moto['marca']); ?>" class="product-image">
                                    <?php else: ?>
                                        <img src="default14.jpeg" alt="Imagen no disponible" class="product-image">
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($moto['marca']); ?></td>
                                <td><?php echo htmlspecialchars($moto['modelo']); ?></td>
                                <td><?php echo htmlspecialchars($moto['anio']); ?></td>
                                <td>$<?php echo number_format($moto['precio'], 2); ?> MXN</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                
                <tbody>
    <?php foreach ($carrito_motos as $moto): ?>
        <tr>
            <td>
                <?php
                 $imagePath = "img_product/" . htmlspecialchars($moto['id_moto']) . ".jpeg";
                if (file_exists($imagePath)): ?>
                    <img src="<?php echo $imagePath; ?>" alt="Imagen de <?php echo htmlspecialchars($moto['marca']); ?>" class="product-image">
                <?php else: ?>
                    <img src="default14.jpeg" alt="Imagen no disponible" class="product-image">
                <?php endif; ?>
            </td>

            <h5>¿Deseas eliminar?</h5>

            
           
            <td>
                <form method="POST" action="carrito.php">
                    <input type="hidden" name="id_moto_eliminar" value="<?php echo $moto['id_moto']; ?>">
                    <button type="submit" class="remove-btn">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>


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
                </div>
            <?php else: ?>
                <p>No tienes artículos en el carrito.</p>
            <?php endif; ?>
        </div>

        

        <div class="cliente-form">
            <h3>Buscar Cliente</h3>
            <form method="POST" action="carrito.php">
                <label for="email">Correo del cliente:</label>
                <input type="email" id="email" name="email" value="<?php echo isset($email_cliente) ? $email_cliente : ''; ?>" required>
                <button type="submit" class="search-btn">Buscar Cliente</button>
            </form>

            <?php if (isset($mensajeCliente)): ?>
                <div class="message"><?php echo htmlspecialchars($mensajeCliente); ?></div>
            <?php endif; ?>

            <?php if ($clienteEncontrado): ?>
                <div class="found-client">
                    <p>Cliente encontrado: <?php echo htmlspecialchars($cliente['nombre_cliente']); ?></p>
                    <p>Correo: <?php echo htmlspecialchars($cliente['email']); ?></p>
                    <p>Teléfono: <?php echo htmlspecialchars($cliente['telefono']); ?></p>
                    <p>Dirección: <?php echo htmlspecialchars($cliente['direccion']); ?></p>
                </div>

                <h3>Forma de Pago</h3>
                <form method="post" action="carrito.php" id="pago-form">
                    <label for="metodo_pago">Método de pago:</label>
                    <select id="metodo_pago" name="metodo_pago" class="styled-select" required>
                        <option value="">Selecciona un método de pago</option>
                        <option value="referencia_banco">Referencia en banco</option>
                        <option value="oxxo">OXXO Pay</option>
                        <option value="efectivo">En efectivo</option>
                        <option value="ccm_financial">CC&M Financial</option> 
                    </select>
                    <button type="submit" name="facturar" class="facturar-btn">Continuar</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>