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

// Verificar que se recibieron todos los datos necesarios
if (!isset($_POST['id_registro']) || !isset($_POST['id_habito']) || 
    !isset($_POST['lapso']) || !isset($_POST['frecuencia'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit();
}

$id_registro = $_POST['id_registro'];
$id_habito = $_POST['id_habito'];
$id_lapso = $_POST['lapso'];
$frecuencia_objetivo = $_POST['frecuencia'];
$id_usuario = $_SESSION['id_usuario'];

try {
    // Iniciar transacción
    $conn->beginTransaction();

    // Actualizar el registro del hábito
    $query = "UPDATE registro_habito 
            SET id_lapso = :id_lapso,
                frecuencia_objetivo = :frecuencia_objetivo,
                frecuencia_actual = 0
            WHERE id_registro = :id_registro 
            AND id_habito = :id_habito 
            AND id_usuario = :id_usuario";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id_lapso', $id_lapso, PDO::PARAM_INT);
    $stmt->bindParam(':frecuencia_objetivo', $frecuencia_objetivo, PDO::PARAM_INT);
    $stmt->bindParam(':id_registro', $id_registro, PDO::PARAM_INT);
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
        'message' => 'Hábito actualizado correctamente'
    ]);

} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar el hábito: ' . $e->getMessage()
    ]);
} 