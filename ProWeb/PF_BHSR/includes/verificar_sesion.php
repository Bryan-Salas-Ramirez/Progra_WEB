<?php
// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Si no hay sesión activa, redirigir al login
    header('Location: ../index.php');
    exit();
}
?> 