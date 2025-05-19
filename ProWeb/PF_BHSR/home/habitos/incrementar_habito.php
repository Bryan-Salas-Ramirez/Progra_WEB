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

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Actualizar la frecuencia actual del hábito
    $query = "UPDATE registro_habito 
            SET frecuencia_actual = frecuencia_actual + 1 
            WHERE id_habito = :id_habito 
            AND id_usuario = :id_usuario";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_habito', $id_habito, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();

    // Obtener la nueva frecuencia actual
    $query = "SELECT frecuencia_actual, l.nombre as lapso 
            FROM registro_habito rh
            JOIN lapso l ON rh.id_lapso = l.id_lapso
            WHERE id_habito = :id_habito 
            AND id_usuario = :id_usuario";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_habito', $id_habito, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Confirmar transacción
    $conn->commit();

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'frecuencia_actual' => $result['frecuencia_actual'],
        'lapso' => $result['lapso']
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la frecuencia del hábito'
    ]);
} 