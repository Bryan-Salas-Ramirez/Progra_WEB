<?php
// Iniciar la sesión
session_start();

// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar los datos del formulario
    $nombres = trim($_POST['nombres']);
    $apellido_paterno = trim($_POST['apellido_paterno']);
    $apellido_materno = trim($_POST['apellido_materno']);
    $correo = trim($_POST['correo']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validar que todos los campos requeridos estén presentes
    if (empty($nombres) || empty($apellido_paterno) || empty($correo) || empty($password)) {
        $_SESSION['error'] = "Por favor, complete todos los campos requeridos.";
        header('Location: ../registro.php');
        exit();
    }

    // Validar formato de correo electrónico
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Por favor, ingrese un correo electrónico válido.";
        header('Location: ../registro.php');
        exit();
    }

    // Validar que las contraseñas coincidan
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header('Location: ../registro.php');
        exit();
    }

    try {
        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id_usuario FROM usuario WHERE correo = ?");
        $stmt->execute([$correo]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Este correo electrónico ya está registrado.";
            header('Location: ../registro.php');
            exit();
        }

        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario
        $stmt = $conn->prepare("INSERT INTO usuario (nombres, a_paterno, a_materno, correo, password, id_rol, id_estatus_us) VALUES (?, ?, ?, ?, ?, 2, 1)");
        $stmt->execute([$nombres, $apellido_paterno, $apellido_materno, $correo, $password_hash]);

        // Redirigir al login con mensaje de éxito
        $_SESSION['success'] = "¡Registro exitoso! Ahora puedes iniciar sesión.";
        header('Location: ../index.php');
        exit();

    } catch (PDOException $e) {
        // En caso de error, redirigir con mensaje de error
        $_SESSION['error'] = "Ha ocurrido un error en el sistema. Por favor, intente más tarde.";
        header('Location: ../registro.php');
        exit();
    }
} else {
    // Si alguien intenta acceder directamente a este archivo
    header('Location: ../registro.php');
    exit();
}
?> 