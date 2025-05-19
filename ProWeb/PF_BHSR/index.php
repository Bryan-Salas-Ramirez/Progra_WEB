<?php
// Iniciar la sesión
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="login-page">
    <!-- Sección izquierda con título y mensaje principal -->
    <div class="left-section">
        <img src="assets/imgs/logo.svg" alt="MetaTrack Logo" class="logo">
        <h1>MetaTrack</h1>
        <p>¡Toma el control de tu vida un paso a la vez!</p>
        <div class="circle"></div>
    </div>

    <!-- Sección derecha con el formulario de login -->
    <div class="right-section">
        <div class="login-container">
            <h2>¡Bienvenido!</h2>
            
            <form id="loginForm" method="POST" action="includes/validar_usuario.php">
                <!-- Campo de correo electrónico -->
                <div class="field">
                    <label class="label">Correo</label>
                    <div class="control">
                        <input class="input" type="email" name="correo" placeholder="Ingrese su correo" required>
                    </div>
                </div>

                <!-- Campo de contraseña con toggle de visibilidad -->
                <div class="field">
                    <label class="label">Contraseña</label>
                    <div class="control password-field">
                        <input class="input" type="password" name="password" placeholder="Ingrese su contraseña" required>
                        <span class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Enlace para recuperar contraseña -->
                <div class="field">
                    <p class="has-text-right">
                        <a href="#" class="has-text-grey">¿Olvidaste tu contraseña?</a>
                    </p>
                </div>

                <!-- Botones de acción -->
                <button type="submit" class="button login-button1">Iniciar Sesión</button>
            </form>

            <div class="divider">o</div>

            <button class="button register-button2" onclick="window.location.href='registro.php'">Registrarse</button>
        </div>
    </div>

    <script>
        // Función para mostrar y ocultar la contraseña
        // Se encarga de mostrar y ocultar la contraseña cuando el usuario hace click en el icono de ojo
        document.querySelector('.password-toggle').addEventListener('click', function() {
            const passwordInput = this.previousElementSibling;
            const icon = this.querySelector('i');
            
            // Si el input es de tipo password, se cambia a texto y se cambia el icono a ojo cerrado
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                // Si el input es de tipo texto, se cambia a password y se cambia el icono a ojo abierto
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Mostrar mensajes de error si existen
        <?php if (isset($_SESSION['error'])): ?>
            alert('<?php echo $_SESSION['error']; ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        // Mostrar mensaje de éxito si existe
        <?php if (isset($_SESSION['success'])): ?>
            alert('<?php echo $_SESSION['success']; ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
