<?php
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Manejo de la carga de archivos
$mensaje = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $upload_dir = __DIR__ . '/uploads/'; // Ruta absoluta para la carpeta de uploads

    // Crear la carpeta de uploads si no existe
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);  // Crea la carpeta con permisos adecuados
    }

    // Archivos a cargar
    $comprobante_ingresos = $_FILES['comprobante_ingresos'];
    $identificacion_oficial = $_FILES['identificacion_oficial'];

    // Procesar los archivos de comprobante de ingresos
    if ($comprobante_ingresos['error'] == 0) {
        $comprobante_path = $upload_dir . basename(str_replace(" ", "_", $comprobante_ingresos['name']));
        if (move_uploaded_file($comprobante_ingresos['tmp_name'], $comprobante_path)) {
            $mensaje = "Comprobante de ingresos cargado correctamente.";
        } else {
            $mensaje = "Error al subir el archivo de comprobante de ingresos.";
        }
    }

    // Procesar los archivos de identificación oficial
    if ($identificacion_oficial['error'] == 0) {
        $identificacion_path = $upload_dir . basename(str_replace(" ", "_", $identificacion_oficial['name']));
        if (move_uploaded_file($identificacion_oficial['tmp_name'], $identificacion_path)) {
            $mensaje = "Identificación oficial cargada correctamente.";
        } else {
            $mensaje = "Error al subir el archivo de identificación oficial.";
        }
    }

    // Conectar a la base de datos
    $servername = "localhost"; // Cambiar por tu servidor de base de datos
    $username = "root"; // Cambiar por tu usuario de base de datos
    $password = ""; // Cambiar por tu contraseña de base de datos
    $dbname = "c&c_financial"; // Base de datos de destino

    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Obtener los datos del formulario
    $numCred = uniqid(); // Generar un ID único para el crédito
    $nombres = $_POST['nombres'];
    $apellido_p = $_POST['apellido_p'];
    $apellido_m = $_POST['apellido_m'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ocupacion = $_POST['ocupacion'];
    $ingresos_mensuales = $_POST['ingresos_mensuales'];
    $monto = $_POST['monto'];
    $score = $_POST['score'];

    // Insertar los datos en la base de datos
    $sql = "INSERT INTO prospectos (numCred, nombres, apellido_p, apellido_m, telefono, email, direccion, ocupacion, ingresos_mensuales, monto, score) 
            VALUES ('$numCred', '$nombres', '$apellido_p', '$apellido_m', '$telefono', '$email', '$direccion', '$ocupacion', '$ingresos_mensuales', '$monto', '$score')";

    if ($conn->query($sql) === TRUE) {
        $mensaje = "Solicitud de financiamiento enviada correctamente.";
    } else {
        $mensaje = "Error al enviar la solicitud: " . $conn->error;
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Financiamiento</title>
    <link rel="stylesheet" href="financial.css">
</head>
<body>
    
    <div class="container">
        <h2>Formulario de Financiamiento</h2>

        <!-- Mensaje de estado -->
        <?php if (!empty($mensaje)): ?>
            <div class="message"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- Formulario para el financiamiento -->
        <form method="POST" enctype="multipart/form-data">
            <label for="nombres">Nombres:</label>
            <input type="text" id="nombres" name="nombres" required>

            <label for="apellido_p">Apellido Paterno:</label>
            <input type="text" id="apellido_p" name="apellido_p" required>

            <label for="apellido_m">Apellido Materno:</label>
            <input type="text" id="apellido_m" name="apellido_m" required>

            <label for="telefono">Teléfono:</label>
            <input type="tel" id="telefono" name="telefono" required>

            <label for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" required>

            <label for="direccion">Dirección:</label>
            <input type="text" id="direccion" name="direccion" required>

            <label for="ocupacion">Ocupación:</label>
            <input type="text" id="ocupacion" name="ocupacion" required>

            <label for="ingresos_mensuales">Ingresos Mensuales:</label>
            <input type="number" id="ingresos_mensuales" name="ingresos_mensuales" required>

            <label for="monto">Monto del préstamo:</label>
            <input type="number" id="monto" name="monto" required>

            <label for="score">Score Crediticio:</label>
            <input type="number" id="score" name="score" required>

            <label for="comprobante_ingresos">Comprobante de Ingresos (PDF o Imagen):</label>
            <input type="file" id="comprobante_ingresos" name="comprobante_ingresos" accept=".pdf, .jpg, .jpeg, .png" required>

            <label for="identificacion_oficial">Identificación Oficial (PDF o Imagen):</label>
            <input type="file" id="identificacion_oficial" name="identificacion_oficial" accept=".pdf, .jpg, .jpeg, .png" required>

            <button type="submit">Enviar Solicitud de Financiamiento</button>
        </form>
    </div>
</body>
</html>
