<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once 'conexion.php';

// Verificar si se recibieron los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $password = $_POST['password'];

    try {
        // Consulta para verificar las credenciales y el estado del usuario
        $query = "SELECT u.*, r.nombre as rol_nombre 
                 FROM usuario u 
                 JOIN rol r ON u.id_rol = r.id_rol 
                 WHERE u.correo = :correo";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {
            // Verificar si el usuario está activo
            if ($usuario['id_estatus_us'] == 1) {
                // Usuario activo, proceder con el inicio de sesión
                $_SESSION['id_usuario'] = $usuario['id_usuario'];
                $_SESSION['nombre'] = $usuario['nombres'] . ' ' . $usuario['a_paterno'];
                $_SESSION['id_rol'] = $usuario['id_rol'];
                $_SESSION['rol'] = $usuario['rol_nombre'];

                // Redirigir según el rol del usuario
                if ($usuario['id_rol'] == 1) {
                    // Si es administrador
                    header('Location: ../home/admin_dashboard.php');
                } else {
                    // Si es usuario normal
                    header('Location: ../home/index.php');
                }
                exit();
            } else {
                // Usuario inactivo
                $_SESSION['error'] = 'Tu cuenta está inactiva. Por favor, contacta al administrador.';
                header('Location: ../index.php');
                exit();
            }
        } else {
            // Credenciales incorrectas
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            header('Location: ../index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Error al intentar iniciar sesión. Por favor, intenta nuevamente.';
        header('Location: ../index.php');
        exit();
    }
} else {
    // Si no es una petición POST, redirigir al index
    header('Location: ../index.php');
    exit();
}