<?php
session_start();
require_once '../../includes/verificar_sesion.php';
require_once '../../includes/conexion.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y decodificar el JSON del cuerpo de la petición
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id_habito'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de hábito no proporcionado']);
    exit();
}

$id_habito = $data['id_habito'];
$id_usuario = $_SESSION['id_usuario'];
$nuevo_estado = isset($data['nuevo_estado']) ? $data['nuevo_estado'] : 2; // Por defecto, completado (2)

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Actualizar el estado del hábito
    $query = "UPDATE registro_habito 
            SET estatus_habito = :nuevo_estado 
            WHERE id_habito = :id_habito 
            AND id_usuario = :id_usuario";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_INT);
    $stmt->bindParam(':id_habito', $id_habito, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    // Verificar si se actualizó algún registro
    if ($stmt->rowCount() === 0) {
        throw new Exception('No se encontró el hábito o no tienes permiso para modificarlo');
    }

    // Confirmar transacción
    $conn->commit();

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Estado del hábito actualizado correctamente'
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el estado del hábito: ' . $e->getMessage()
    ]);
} 