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

    // Verificar si el formulario fue enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener datos del formulario
        $marca = $_POST['marca'] ?? null;
        $modelo = $_POST['modelo'] ?? null;
        $anio = $_POST['anio'] ?? null;
        $precio = $_POST['precio'] ?? null;
        $clasificacion = $_POST['clasificacion'] ?? null;
        $estado_inventario = $_POST['estado_inventario'] ?? null;
        $imagen = $_FILES['imagen'] ?? null;

        // Validar que todos los campos estén completos
        if ($marca && $modelo && $anio && $precio && $clasificacion && $estado_inventario && $imagen) {
            // Insertar datos en la tabla moto
            $stmt = $conn->prepare("INSERT INTO moto (marca, modelo, anio, precio, id_clasificacion, id_sucursal, estado_inventario) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssiiiss", $marca, $modelo, $anio, $precio, $clasificacion, $id_sucursal, $estado_inventario);

            if ($stmt->execute()) {
                // Obtener el ID del nuevo producto
                $producto_id = $stmt->insert_id;

                // Guardar la imagen en la carpeta 'img_product' con el nombre del ID del producto
                $target_dir = "img_product/";
                $target_file = $target_dir . $producto_id . "." . pathinfo($imagen['name'], PATHINFO_EXTENSION);

                if (move_uploaded_file($imagen['tmp_name'], $target_file)) {
                    $_SESSION['success_message'] = "Producto agregado exitosamente.";
                } else {
                    $_SESSION['error_message'] = "Error al subir la imagen.";
                }
            } else {
                $_SESSION['error_message'] = "Error al agregar el producto.";
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
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="style_index.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        /* Estilos adicionales para el formulario */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        }

        .form-container h2 {
            color: #333;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1rem;
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            color: #666;
        }

        .form-group select,
        .form-group input {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #d4a017;
        }

        .form-group button {
            padding: 0.75rem;
            background-color: #ffbf00;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #d4a017;
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

        .form-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
        }

        .form-row .form-group {
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Agregar Producto</h2>

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

        <form action="agregar_producto.php" method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="form-group">
                    <label for="marca">Marca</label>
                    <select id="marca" name="marca" required>
                        <option value="">Seleccione una marca</option>
                        <option value="Italika">ITALIKA</option>
                        <option value="Honda">HONDA</option>
                        <option value="Yamaha">YAMAHA</option>
                        <option value="Suzuki">SUZUKI</option>
                        <option value="Kawasaki">KAWASAKI</option>
                        <option value="Harley-Davidson">HARLEY-DAVIDSON</option>
                        <option value="Ducati">DUCATI</option>
                        <option value="BMW">BMW</option>
                        <option value="KTM">KTM</option>
                        <option value="Vento">VENTO</option>
                        <option value="Bajaj">BAJAJ</option>
                        <option value="Benelli">BENELLI</option>
                        <option value="Royal Enfield">ROYAL ENFIELD</option>
                        <option value="Triumph">TRIUMPH</option>
                        <option value="Moto Guzzi">MOTO GUZZI</option>
                        <option value="Aprilia">APRILIA</option>
                        <option value="Husqvarna">HUSQVARNA</option>
                        <option value="Indian">INDIAN</option>
                        <option value="CFMoto">CFMOTO</option>
                        <option value="Vespa">VESPA</option>
                        <option value="ELECTRICA">ELECTRICA</option>
                        <!-- Añadir más opciones según sea necesario -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="anio">Año</label>
                    <select id="anio" name="anio" required>
                        <option value="">Seleccione un año</option>
                        <?php for ($year = 2021; $year <= date("Y"); $year++) : ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="precio">Precio</label>
                    <input type="number" id="precio" name="precio" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="clasificacion">Clasificación</label>
                    <select id="clasificacion" name="clasificacion" required>
                        <option value="">Seleccione una clasificación</option>
                        <option value="1">Motoneta</option>
                        <option value="2">Motocicleta</option>
                        <option value="3">Trimoto</option>
                        <option value="4">Eléctricas</option>
                        <option value="5">Cuatrimoto</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="estado_inventario">Estado del Inventario</label>
                    <select id="estado_inventario" name="estado_inventario" required>
                        <option value="">Seleccione un estado</option>
                        <option value="disponible">Disponible</option>
                        <option value="no disponible">No Disponible</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen del Producto</label>
                <input type="file" id="imagen" name="imagen" required>
            </div>

            <div class="form-group">
                <button type="submit">Agregar Producto</button>
            </div>
        </form>
    </div>
</body>
</html>