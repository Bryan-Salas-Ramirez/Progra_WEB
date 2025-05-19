CREATE DATABASE metatrack_db;
USE metatrack_db;

DROP TABLE IF EXISTS estatus_usuario;
CREATE TABLE estatus_usuario (
  id_estatus_us INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(60)
) ENGINE = InnoDB;

INSERT INTO estatus_usuario (nombre) VALUES ('Activo');
INSERT INTO estatus_usuario (nombre) VALUES ('Inactivo');

DROP TABLE IF EXISTS rol;
CREATE TABLE rol (
  id_rol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(30) NOT NULL
) ENGINE = InnoDB;

INSERT INTO rol (nombre) VALUES ('Administrador');
INSERT INTO rol (nombre) VALUES ('Usuario');

DROP TABLE IF EXISTS usuario;
CREATE TABLE usuario (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(120) NOT NULL,
    a_paterno VARCHAR(60) NOT NULL,
    a_materno VARCHAR(60),
    correo VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    id_rol INT UNSIGNED NOT NULL,
    id_estatus_us INT UNSIGNED NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (id_estatus_us) REFERENCES estatus_usuario(id_estatus_us)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS categoria;
CREATE TABLE categoria (
	id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE
) ENGINE = InnoDB;

INSERT INTO categoria (nombre) VALUES
('Cuida tu salud'),
('Come mejor cada día'),
('Dedica tiempo a tu familia'),
('Aprovecha tu tiempo'),
('Mejora tus finanzas'),
('Impulsa tu carrera'),
('Aprende algo nuevo'),
('Mantén tu espacio en orden');

DROP TABLE IF EXISTS habito;
CREATE TABLE habito (
    id_habito INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL,
    id_categoria INT UNSIGNED NOT NULL,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
) ENGINE = InnoDB;

INSERT INTO habito (nombre, descripcion, id_categoria) VALUES
-- 1. Cuida tu salud
('Dormir 8 horas', 'Establecer una rutina de sueño adecuada para mejorar la salud general.', 1),
('Hacer ejercicio', 'Realizar actividad física moderada para mantener el cuerpo activo.', 1),
('Beber agua', 'Mantenerse hidratado para un buen funcionamiento corporal.', 1),
('Tomar una pausa para respirar', 'Dedicarse unos minutos a relajarse y reducir el estrés.', 1),
('Evitar el cigarro', 'Reducir o eliminar el tabaquismo para mejorar la salud respiratoria.', 1),

-- 2. Come mejor cada día
('Evitar bebidas azucaradas', 'Reemplazar refrescos y jugos por agua o infusiones naturales.', 2),
('Incluir frutas en el desayuno', 'Consumir una porción de fruta fresca por las mañanas.', 2),
('Preparar comida casera', 'Cocinar en casa para tener mayor control sobre los ingredientes.', 2),
('Reducir el consumo de frituras', 'Evitar alimentos fritos para mejorar la salud digestiva.', 2),
('No comer tarde en la noche', 'Establecer un horario saludable para las comidas nocturnas.', 2),

-- 3. Dedica tiempo a tu familia
('Cenar con la familia', 'Compartir tiempo en la mesa para fortalecer los lazos familiares.', 3),
('Conversar con tus hijos', 'Escuchar y hablar con los hijos sobre su día.', 3),
('Llamar a un familiar', 'Mantener contacto frecuente con familiares cercanos.', 3),
('Realizar actividades familiares', 'Organizar juegos o salidas para convivir juntos.', 3),
('Apagar pantallas durante las comidas', 'Evitar dispositivos para fomentar la conversación en familia.', 3),

-- 4. Aprovecha tu tiempo
('Planificar el día', 'Establecer metas y tareas para aumentar la productividad.', 4),
('Revisar pendientes', 'Organizar y ajustar las tareas no completadas.', 4),
('Evitar distracciones', 'Limitar el uso del celular o redes sociales mientras se trabaja.', 4),
('Tener un espacio ordenado para trabajar', 'Mantener el área de trabajo limpia y funcional.', 4),
('Establecer horarios para tus actividades', 'Crear rutinas con tiempos definidos para mejorar el orden.', 4),

-- 5. Mejora tus finanzas
('Registrar gastos', 'Llevar un control de cada gasto realizado para tomar mejores decisiones.', 5),
('Ahorrar una cantidad fija', 'Apartar dinero regularmente para metas futuras.', 5),
('Evitar compras impulsivas', 'Pensar antes de comprar para no afectar el presupuesto.', 5),
('Usar efectivo en vez de tarjeta', 'Controlar el gasto físico para evitar deudas innecesarias.', 5),
('Revisar tus finanzas', 'Evaluar ingresos y egresos con regularidad.', 5),

-- 6. Impulsa tu carrera
('Actualizar tu currículum', 'Revisar y mejorar tu CV periódicamente.', 6),
('Leer noticias de tu industria', 'Mantenerse informado sobre tendencias laborales.', 6),
('Mejorar una habilidad profesional', 'Practicar algo útil para tu trabajo actual o futuro.', 6),
('Organizar tu espacio de trabajo', 'Tener todo listo para trabajar de forma eficiente.', 6),
('Definir metas laborales', 'Establecer objetivos claros relacionados con tu trabajo.', 6),

-- 7. Aprende algo nuevo
('Leer libros', 'Incorporar la lectura como hábito de aprendizaje.', 7),
('Estudiar un idioma', 'Practicar vocabulario o gramática para avanzar en el idioma.', 7),
('Ver contenido educativo', 'Consumir material formativo en plataformas como YouTube.', 7),
('Seguir tutoriales', 'Aprender haciendo con guías paso a paso.', 7),
('Anotar lo que aprendes', 'Registrar reflexiones sobre lo nuevo aprendido.', 7),

-- 8. Mantén tu espacio en orden
('Hacer la cama', 'Empezar el día con una pequeña acción de orden personal.', 8),
('Ordenar el escritorio', 'Dejar limpio y organizado tu lugar de trabajo o estudio.', 8),
('Limpiar un espacio del hogar', 'Dividir tareas domésticas para mantener el orden.', 8),
('Revisar el closet', 'Eliminar ropa innecesaria y organizar lo que se usa.', 8),
('Lavar los trastes por la noche', 'Dejar la cocina limpia al final del día.', 8);

DROP TABLE IF EXISTS estatus_actividad;
CREATE TABLE estatus_actividad (
    id_estatus_ac INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE
) ENGINE = InnoDB;

INSERT INTO estatus_actividad (nombre) VALUES 
('Activo'),
('Completado'),
('Cancelado');

DROP TABLE IF EXISTS meta;
CREATE TABLE meta (
    id_meta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    descripcion VARCHAR(200),
    habitos_cumplidos INT UNSIGNED,
    estatus_meta INT UNSIGNED NOT NULL,
    fecha_inicio TIMESTAMP NOT NULL,
    fecha_fin TIMESTAMP NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (estatus_meta) REFERENCES estatus_actividad(id_estatus_ac)
) ENGINE = InnoDB;

DROP TABLE IF EXISTS lapso;
CREATE TABLE lapso (
    id_lapso INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
) ENGINE = InnoDB;

INSERT INTO lapso (nombre) VALUES ('Dia'), ('Semana'), ('Mes');

DROP TABLE IF EXISTS registro_habito;
CREATE TABLE registro_habito (
    id_registro INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    id_habito INT UNSIGNED NOT NULL,
    id_meta INT UNSIGNED,
    id_lapso INT UNSIGNED NOT NULL,
    frecuencia_objetivo INT UNSIGNED NOT NULL,
    frecuencia_actual INT UNSIGNED NOT NULL,
    estatus_habito INT UNSIGNED NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_habito) REFERENCES habito(id_habito),
    FOREIGN KEY (id_meta) REFERENCES meta(id_meta),
    FOREIGN KEY (id_lapso) REFERENCES lapso(id_lapso),
    FOREIGN KEY (estatus_habito) REFERENCES estatus_actividad(id_estatus_ac)
) ENGINE = InnoDB;