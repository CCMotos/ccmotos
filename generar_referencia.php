<?php
session_start();
require('fpdf/fpdf.php');
require 'vendor/autoload.php'; // Asegúrate de que la ruta es correcta

use Picqer\Barcode\BarcodeGeneratorPNG;

// Función para generar una referencia aleatoria
function generarReferencia($length = 20) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Función para generar un NIV (número de identificación vehicular) aleatorio de 18 dígitos
function generarNIV() {
    return strtoupper(bin2hex(random_bytes(9))); // Genera un código de 18 caracteres hexadecimales
}

// Verificar si se ha generado una referencia de pago
if (!isset($_SESSION['referencia_pago']) || !isset($_SESSION['factura'])) {
    header("Location: carrito.php");
    exit();
}

// Conectar a la base de datos (ajusta estos parámetros según tu configuración)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chrischarliemotos";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos de la factura y la referencia de pago
$factura = $_SESSION['factura'];
$referencia = generarReferencia(); // Generar una nueva referencia aleatoria
$metodo_pago = $factura['metodo_pago'];
$cliente = $factura['cliente'];

// Verificar si la referencia ya existe en la tabla 'referencias'
$stmt = $conn->prepare("SELECT COUNT(*) FROM referencias WHERE referencia = ?");
$stmt->bind_param("s", $referencia);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count > 0) {
    // La referencia ya existe, redirigir o mostrar un mensaje de error
    echo "La referencia ya existe. No se puede guardar una referencia duplicada.";
    $conn->close();
    exit();
}

// Insertar los datos del cliente y las motos en la tabla 'referencias'
$nombre_cliente = $cliente['nombre_cliente'];
$email = $cliente['email'];
$domicilio = $cliente['direccion'];
$validacion = 'pendiente';
$monto = $factura['total']; // Agregar el monto desde el total de la factura
$created_at = (new DateTime())->format('Y-m-d H:i:s');

// Insertar los datos en la tabla 'referencias'
$stmt = $conn->prepare("INSERT INTO referencias (referencia, nombre_cliente, email, domicilio, validacion, monto, metodo_pago, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssss", $referencia, $nombre_cliente, $email, $domicilio, $validacion, $monto, $metodo_pago, $created_at);
$stmt->execute();
$stmt->close();

// Insertar las motos asociadas a la referencia en la misma tabla 'referencias'
foreach ($factura['motos'] as $moto) {
    $marca = $moto['marca'];
    $modelo = $moto['modelo'];
    $anio = $moto['anio'];
    $precio = $moto['precio'];
    $niv = generarNIV(); // Generar un NIV aleatorio

    // Actualizar la referencia con los datos de las motos (si es necesario)
    $stmt = $conn->prepare("UPDATE referencias SET marca = ?, modelo = ?, anio = ?, precio = ?, niv = ? WHERE referencia = ?");
    $stmt->bind_param("ssssss", $marca, $modelo, $anio, $precio, $niv, $referencia);
    $stmt->execute();
    $stmt->close();
}

// Determinar la imagen según el método de pago
$imagen_pago = '';
switch ($metodo_pago) {
    case 'oxxo':
        $imagen_pago = 'oxxo_pay_Grande.png';
        break;
    case 'referencia_banco':
        $imagen_pago = '53-539596_bbva-logo-png-transparent-png.png';
        break;
    case 'efectivo':
        $imagen_pago = 'efectivo-logo.png';
        break;
    case 'ccm_financial':
        $imagen_pago = 'LOGO FINANCIAL.jpeg';
        break;
}

// Generar el código de barras
$generator = new BarcodeGeneratorPNG();
$barcode = $generator->getBarcode($referencia, $generator::TYPE_CODE_128);
file_put_contents('barcode.png', $barcode);

// Clase para generar el PDF
class PDF extends FPDF
{
    function Header()
    {
        global $imagen_pago;
        if ($imagen_pago) {
            $this->Image($imagen_pago, 10, 6, 30);
        }
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(33, 37, 41); // Color gris oscuro
        $this->Cell(0, 10, 'Resumen de Compra', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128); // Color gris claro
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function ChapterTitle($title)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(33, 37, 41); // Color gris oscuro
        $this->Cell(0, 10, $title, 0, 1, 'L');
        $this->Ln(4);
    }

    function ChapterBody($body)
    {
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(73, 80, 87); // Color gris medio
        $this->MultiCell(0, 10, $body);
        $this->Ln();
    }

    function AddTable($header, $data)
    {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(33, 37, 41); // Color gris oscuro
        $this->SetFillColor(230, 230, 230); // Color de fondo gris claro
        foreach ($header as $col) {
            $this->Cell(45, 7, $col, 1, 0, 'C', true);
        }
        $this->Ln();
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(73, 80, 87); // Color gris medio
        foreach ($data as $row) {
            foreach ($row as $col) {
                $this->Cell(45, 6, $col, 1);
            }
            $this->Ln();
        }
    }
}

// Crear el PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->ChapterTitle('Detalles de la Compra');
$pdf->ChapterBody("Referencia de Pago: $referencia\n\nTotal a Pagar: $" . number_format($factura['total'], 2) . " MXN\n\nMetodo de Pago: " . $factura['metodo_pago']);

// Agregar información del cliente
$pdf->ChapterTitle('Referencia generada para:');
$pdf->ChapterBody("Nombre: " . $cliente['nombre_cliente'] . "\nCorreo: " . $cliente['email'] . "\nDomicilio: " . $cliente['direccion']);

// Agregar el código de barras y la referencia al PDF
$pdf->Image('barcode.png', 10, $pdf->GetY(), 100, 30);
$pdf->Ln(35);
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, "------> $referencia <------", 0, 1, 'L'); // Alineado a la izquierda
$pdf->Ln(10);

$header = array('Marca', 'Submarca', 'Modelo', 'Precio');
$data = [];
foreach ($factura['motos'] as $moto) {
    $data[] = [$moto['marca'], $moto['modelo'], $moto['anio'], '$' . number_format($moto['precio'], 2) . ' MXN'];
}
$pdf->AddTable($header, $data);

// Guardar el PDF en un archivo temporal
$pdfFilePath = 'referencia_pago.pdf';
$pdf->Output('F', $pdfFilePath);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Compra</title>
    <link rel="stylesheet" href="style_vendedor.css">
</head>
<body>
    <div class="container">
        <header>
            <div class="logo-container">
                <img src="logo-removebg-preview.png" alt="Logo">
            </div>
            <h2>Resumen de Compra</h2>
        </header>

        <div class="summary-container">
            <h3>Detalles de la Compra</h3>
            <p><strong>Referencia de Pago:</strong> <?php echo htmlspecialchars($referencia); ?></p>
            <p><strong>Total a Pagar:</strong> $<?php echo number_format($factura['total'], 2); ?> MXN</p>
            <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($factura['metodo_pago']); ?></p>

            <?php if ($imagen_pago): ?>
                <img src="<?php echo htmlspecialchars($imagen_pago); ?>" alt="Método de Pago" style="width: 200px; height: auto;">
            <?php endif; ?>

            <h3>Información del Cliente</h3>
            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['nombre_cliente']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($cliente['email']); ?></p>
            <p><strong>Dirección:</strong> <?php echo htmlspecialchars($cliente['direccion']); ?></p>

            <h3>Motos en tu Carrito</h3>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Año</th>
                        <th>Precio</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($factura['motos'] as $moto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($moto['marca']); ?></td>
                            <td><?php echo htmlspecialchars($moto['modelo']); ?></td>
                            <td><?php echo htmlspecialchars($moto['anio']); ?></td>
                            <td>$<?php echo number_format($moto['precio'], 2); ?> MXN</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <a href="<?php echo $pdfFilePath; ?>" class="download-btn" download>Descargar PDF</a>
        </div>
    </div>
</body>
</html>



