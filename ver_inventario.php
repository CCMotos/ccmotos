<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Datos de conexión a la base de datos
$host = "localhost";
$dbname = "chrischarliemotos";
$username = "root";
$password = "";

try {
    // Crear la conexión
    $conn = new mysqli($host, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    // Obtener datos de inventario
    $stmt = $conn->prepare("SELECT inventario.id_inventario, inventario.id_moto, inventario.cantidad_disponible, inventario.id_sucursal, moto.marca, moto.modelo FROM inventario JOIN moto ON inventario.id_moto = moto.id_moto WHERE inventario.id_sucursal = ?");
    $stmt->bind_param("i", $_SESSION['user']['id_sucursal']);
    $stmt->execute();
    $result = $stmt->get_result();

    $inventario = [];
    while ($row = $result->fetch_assoc()) {
        $inventario[] = $row;
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Inventario</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Incluye aquí el CSS adicional */
    </style>
</head>
<body>
    <style>
        /* Estilos adicionales para la tabla de inventario */
.table-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.table-container h2 {
    color: #333;
    margin-bottom: 1.5rem;
    text-align: center;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1.5rem;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 0.75rem;
    text-align: left;
}

th {
    background-color: #f4f4f4;
    color: #333;
}

td img {
    max-width: 100px;
    border-radius: 8px;
}

.message-container {
    margin-bottom: 1rem;
    text-align: center;
}

.success-message {
    color: #28a745;
}

.error-message {
    color: #dc3545;
}
    </style>
    <div class="table-container">
        <h2>Inventario</h2>

        <!-- Contenedor de mensajes de error y éxito -->
        <div class="message-container">
            <?php if (isset($_SESSION['error_message'])) : ?>
                <div class="error-message">
                    <strong>Error:</strong> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success_message'])) : ?>
                <div class="success-message">
                    <strong>Éxito:</strong> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                </div>
            <?php endif; ?>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID Inventario</th>
                    <th>Imagen</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Cantidad Disponible</th>
                    <th>ID Sucursal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($inventario)) : ?>
                    <?php foreach ($inventario as $item) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['id_inventario']); ?></td>
                            <td><img src="img_product/<?php echo htmlspecialchars($item['id_moto']); ?>.jpeg" alt="Imagen de <?php echo htmlspecialchars($item['modelo']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['marca']); ?></td>
                            <td><?php echo htmlspecialchars($item['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($item['cantidad_disponible']); ?></td>
                            <td><?php echo htmlspecialchars($item['id_sucursal']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6">No hay productos en el inventario.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>