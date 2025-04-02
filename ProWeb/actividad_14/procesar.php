<?php
    $validar = 0;

    //Crear Fórmula para no estar creando los botones cada if
    function mostrarBotonInicio() {
        echo "<center>
                <form action='index.html' method='get'>
                    <button type='submit'>Inicio</button>
                </form>
                </center>";
    }

    //Validar la variable nombre por si está vacía o no declarada
    if (isset($_POST['nombre'])) {
        $validar += 1;
        $nombre = $_POST['nombre'];
        if (!empty($nombre)) {
            $validar += 1;
        } else {
            die("Alerta!!! No puedes dejar campos vacíos" . mostrarBotonInicio());
        }
    } else {
        echo "Define la variable";
        mostrarBotonInicio();
        exit;
    }

    //Validar la variable apellido por si está vacía o no declarada
    if (isset($_POST['apellido'])) {
        $validar += 1;
        $apellido = $_POST['apellido'];
        if (!empty($apellido)) {
            $validar += 1;
        } else {
            die("Alerta!!! No puedes dejar campos vacíos" . mostrarBotonInicio());
        }
    } else {
        echo "Define la variable";
        mostrarBotonInicio();
        exit;
    }

    //Validar la variable nombre por si está vacía, no declarada o si no es un valor numérico
    if (isset($_POST['edad'])) {
        $validar += 1;
        $edad = $_POST['edad'];
        if (!empty($edad)) {
            if (!is_numeric($edad)) {
                die("Error: ¡El campo EDAD sólo permite números!" . mostrarBotonInicio());
            } else {
                $validar += 1;
            }
        } else {
            die("Alerta!!! No puedes dejar campos vacíos" . mostrarBotonInicio());
        }
    } else {
        die("Define la variable" . mostrarBotonInicio());
    }

    //Validar la variable nombre por si está vacía, no declarada o no cuenta con el formato de email
    if (isset($_POST['email'])) {
        $validar += 1;
        $email = $_POST['email'];
        if (!empty($email)) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validar += 1;
            } else {
                echo "La dirección de correo electrónico NO es válida <br>";
                mostrarBotonInicio();
                exit;
            }
        } else {
            echo "Alerta!!! No puedes dejar campos vacíos <br>";
            mostrarBotonInicio();
            exit;
        }
    } else {
        echo "Define la variable <br>";
        mostrarBotonInicio();
        exit;
    }

    // Validadr si todas las condiciones se cumplieron para imprimir los datos del usuario
    if ($validar == 8) {
        echo "<h2>Estimad@ $nombre $apellido, sus datos se han registrado correctamente.</h2><br> 
        <h2>Edad: $edad años</h2><br> 
        <h2>Correo: $email</h2><br>";
    }

    // Botón para regresar a la página principal
    mostrarBotonInicio();
?>