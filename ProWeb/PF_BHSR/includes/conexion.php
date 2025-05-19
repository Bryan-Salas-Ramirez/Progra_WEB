<?php
    // Configuración de la base de datos
    $host = 'sql210.infinityfree.com';
    $dbname = 'if0_39019432_metatracker_db';
    $usuario = 'if0_39019432';
    $clave = 'metatrack15';

    try {
        // Crear la conexión usando PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $clave);
        
        // Configurar el modo de errores para que PDO lance excepciones
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Configurar el modo de recuperación predeterminado para obtener arrays asociativos
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Configurar el modo de emulación de prepared statements
        $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        
    } catch (PDOException $e) {
        // En caso de error, mostrar un mensaje amigable
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
?>