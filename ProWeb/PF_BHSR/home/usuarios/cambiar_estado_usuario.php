<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir la conexión a la base de datos
require_once '../../includes/conexion.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] !== 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y decodificar los datos JSON
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit();
}

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Verificar si es el administrador principal
    $query = "SELECT id_usuario, id_estatus_us, id_rol FROM usuario WHERE id_usuario = :id_usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception('Usuario no encontrado');
    }

    // Verificar que no se intente desactivar el administrador principal
    if ($usuario['id_usuario'] == 1 && $usuario['id_rol'] == 1) {
        $_SESSION['error'] = 'No se puede desactivar el administrador principal';
        header('Location: gestionar_usuarios.php');
        exit();
    }

    // Cambiar el estado (1 a 2 o 2 a 1)
    $nuevo_estado = $usuario['id_estatus_us'] == 1 ? 2 : 1;
    
    // Actualizar el estado del usuario
    $query = "UPDATE usuario SET id_estatus_us = :estatus WHERE id_usuario = :id_usuario";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':estatus', $nuevo_estado, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $data['id_usuario'], PDO::PARAM_INT);
    $stmt->execute();

    // Confirmar transacción
    $conn->commit();

    // Enviar respuesta exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Estado del usuario actualizado correctamente',
        'nuevo_estado' => $nuevo_estado
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    
    // Enviar respuesta de error
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el estado del usuario: ' . $e->getMessage()
    ]);
} 