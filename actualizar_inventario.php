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

    // Obtener id_sucursal del usuario que tiene la sesión activa
    $id_sucursal = $_SESSION['user']['id_sucursal'];

    // Obtener datos de motos existentes
    $stmt = $conn->prepare("SELECT id_moto, marca, modelo FROM moto");
    $stmt->execute();
    $result = $stmt->get_result();

    $motos = [];
    while ($row = $result->fetch_assoc()) {
        $motos[] = $row;
    }

    $stmt->close();

    // Obtener datos de inventarios existentes para la sucursal del usuario
    $stmt = $conn->prepare("SELECT id_inventario, id_moto, cantidad_disponible FROM inventario WHERE id_sucursal = ?");
    $stmt->bind_param("i", $id_sucursal);
    $stmt->execute();
    $result = $stmt->get_result();

    $inventarios = [];
    while ($row = $result->fetch_assoc()) {
        $inventarios[] = $row;
    }

    $stmt->close();

    // Verificar si el formulario fue enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos del formulario
        $id_inventario = $_POST['id_inventario'] ?? null;
        $id_moto = $_POST['id_moto'] ?? null;
        $cantidad_disponible = $_POST['cantidad_disponible'] ?? null;

        // Validar que todos los campos estén completos
        if ($id_inventario && $cantidad_disponible) {
            // Actualizar datos en la tabla inventario
            $stmt = $conn->prepare("UPDATE inventario SET cantidad_disponible = ? WHERE id_inventario = ? AND id_sucursal = ?");
            $stmt->bind_param("iii", $cantidad_disponible, $id_inventario, $id_sucursal);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Inventario actualizado exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al actualizar el inventario.";
            }

            $stmt->close();
        } elseif ($id_moto && $cantidad_disponible) {
            // Insertar datos en la tabla inventario
            $stmt = $conn->prepare("INSERT INTO inventario (id_moto, cantidad_disponible, id_sucursal) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $id_moto, $cantidad_disponible, $id_sucursal);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Inventario agregado exitosamente.";
            } else {
                $_SESSION['error_message'] = "Error al agregar el inventario.";
            }

            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Por favor, complete todos los campos.";
        }
    }

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
    <title>Actualizar Inventario</title>
    <link rel="stylesheet" href="style_index.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Incluye aquí el CSS adicional */
    </style>
    <script>
        function toggleMotoField() {
            var idInventario = document.getElementById("id_inventario").value;
            var idMoto = document.getElementById("id_moto");
            if (idInventario) {
                idMoto.disabled = true;
            } else {
                idMoto.disabled = false;
            }
        }
    </script>
</head>
<body>
    <div class="form-container">
        <h2>Actualizar Inventario</h2>

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

        <form action="actualizar_inventario.php" method="POST">
            <div class="form-group">
                <label for="id_inventario">Inventario Existente</label>
                <select id="id_inventario" name="id_inventario" onchange="toggleMotoField()">
                    <option value="">Seleccione un inventario</option>
                    <?php foreach ($inventarios as $inventario) : ?>
                        <option value="<?php echo htmlspecialchars($inventario['id_inventario']); ?>">
                            <?php echo htmlspecialchars($inventario['id_inventario'] . ' - ' . $inventario['id_moto'] . ' - Cantidad: ' . $inventario['cantidad_disponible']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_moto">Moto</label>
                <select id="id_moto" name="id_moto">
                    <option value="">Seleccione una moto</option>
                    <?php foreach ($motos as $moto) : ?>
                        <option value="<?php echo htmlspecialchars($moto['id_moto']); ?>">
                            <?php echo htmlspecialchars($moto['id_moto'] . ' - ' . $moto['marca'] . ' - ' . $moto['modelo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="cantidad_disponible">Cantidad Disponible</label>
                <input type="number" id="cantidad_disponible" name="cantidad_disponible" required>
            </div>

            <div class="form-group">
                <button type="submit">Actualizar Inventario</button>
            </div>
        </form>
    </div>
</body>
</html>