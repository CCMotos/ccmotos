<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="style_index.css">
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Logo en GIF flotando fuera del contenedor principal -->
    <div class="logo-floating">
        <img src="logo_gif.gif" alt="Logo Animado" class="logo">
    </div>

    <!-- Contenedor de mensajes (error o éxito) -->
    <div class="message-container">
        <?php if (isset($error_message)) { echo "<div class='error-message'>$error_message</div>"; } ?>
        <?php if (isset($success_message)) { echo "<div class='success-message'>$success_message</div>"; } ?>
    </div>
    
    <div class="login-container">
        <h2>Inicio de Sesión</h2>
        <form action="login.php" method="POST">
            <!-- Fila para el correo electrónico -->
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <!-- Fila para el teléfono (usado como contraseña) -->
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Teléfono (como contraseña)</label>
                    <input type="password" id="password" name="password" required>
                </div>
            </div>
            
            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>
