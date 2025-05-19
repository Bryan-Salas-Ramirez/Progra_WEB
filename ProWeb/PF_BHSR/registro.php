<?php
// Iniciar la sesión
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MetaTrack - Registro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="register-page">
    <!-- Sección izquierda con título y mensaje principal -->
    <div class="left-section">
        <img src="assets/imgs/logo.svg" alt="MetaTrack Logo" class="logo">
        <h1>MetaTrack</h1>
        <p>¡Toma el control de tu vida un paso a la vez!</p>
        <div class="circle"></div>
    </div>

    <!-- Sección derecha con el formulario de registro -->
    <div class="right-section">
        <div class="register-container">
            <h2>¡Comencemos!</h2>
            
            <form id="registerForm" method="POST" action="includes/verificar_registro.php" onsubmit="return validateForm()">
                <!-- Campo de nombres -->
                <div class="field">
                    <label class="label">Nombres</label>
                    <div class="control">
                        <input class="input" type="text" name="nombres" placeholder="Sergio Raúl" required>
                    </div>
                </div>

                <!-- Campo de apellido paterno -->
                <div class="field">
                    <label class="label">Apellido Paterno</label>
                    <div class="control">
                        <input class="input" type="text" name="apellido_paterno" placeholder="González" required>
                    </div>
                </div>

                <!-- Campo de apellido materno -->
                <div class="field">
                    <label class="label">Apellido Materno</label>
                    <div class="control">
                        <input class="input" type="text" name="apellido_materno" placeholder="Pérez" required>
                    </div>
                </div>

                <!-- Campo de correo electrónico -->
                <div class="field">
                    <label class="label">Correo</label>
                    <div class="control">
                        <input class="input" type="email" name="correo" placeholder="ejemplo@correo.com" required>
                    </div>
                </div>

                <!-- Campo de contraseña con toggle de visibilidad -->
                <div class="field">
                    <label class="label">Contraseña</label>
                    <div class="control password-field">
                        <input class="input" type="password" name="password" id="password" placeholder="Ingrese su contraseña" required>
                        <span class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Campo de confirmación de contraseña -->
                <div class="field">
                    <label class="label">Confirmar Contraseña</label>
                    <div class="control password-field">
                        <input class="input" type="password" name="confirm_password" id="confirm_password" placeholder="Confirme su contraseña" required>
                        <span class="password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <!-- Botón de registro -->
                <button type="submit" class="button register-button1">Registrarse</button>
            </form>

            <!-- Sección de cuenta existente -->
            <p class="existing-account">¿Ya tienes una cuenta?</p>
            <button class="button register-button2" onclick="window.location.href='index.php'">Iniciar Sesión</button>
        </div>
    </div>

    <script>
        // Función para mostrar y ocultar la contraseña
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Función para validar que las contraseñas coincidan
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                return false;
            }
            return true;
        }

        // Mostrar mensajes de error o éxito si existen
        <?php if (isset($_SESSION['error'])): ?>
            alert('<?php echo $_SESSION['error']; ?>');
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            alert('<?php echo $_SESSION['success']; ?>');
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
    </script>
</body>
</html> 