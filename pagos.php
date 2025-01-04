<?php
require('fpdf/fpdf.php');

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "chrischarliemotos";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener datos de la referencia desde la base de datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $referencia = $_POST['referencia'];
    $monto_pagado = $_POST['monto_pagado'];

    $sql = "SELECT * FROM referencias WHERE referencia = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $referencia);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Datos extraídos de la base de datos
        $nombre_cliente = $row['nombre_cliente'];
        $email = $row['email'];
        $domicilio = $row['domicilio'];
        $marca = $row['marca'];
        $modelo = $row['modelo'];
        $anio = $row['anio'];
        $niv = $row['niv'];
        $metodo_pago = $row['metodo_pago'];
        $monto = $row['precio'];
        $validacion = $row['validacion'];

        // Verificar el pago y generar la factura
        if ($validacion === "Procedente" && $monto_pagado >= $monto) {
            $cambio = $monto_pagado - $monto;

            // Generar el PDF
            class PDF extends FPDF
{
    function Header()
    {
        global $imagen_pago;

        {
            $this->SetFillColor(255, 191, 0);
            $this->SetLineWidth(0.1); // Grosor de 1.5 puntos
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('Arial', 'B', 16);
            $this->Cell(0, 15, 'FACTURA DE COMPRA', 0, 1, 'C', true);
            $this->Ln(5);
        }

        // Configurar la fecha y hora de emisión
        date_default_timezone_set('America/Mexico_City'); // Ajustar a tu zona horaria
        $fecha_hora_emision = date('H:i:s/Y-m-d'); // Formato solicitado

        // Logo
        $logo = 'logo-removebg-preview.png'; // Nombre del archivo del logo
        if (file_exists($logo)) {
            $this->Image($logo, 10, 6, 30); // Coordenadas X=10, Y=6, ancho=30
        } else {
            error_log("El logo no se encontró en la ruta: " . $logo);
        }

       

        // Fecha y hora de emisión
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(73, 80, 87); // Color gris medio
        $this->Cell(0, 10, 'Ixtapaluca, Estado de Mexico a, ' . $fecha_hora_emision, 0, 1, 'R');
        $this->Ln(10);
    }



                function Footer()
                {
                    $this->Cell(0, 10, 'Garantia por 60 dias naturales a partir de la fecha de emision de la presente factura', 0, 0, 'C');
                    $this->SetY(-15);
                    $this->SetFont('Arial', 'I', 10);
                    $this->SetTextColor(50, 50, 50);
                    $this->Cell(0, 10, 'Gracias por su compra. C&C Motos.', 0, 0, 'C');
                }

                function SectionTitle($title)
                {
                    $this->SetFillColor(214, 162, 22);
                    $this->SetTextColor(0, 0, 0);
                    $this->SetFont('Arial', 'B', 14);
                    $this->Cell(0, 10, $title, 0, 1, 'L', true);
                    $this->Ln(2);
                }

                function SectionContent($label, $value)
                {
                    $this->SetFont('Arial', '', 12);
                    $this->SetTextColor(0, 0, 0);
                    $this->Cell(50, 10, utf8_decode($label), 0, 0, 'L');
                    $this->Cell(0, 10, utf8_decode($value), 0, 1, 'L');
                }
            }

            $pdf = new PDF();
            $pdf->AddPage();

            // Sección de cliente
            $pdf->SectionTitle("Datos del Cliente");
            $pdf->SectionContent("Nombre:", $nombre_cliente);
            $pdf->SectionContent("Email:", $email);
            $pdf->SectionContent("Domicilio:", $domicilio);

            // Sección de la compra
            $pdf->Ln(5);
            $pdf->SectionTitle("Datos de pago");
            $pdf->SectionContent("Referencia:", $referencia);
            $pdf->SectionContent("Método de Pago:", $metodo_pago);
            $pdf->SectionContent("Monto Total:", "$" . number_format($monto, 2) . " MXN");
            $pdf->SectionContent("Monto Pagado:", "$" . number_format($monto_pagado, 2) . " MXN");
            $pdf->SectionContent("Cambio:", "$" . number_format($cambio, 2) . " MXN");

            // Sección de producto
            $pdf->Ln(5);
            $pdf->SectionTitle("Detalles de la Motocicleta");
            $pdf->SectionContent("Marca:", $marca);
            $pdf->SectionContent("Modelo:", $modelo);
            $pdf->SectionContent("Año:", $anio);
            $pdf->SectionContent("NIV:", $niv);

            // Descargar PDF
            $pdf->Output('D', "Factura_Referencia_$referencia.pdf");
            exit();
        } else {
            echo "<p class='alert error'>Pago insuficiente o referencia inválida.</p>";
        }
    } else {
        echo "<p class='alert error'>Referencia no encontrada.</p>";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pago</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #555;
        }

        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 15px;
            margin-top: 20px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }

        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Realizar Pago</h2>
        <form method="post" action="">
            <label for="referencia">Número de Referencia:</label>
            <input type="text" id="referencia" name="referencia" required><br>

            <label for="monto_pagado">Monto a Pagar:</label>
            <input type="number" id="monto_pagado" name="monto_pagado" step="0.01" required><br>

            <input type="submit" value="Pagar">
        </form>
    </div>

</body>
</html>
