CREATE TABLE ajustes_tipo (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(15) NOT NULL UNIQUE,
    tipo                            VARCHAR(25) NOT NULL
);

INSERT INTO ajustes_tipo(codigo, tipo) VALUES('int', 'Entero'),
                                                    ('float', 'Float'),
                                                    ('money', 'Money'),
                                                    ('string', 'String'),
                                                    ('json', 'JSON'),
                                                    ('boolean', 'Boolean');

CREATE TABLE ajustes_categoria (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(15) NOT NULL UNIQUE,
    categoria                       VARCHAR(30) NOT NULL
);

INSERT INTO ajustes_categoria(codigo, categoria) VALUES('general', 'General'),
                                                            ('agenda', 'Agenda'),
                                                            ('facturacion', 'Facturacion'),
                                                            ('seguridad', 'Seguridad'),
                                                            ('notificaciones', 'Notificaciones');

CREATE TABLE ajustes (
    id                              VARCHAR(100) PRIMARY KEY,
    descripcion                     VARCHAR(255) NOT NULL,
    valor                           TEXT NOT NULL,
    valor_defecto                   TEXT NOT NULL,
    categoria                       SMALLINT NOT NULL,
    tipo                            SMALLINT NOT NULL DEFAULT 4,
    f_actualizacion                 DATETIME NOT NULL,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    CONSTRAINT FK_ajustes_tipo FOREIGN KEY(tipo) REFERENCES ajustes_tipo(id),
    CONSTRAINT FK_ajustes_categoria FOREIGN KEY(categoria) REFERENCES ajustes_categoria(id)
);

CREATE INDEX idx_ajustes_categoria ON ajustes(categoria);

INSERT INTO ajustes(id, descripcion, valor, valor_defecto, categoria, tipo, f_actualizacion) VALUES('codigo_paciente', 'Codigo para la clave de pacientes.', 'PE', 'PE', 1, 4, NOW());
INSERT INTO ajustes(id, descripcion, valor, valor_defecto, categoria, tipo, f_actualizacion) VALUES('codigo_cliente', 'Codigo para la clave de clientes.', 'CE', 'CE', 1, 4, NOW());
INSERT INTO ajustes(id, descripcion, valor, valor_defecto, categoria, tipo, f_actualizacion) VALUES('agenda_intervalo_minutos', 'Intervalo de tiempo en minutos para busqueda de bloques de citas', '15', '15', 2, 1, NOW());

CREATE TABLE paises (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    pais                            VARCHAR(50) NOT NULL,
    abbr                            VARCHAR(5) DEFAULT NULL,
    lada                            SMALLINT DEFAULT NULL
);

CREATE TABLE estados (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    estado                          VARCHAR(80) NOT NULL,
    pais                            SMALLINT NOT NULL,
    CONSTRAINT FK_estados_pais FOREIGN KEY(pais) REFERENCES paises(id)
);

CREATE TABLE municipios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    municipio                       VARCHAR(80) NOT NULL,
    estado                          INT NOT NULL,
    CONSTRAINT FK_municipios_estado FOREIGN KEY(estado) REFERENCES estados(id)
);


CREATE TABLE colonias (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    colonia                         VARCHAR(80) NOT NULL,
    municipio                       INT NOT NULL,
    cp                              VARCHAR(5) DEFAULT NULL,
    CONSTRAINT FK_colonias_municipio FOREIGN KEY(municipio) REFERENCES municipios(id)
);

CREATE TABLE unidades (
    id                              VARCHAR(8) PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    unidad                          VARCHAR(40) NOT NULL
);

INSERT INTO unidades (id, codigo, unidad) VALUES
('PZA', 'pza', 'Pieza'),
('PAR', 'par', 'Par'),
('CAJ', 'caja', 'Caja'),
('PAQ', 'paq', 'Paquete'),
('BOL', 'bolsa', 'Bolsa'),
('ROL', 'rollo', 'Rollo'),
('FRA', 'frasco', 'Frasco'),
('BOT', 'botella', 'Botella'),
('AMP', 'amp', 'Ampolleta'),
('VIA', 'vial', 'Vial'),
('TUB', 'tubo', 'Tubo'),
('KIT', 'kit', 'Kit'),
('BLI', 'blister', 'Blíster'),
('SOB', 'sobre', 'Sobre'),
('LAT', 'lata', 'Lata'),

('ML',  'ml', 'Mililitro'),
('L',   'l', 'Litro'),
('CC',  'cc', 'Centímetro cúbico'),
('GOT', 'gotas', 'Gotas'),
('DL',  'dl', 'Decilitro'),

('G',   'g', 'Gramo'),
('MG',  'mg', 'Miligramo'),
('KG',  'kg', 'Kilogramo'),

('APL', 'aplic', 'Aplicación'),
('DOS', 'dosis', 'Dosis'),
('SES', 'sesion', 'Sesión'),
('CUR', 'curacion', 'Curación'),
('SER', 'serv', 'Servicio'),
('TRA', 'trat', 'Tratamiento'),

('GAS', 'gasa', 'Gasa'),
('COM', 'comp', 'Compresa'),
('HIS', 'hisopo', 'Hisopo'),
('CAM', 'campo', 'Campo quirúrgico'),
('JER', 'jeringa', 'Jeringa'),
('AGU', 'aguja', 'Aguja');

CREATE TABLE usuarios_tipos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(30) NOT NULL
);

INSERT INTO usuarios_tipos(codigo, tipo) VALUES('administrador', 'Administrador'),
                                                    ('recepcion', 'Recepción'),
                                                    ('supervisor', 'Supervisor'),
                                                    ('doctor', 'Doctor'),
                                                    ('finanzas', 'Finanzas'),
                                                    ('enfermero', 'Enfermero'),
                                                    ('caja', 'Caja');

CREATE TABLE puestos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    puesto                          VARCHAR(30)
);

INSERT INTO puestos(codigo, puesto) VALUES('recepcion', 'Recepción'),
                                                ('supervisor', 'Supervisor'),
                                                ('caja', 'Caja'),
                                                ('medico', 'Medico'),
                                                ('enfermero', 'Enfermero'),
                                                ('contabilidad', 'Contabilidad');

CREATE TABLE especialidades (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    especialidad                    VARCHAR(40) NOT NULL UNIQUE,
    descripcion                     VARCHAR(512) DEFAULT NULL
);

INSERT INTO especialidades(codigo, especialidad) VALUES('sin-especialidad', 'Sin Especialidad'),
                                                            ('medico-general', 'Medico General'),
                                                            ('podologo', 'Podologo');

CREATE TABLE generos (
    id                              VARCHAR(1) PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    genero                          VARCHAR(15) NOT NULL
);

INSERT INTO generos(id, codigo, genero) VALUES('N', 'N/D', 'N/D'), ('H', 'hombre', 'Hombre'), ('M', 'mujer', 'Mujer');

CREATE TABLE personal_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    estatus                         VARCHAR(30) NOT NULL
);

INSERT INTO personal_estatus(codigo, estatus) VALUES('active', 'Activo');
INSERT INTO personal_estatus(codigo, estatus) VALUES('not-working', 'Baja');
INSERT INTO personal_estatus(codigo, estatus) VALUES('suspended', 'Suspendido');
INSERT INTO personal_estatus(codigo, estatus) VALUES('vacations', 'Vacaciones');

CREATE TABLE personal (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    rfc                             VARCHAR(14) DEFAULT NULL,
    nombre                          VARCHAR(60) NOT NULL,
    paterno                         VARCHAR(40) DEFAULT NULL,
    materno                         VARCHAR(40) DEFAULT NULL,
    f_nacimiento                    DATE DEFAULT NULL,
    calle                           VARCHAR(120) DEFAULT NULL,
    num_ext                         VARCHAR(12) DEFAULT NULL,
    num_int                         VARCHAR(12) DEFAULT NULL,
    colonia                         INT DEFAULT NULL,
    email                           VARCHAR(255) DEFAULT NULL,
    curp                            VARCHAR(20) DEFAULT NULL,
    telefono                        VARCHAR(40) DEFAULT NULL,
    movil                         VARCHAR(40) DEFAULT NULL,
    genero                          VARCHAR(1) NOT NULL,
    puesto                          SMALLINT NOT NULL,
    estatus                         SMALLINT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_personal_colonia FOREIGN KEY(colonia) REFERENCES colonias(id),
    CONSTRAINT FK_personal_genero FOREIGN KEY(genero) REFERENCES generos(id),
    CONSTRAINT FK_personal_puesto FOREIGN KEY(puesto) REFERENCES puestos(id),
    CONSTRAINT FK_personal_estatus FOREIGN KEY(estatus) REFERENCES personal_estatus(id)
);

INSERT INTO personal(uuid, nombre, paterno, puesto, estatus, genero, f_registro) VALUES(10, 'Juan', 'Perez', 4, 1, 'H', NOW()),
                                                                                        (20, 'Eliver', 'Perez', 4, 1, 'H', NOW());

CREATE TABLE personal_profesional (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    cedula                          VARCHAR(12) DEFAULT NULL,
    especialidad                    SMALLINT NOT NULL,
    universidad                     VARCHAR(250) DEFAULT NULL,
    egreso                          SMALLINT DEFAULT NULL,
    universidad_municipio           INT DEFAULT NULL,
    color_agenda                    VARCHAR(7) NOT NULL DEFAULT '#07F',
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_personalprofesional_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_personalprofesional_especialidad FOREIGN KEY(especialidad) REFERENCES especialidades(id)
);

CREATE TABLE personal_altas (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    f_alta                          DATE NOT NULL,
    f_baja                          DATE DEFAULT NULL,
    razon_baja                      VARCHAR(512) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_personalaltas_personal FOREIGN KEY(personal) REFERENCES personal(id)
);

CREATE TABLE usuarios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    nombre                          VARCHAR(120) DEFAULT NULL,
    usuario                         VARCHAR(30) NOT NULL UNIQUE,
    password_hash                   VARCHAR(255) NOT NULL,
    tipo_usuario                    SMALLINT NOT NULL,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    f_registro                      DATETIME NOT NULL,
    f_ultima_conexion               DATETIME DEFAULT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_usuarios_tipo FOREIGN KEY(tipo_usuario) REFERENCES usuarios_tipos(id)
);

INSERT INTO usuarios(uuid, nombre, usuario, password_hash, tipo_usuario, activo, f_registro) 
                VALUES(10, 'Admin', 'admin', '$2y$10$MqUTuFBUs.OIhkWSAxL3A.RfbglmDA9Uy/vgfYNOUvs2kI0EkBaYK', 1, 1, NOW()),
                        (20, 'Juan', 'juan', '$2y$10$r/SCylnVyrMQ9kJybTpkj.UFvAOKpR6eRSw6/fgbs09Rw8Fe.RGGq', 4, 1, NOW()),
                        (30, 'Eliver', 'eliver', '$2y$10$r/SCylnVyrMQ9kJybTpkj.UFvAOKpR6eRSw6/fgbs09Rw8Fe.RGGq', 4, 1, NOW());

CREATE TABLE personal_usuarios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    usuario                         INT NOT NULL,
    activo                          TINYINT NOT NULL DEFAULT 1,
    f_registro                      DATETIME NOT NULL,
    f_removido                      DATETIME DEFAULT NULL,
    CONSTRAINT FK_personalusuarios_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_personalusuarios_usuario FOREIGN KEY(usuario) REFERENCES usuarios(id)
);

INSERT INTO personal_usuarios(personal, usuario, f_registro) VALUES(1, 2, NOW()),
                                                                    (2, 3, NOW());

CREATE TABLE personal_sueldos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    sueldo_anterior                 NUMERIC(18, 2) NOT NULL DEFAULT 0,
    sueldo_actual                   NUMERIC(18, 2) NOT NULL DEFAULT 0,
    actualizo                       INT NOT NULL,
    f_apartir_de                    DATE NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_personalsueldos_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_personalsueldos_actualizo FOREIGN KEY(actualizo) REFERENCES usuarios(id)
);

CREATE TABLE empresas (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    empresa                         VARCHAR(120) NOT NULL,
    domicilio                       VARCHAR(255) DEFAULT NULL,
    esta_empresa                    SMALLINT NOT NULL DEFAULT 0
);

INSERT INTO empresas(uuid, empresa, domicilio, esta_empresa) VALUES(X'30313866386333612d386630622d3762', 'Clinica 1', 'Domicilio Conocido 1', 1);
INSERT INTO empresas(uuid, empresa, domicilio, esta_empresa) VALUES(X'40313866386333612d386630622d3762', 'Clinica 2', 'Domicilio Conocido 2', 0);

CREATE TABLE permisos (
    id                              VARCHAR(30) PRIMARY KEY,
    permiso                         VARCHAR(255) NOT NULL,
    descripcion                     VARCHAR(1024) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL
);

CREATE TABLE permisos_usuarios (
    permiso                         VARCHAR(30) NOT NULL,
    usuario                         INT NOT NULL,
    empresa                         INT NOT NULL,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    valor                           SMALLINT NOT NULL DEFAULT 1,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_permisosusuarios_permiso FOREIGN KEY(permiso) REFERENCES permisos(id),
    CONSTRAINT FK_permisosusuarios_usuario FOREIGN KEY(usuario) REFERENCES usuarios(id),
    CONSTRAINT FK_permisosusuarios_empresa FOREIGN KEY(empresa) REFERENCES empresas(id)
);

CREATE TABLE permisos_usuarios_tipo (
    permiso                         VARCHAR(30) NOT NULL,
    tipo                            SMALLINT NOT NULL,
    empresa                         INT NOT NULL,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    valor                           SMALLINT NOT NULL DEFAULT 1,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_permisosusuariostipo_permiso FOREIGN KEY(permiso) REFERENCES permisos(id),
    CONSTRAINT FK_permisosusuariostipo_tipo FOREIGN KEY(tipo) REFERENCES usuarios_tipos(id),
    CONSTRAINT FK_permisosusuariostipo_empresa FOREIGN KEY(empresa) REFERENCES empresas(id)
);

CREATE TABLE usuarios_sesiones (
    id                              BINARY(16) PRIMARY KEY,
    usuario                         INT NOT NULL,

    token_hash                      BINARY(32) NOT NULL,

    f_registro                      DATETIME NOT NULL,
    ultima_actividad                DATETIME NOT NULL,
    expira_en                       DATETIME NOT NULL,
    destruida_en                    DATETIME NULL,

    ip                              VARCHAR(255),
    user_agent                      VARCHAR(255),
    dispositivo                     VARCHAR(255),

    motivo_cierre                   VARCHAR(255),

    CONSTRAINT FK_usuariossesiones_usuario FOREIGN KEY (usuario) REFERENCES usuarios(id)
);




CREATE TABLE plantillas_horarios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    nombre                          VARCHAR(80) NOT NULL,
    descripcion                     VARCHAR(255) DEFAULT NULL,
    usuario                         INT DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_plantillashorarios_usuario FOREIGN KEY(usuario) REFERENCES usuarios(id)
);

CREATE TABLE plantillas_horarios_detalles (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    plantilla                       INT NOT NULL,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    dia_semana                      SMALLINT NOT NULL,
    hora_inicio                     TIME NOT NULL,
    hora_fin                        TIME NOT NULL,
    CONSTRAINT FK_plantillashorariosdetalles_plantilla FOREIGN KEY(plantilla) REFERENCES plantillas_horarios(id)
);

CREATE TABLE horarios_laborales (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    consultas                       SMALLINT NOT NULL DEFAULT 1,
    plantilla                       INT DEFAULT NULL,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_horarioslaborales_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_horarioslaborales_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

INSERT INTO horarios_laborales(id, personal, consultas, activo, registro, f_registro, f_actualizacion) VALUES(1, 1, 1, 1, 1, NOW(), NOW()),
                                                                                                                (2, 2, 1, 1, 1, NOW(), NOW());


CREATE TABLE horarios_laborales_detalles (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    horario                         INT NOT NULL,
    dia_semana                      SMALLINT NOT NULL,
    hora_inicio                     SMALLINT NOT NULL,
    hora_fin                        SMALLINT NOT NULL,
    CONSTRAINT FK_horarioslaboralesdetalles_horario FOREIGN KEY(horario) REFERENCES horarios_laborales(id)
);

CREATE INDEX IDX_horarioslaboralesdetalles_horario_dia ON horarios_laborales_detalles(horario, dia_semana);

INSERT INTO horarios_laborales_detalles(horario, dia_semana, hora_inicio, hora_fin) VALUES(1, 1, 480, 780),
                                                                                                (1, 2, 480, 900),
                                                                                                (1, 3, 480, 900),
                                                                                                (1, 4, 480, 900),
                                                                                                (1, 5, 480, 900),
                                                                                                (1, 6, 540, 900),

                                                                                                (2, 1, 480, 1080),
                                                                                                (2, 2, 480, 1080),
                                                                                                (2, 3, 480, 1080),
                                                                                                (2, 4, 480, 1080),
                                                                                                (2, 5, 480, 1080),
                                                                                                (2, 6, 540, 1080);

CREATE TABLE bloqueos_agenda_razones (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    razon                           VARCHAR(30)
);

INSERT INTO bloqueos_agenda_razones(codigo, razon) VALUES('vacaciones', 'Vacaciones'),
                                                                ('enfermedad', 'Enfermedad'),
                                                                ('formacion', 'Formación'),
                                                                ('reunion', 'Reunión'),
                                                                ('suspension', 'Suspensión'),
                                                                ('otro', 'Otro');

CREATE TABLE bloqueos_agenda (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    titulo                          VARCHAR(30) NOT NULL,
    razon                           SMALLINT NOT NULL,
    otra_razon                      VARCHAR(255) DEFAULT NULL,
    f_inicio                        DATE NOT NULL,
    f_fin                           DATE NOT NULL,
    h_inicio                        SMALLINT NOT NULL,
    h_fin                           SMALLINT NOT NULL,
    todo_el_dia                     SMALLINT NOT NULL DEFAULT 0,
    observaciones                   VARCHAR(512) DEFAULT NULL,
    registro                        INT NOT NULL,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_bloqueosagenda_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_bloqueosagenda_razon FOREIGN KEY(razon) REFERENCES bloqueos_agenda_razones(id),
    CONSTRAINT FK_bloqueosagenda_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE INDEX IDX_bloqueosagenda_personal_inicio_fin ON bloqueos_agenda(personal, f_inicio, f_fin);

CREATE TABLE clientes (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    consecutivo                     INT DEFAULT NULL,
    clave                           VARCHAR(16) UNIQUE,
    es_empresa                      SMALLINT NOT NULL DEFAULT 0,
    empresa                         VARCHAR(255) DEFAULT NULL,
    nombre                          VARCHAR(100) NOT NULL,
    paterno                         VARCHAR(80) DEFAULT NULL,
    materno                         VARCHAR(80) DEFAULT NULL,
    curp                            VARCHAR(20) DEFAULT NULL,
    genero                          VARCHAR(1) NOT NULL,
    f_nacimiento                    DATE DEFAULT NULL,
    calle                           VARCHAR(120) DEFAULT NULL,
    num_ext                         VARCHAR(12) DEFAULT NULL,
    num_int                         VARCHAR(12) DEFAULT NULL,
    colonia                         INT DEFAULT NULL,
    cp                              VARCHAR(5) DEFAULT NULL,
    telefono                        VARCHAR(40) DEFAULT NULL,
    movil                           VARCHAR(40) DEFAULT NULL,
    email                           VARCHAR(255) DEFAULT NULL,
    adeudo                          NUMERIC(18, 2) NOT NULL DEFAULT 0,
    ultimo_pago                     NUMERIC(18, 2) NOT NULL DEFAULT 0,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    f_ultimo_pago                   DATETIME DEFAULT NULL,
    CONSTRAINT FK_clientes_genero FOREIGN KEY(genero) REFERENCES generos(id),
    CONSTRAINT FK_clientes_colonia FOREIGN KEY(colonia) REFERENCES colonias(id),
    CONSTRAINT FK_clientes_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE facturacion_tipo_contribuyente (
    id                              CHAR(1) PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(30) NOT NULL
);

INSERT INTO facturacion_tipo_contribuyente(id, codigo, tipo) VALUES('F', 'persona-fisica', 'Persona Fisica'), ('M', 'persona-moral', 'Persona Moral');

CREATE TABLE facturacion_regimen (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    tipo                            CHAR(1) DEFAULT NULL,
    codigo_sat                      VARCHAR(8) NOT NULL,
    regimen                         VARCHAR(255) NOT NULL,
    CONSTRAINT FK_facturacionregimen_tipo FOREIGN KEY(tipo) REFERENCES facturacion_tipo_contribuyente(id)
);

INSERT INTO facturacion_regimen(codigo, tipo, codigo_sat, regimen) VALUES('persona-moral', 'M', '601', 'REGIMEN GENERAL DE LEY PERSONAS MORALES'),
                                                                            ('persona-simp-moral', 'M', '602', 'RÉGIMEN SIMPLIFICADO DE LEY PERSONAS MORALES'),
                                                                            ('persona-moral-no-luc', 'M', '603', 'PERSONAS MORALES CON FINES NO LUCRATIVOS'),
                                                                            ('peq-contribuyente', 'F', '604', 'RÉGIMEN DE PEQUEÑOS CONTRIBUYENTES'),
                                                                            ('sueldos-salarios', 'F', '605', 'RÉGIMEN DE SUELDOS Y SALARIOS E INGRESOS ASIMILADOS A SALARIOS'),
                                                                            ('arrendamiento', 'F', '606', 'RÉGIMEN DE ARRENDAMIENTO'),
                                                                            ('enajenacion', 'F', '607', 'RÉGIMEN DE ENAJENACIÓN O ADQUISICIÓN DE BIENES'),
                                                                            ('demas-ingresos', 'F', '608', 'RÉGIMEN DE LOS DEMÁS INGRESOS'),
                                                                            ('consolidacion', 'M', '609', 'RÉGIMEN DE CONSOLIDACIÓN'),
                                                                            ('extranjeros-sin-est', NULL, '610', 'RÉGIMEN RESIDENTES EN EL EXTRANJERO SIN ESTABLECIMIENTO PERMANENTE EN MÉXICO'),
                                                                            ('dividendos', 'F', '611', 'RÉGIMEN DE INGRESOS POR DIVIDENDOS (SOCIOS Y ACCIONISTAS)'),
                                                                            ('actividad-empresarial', 'F', '612', 'RÉGIMEN DE LAS PERSONAS FÍSICAS CON ACTIVIDADES EMPRESARIALES Y PROFESIONALES'),
                                                                            ('int-act-empresarial', 'F', '613', 'RÉGIMEN INTERMEDIO DE LAS PERSONAS FÍSICAS CON ACTIVIDADES EMPRESARIALES'),
                                                                            ('intereses', 'F', '614', 'RÉGIMEN DE LOS INGRESOS POR INTERESES'),
                                                                            ('premios', 'F', '615', 'RÉGIMEN DE LOS INGRESOS POR OBTENCIÓN DE PREMIOS'),
                                                                            ('sin-obligaciones', NULL, '616', 'SIN OBLIGACIONES FISCALES'),
                                                                            ('pemex', 'M', '617', 'PEMEX'),
                                                                            ('simplificado-fisicas', 'F', '618', 'RÉGIMEN SIMPLIFICADO DE LEY PERSONAS FÍSICAS'),
                                                                            ('prestamos', 'F', '619', 'INGRESOS POR LA OBTENCIÓN DE PRÉSTAMOS'),
                                                                            ('produccion', 'M', '620', 'SOCIEDADES COOPERATIVAS DE PRODUCCIÓN QUE OPTAN POR DIFERIR SUS INGRESOS.'),
                                                                            ('rif', 'F', '621', 'RÉGIMEN DE INCORPORACIÓN FISCAL'),
                                                                            ('agricolas', 'F', '622', 'RÉGIMEN DE ACTIVIDADES AGRÍCOLAS, GANADERAS, SILVÍCOLAS Y PESQUERAS PM'),
                                                                            ('opcion-sociedades', 'M', '623', 'RÉGIMEN DE OPCIONAL PARA GRUPOS DE SOCIEDADES'),
                                                                            ('coordinados', 'M', '624', 'RÉGIMEN DE LOS COORDINADOS'),
                                                                            ('plataformas', 'F', '625', 'RÉGIMEN DE LAS ACTIVIDADES EMPRESARIALES CON INGRESOS A TRAVÉS DE PLATAFORMAS TECNOLÓGICAS.'),
                                                                            ('resico', 'F', '626', 'RÉGIMEN SIMPLIFICADO DE CONFIANZA');

CREATE TABLE clientes_facturacion (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cliente                         INT NOT NULL,
    regimen                         SMALLINT NOT NULL,
    rfc                             VARCHAR(18) NOT NULL,
    razon_social                    VARCHAR(255) NOT NULL,
    calle                           VARCHAR(120) DEFAULT NULL,
    num_ext                         VARCHAR(12) DEFAULT NULL,
    num_int                         VARCHAR(12) DEFAULT NULL,
    colonia                         INT DEFAULT NULL,
    cp                              CHAR(5) NOT NULL,
    telefono                        VARCHAR(40) DEFAULT NULL,
    email                           VARCHAR(255) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    f_ultima_factura                DATETIME DEFAULT NULL,
    CONSTRAINT FK_clientesfacturacion_cliente FOREIGN KEY(cliente) REFERENCES clientes(id),
    CONSTRAINT FK_clientesfacturacion_regimen FOREIGN KEY(regimen) REFERENCES facturacion_regimen(id),
    CONSTRAINT FK_clientesfacturacion_colonia FOREIGN KEY(colonia) REFERENCES colonias(id)
);

CREATE TABLE parentescos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) UNIQUE NOT NULL,
    descripcion                     VARCHAR(100) NOT NULL
);

INSERT INTO parentescos(codigo, descripcion) VALUES('self', 'El cliente es el mismo paciente'),
                                                        ('spouse', 'Esposo / Esposa'),
                                                        ('parent', 'Padre / Madre'),
                                                        ('child', 'Hijo / Hija'),
                                                        ('friend', 'Amigo / Amiga'),
                                                        ('employer', 'Empleador'),
                                                        ('tutor', 'Tutor legal');

CREATE TABLE pacientes (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    consecutivo                     INT DEFAULT NULL,
    clave                           VARCHAR(16) UNIQUE,
    nombre                          VARCHAR(60) NOT NULL,
    paterno                         VARCHAR(60) DEFAULT NULL,
    materno                         VARCHAR(60) DEFAULT NULL,
    curp                            VARCHAR(20) DEFAULT NULL,
    f_nacimiento                    DATE DEFAULT NULL,
    genero                          VARCHAR(1) NOT NULL,
    telefono                        VARCHAR(40) DEFAULT NULL,
    movil                           VARCHAR(40) DEFAULT NULL,
    email                           VARCHAR(255) DEFAULT NULL,
    calle                           VARCHAR(120) DEFAULT NULL,
    num_ext                         VARCHAR(12) DEFAULT NULL,
    num_int                         VARCHAR(12) DEFAULT NULL,
    colonia                         INT DEFAULT NULL,
    cp                              VARCHAR(5) DEFAULT NULL,
    medicamentos                    VARCHAR(2048) DEFAULT NULL,
    suplementos                     VARCHAR(2048) DEFAULT NULL,
    antecedentes_familiares         VARCHAR(2048) DEFAULT NULL,
    observaciones_generales         VARCHAR(2048) DEFAULT NULL,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    f_ultima_visita                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_pacientes_genero FOREIGN KEY(genero) REFERENCES generos(id),
    CONSTRAINT FK_pacientes_colonia FOREIGN KEY(colonia) REFERENCES colonias(id),
    CONSTRAINT FK_pacientes_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE clientes_pacientes (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cliente                         INT NOT NULL,
    paciente                        INT NOT NULL,
    parentesco                      SMALLINT NOT NULL,
    principal                       SMALLINT NOT NULL DEFAULT 0,
    registro                        INT NOT NULL,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT UK_clientespaciente_paciente UNIQUE(cliente, paciente),
    CONSTRAINT FK_clientespacientes_cliente FOREIGN KEY(cliente) REFERENCES clientes(id),
    CONSTRAINT FK_clientespacientes_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_clientespacientes_parentesco FOREIGN KEY(parentesco) REFERENCES parentescos(id),
    CONSTRAINT FK_clientespacientes_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE antecedentes_categorias (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) UNIQUE NOT NULL,
    nombre                          VARCHAR(100) NOT NULL,
    descripcion                     VARCHAR(512) DEFAULT NULL
);

INSERT INTO antecedentes_categorias(codigo, nombre) VALUES('personal_patologico', 'Antecedentes personales patológicos'),
                                                                ('quirurgico', 'Antecedentes quirúrgicos'),
                                                                ('alergico', 'Antecedentes alérgicos'),
                                                                ('familiar', 'Antecedentes familiares'),
                                                                ('no_patologico', 'Antecedentes no patológicos'),
                                                                ('gineco', 'Gineco-obstétricos'),
                                                                ('infecciones', 'Antecedentes infecciosos');

CREATE TABLE antecedentes_tipos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    categoria                       SMALLINT NOT NULL,
    codigo                          VARCHAR(40) UNIQUE NOT NULL,
    nombre                          VARCHAR(100) NOT NULL,
    descripcion                     VARCHAR(512) DEFAULT NULL,
    CONSTRAINT FK_tiposantecedentes_categoria FOREIGN KEY (categoria) REFERENCES antecedentes_categorias(id)
);

INSERT INTO antecedentes_tipos(categoria, codigo, nombre) VALUES(1, 'diabetes-mellitus', 'Diabetes mellitus'),
                                                                    (1, 'hipertension-arterial', 'Hipertensión arterial'),
                                                                    (1, 'dislipidemia', 'Dislipidemia (colesterol / triglicéridos altos)'),
                                                                    (1, 'cardiopatia', 'Cardiopatía'),
                                                                    (1, 'arritmias', 'Arritmias'),
                                                                    (1, 'insuficiencia-renal', 'Insuficiencia renal'),
                                                                    (1, 'insuficiencia-hepatica', 'Insuficiencia hepática'),
                                                                    (1, 'asma', 'Asma'),
                                                                    (1, 'epoc', 'EPOC'),
                                                                    (1, 'tiroides', 'Enfermedad tiroidea (hipotiroidismo / hipertiroidismo)'),
                                                                    (1, 'epilepsia', 'Epilepsia'),
                                                                    (1, 'cerebrovascular', 'Enfermedad cerebrovascular (EVC / derrame)'),
                                                                    (1, 'coagulacion', 'Trastornos de coagulación'),
                                                                    (1, 'cancer', 'Cáncer (neoplasia)'),
                                                                    (1, 'vih', 'VIH'),
                                                                    (1, 'hepatitis', 'Hepatitis'),
                                                                    (1, 'tuberculosis', 'Tuberculosis'),
                                                                    (1, 'artritis', 'Artritis / enfermedades reumatológicas'),
                                                                    (1, 'autoinmune', 'Enfermedades autoinmunes'),
                                                                    (2, 'cirugia-previa', 'Cirugías previas'),
                                                                    (2, 'cirugia-cardiovascular', 'Cirugía cardiovascular'),
                                                                    (2, 'cirugia-abdominal', 'Cirugía abdominal'),
                                                                    (2, 'cesarea', 'Cesárea'),
                                                                    (2, 'apendicectomia', 'Apendicectomía'),
                                                                    (2, 'colecistectomia', 'Colecistectomía'),
                                                                    (2, 'protesis', 'Prótesis / implantes'),
                                                                    (2, 'transplante', 'Transplante'),
                                                                    (3, 'alergia-medicamentos', 'Alergia a medicamentos'),
                                                                    (3, 'alergia-alimentos', 'Alergia a alimentos'),
                                                                    (3, 'alergia-anestesicos', 'Alergia a anestésicos'),
                                                                    (3, 'alergia-latex', 'Alergia a látex'),
                                                                    (3, 'alergia-otros', 'Otras alergias'),
                                                                    (4, 'diabetes-familiar', 'Diabetes familiar'),
                                                                    (4, 'hipertension-familiar', 'Hipertensión familiar'),
                                                                    (4, 'cardiopatia-familiar', 'Cardiopatía familiar'),
                                                                    (4, 'cancer-familiar', 'Cáncer familiar'),
                                                                    (4, 'hereditarias', 'Enfermedades hereditarias'),
                                                                    (5, 'tabaquismo', 'Tabaquismo'),
                                                                    (5, 'alcoholismo', 'Alcoholismo'),
                                                                    (5, 'drogadiccion', 'Drogadicción'),
                                                                    (5, 'sedentarismo', 'Sedentarismo'),
                                                                    (5, 'obesidad', 'Obesidad'),
                                                                    (5, 'actividad-fisica-regular', 'Actividad física regular'),
                                                                    (5, 'exposicion-laboral-riesgos', 'Exposición laboral a riesgos'),
                                                                    (6, 'embarazo', 'Embarazo actual'),
                                                                    (6, 'embarazo-previo', 'Embarazos previos'),
                                                                    (6, 'parto', 'Partos'),
                                                                    (6, 'aborto', 'Abortos'),
                                                                    (6, 'cesareas', 'Cesáreas'),
                                                                    (6, 'complicaciones-obstetricas', 'Complicaciones obstétricas'),
                                                                    (6, 'menopausia', 'Menopausia'),
                                                                    (7, 'covid', 'COVID-19'),
                                                                    (7, 'influenza-recurrente', 'Influenza recurrente'),
                                                                    (7, 'infecciones-respiratorias-frecuentes', 'Infecciones respiratorias frecuentes'),
                                                                    (7, 'infecciones-urinarias-frecuentes', 'Infecciones urinarias recurrentes');

CREATE TABLE pacientes_antecedentes (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    paciente                        INT NOT NULL,
    tipo_antecedente                SMALLINT NOT NULL,
    diagnostico                     VARCHAR(512) DEFAULT NULL,
    fecha_diagnostico               DATE DEFAULT NULL,
    tratamiento_actual              VARCHAR(512) DEFAULT NULL,
    observaciones                   VARCHAR(1024) DEFAULT NULL,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_pacientesantecedentes_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_pacientesantecedentes_tipo FOREIGN KEY(tipo_antecedente) REFERENCES antecedentes_tipos(id),
    CONSTRAINT FK_pacientesantecedentes_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE articulos_categoria (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    categoria                       VARCHAR(80) NOT NULL,
    descripcion                     VARCHAR(255) DEFAULT NULL
);

CREATE TABLE articulos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    clave                           VARCHAR(12) NOT NULL UNIQUE,
    codigo_barras                   VARCHAR(32) NOT NULL UNIQUE,
    nombre                          VARCHAR(100) NOT NULL,
    nombre_ticket                   VARCHAR(32) NOT NULL,
    categoria                       SMALLINT NOT NULL,
    descripcion                     VARCHAR(255) DEFAULT NULL,
    unidad_uso                      VARCHAR(8) NOT NULL DEFAULT 1,
    unidad_compra                   VARCHAR(8) NOT NULL DEFAULT 1,
    factor_conversion               NUMERIC(12, 4) NOT NULL DEFAULT 1,
    factor_conversion_venta         NUMERIC(12, 4) NOT NULL DEFAULT 1,
    costo_unidad                    NUMERIC(18, 2) NOT NULL DEFAULT 0,
    minimo_inventario               NUMERIC(12, 4) NOT NULL DEFAULT 0,
    habilitado_venta                SMALLINT NOT NULL DEFAULT 1,
    activo                          SMALLINT NOT NULL DEFAULT 1,
    registro                        INT NOT NULL,
    f_registro                    DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_articulos_categoria FOREIGN KEY(categoria) REFERENCES articulos_categoria(id),
    CONSTRAINT FK_articulos_registro FOREIGN KEY(registro) REFERENCES usuarios(id),
    CONSTRAINT FK_articulos_unidaduso FOREIGN KEY(unidad_uso) REFERENCES unidades(id),
    CONSTRAINT FK_articulos_unidadcompra FOREIGN KEY(unidad_compra) REFERENCES unidades(id)
);

CREATE TABLE servicios (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    servicio                        VARCHAR(120) NOT NULL,
    descripcion                     VARCHAR(500) DEFAULT NULL,
    duracion_min                    SMALLINT NOT NULL,
    costo_base                      NUMERIC(18,2) NOT NULL,
    requiere_material               BOOLEAN NOT NULL DEFAULT 0,
    es_procedimiento                BOOLEAN NOT NULL DEFAULT 0,
    activo                          BOOLEAN NOT NULL DEFAULT 1
);

INSERT INTO servicios (codigo, servicio, descripcion, duracion_min, costo_base, requiere_material, es_procedimiento) VALUES

('consulta-general',        'Consulta General Podológica',        'Valoración inicial del estado del pie y diagnóstico básico',                  30, 500, 0, 0),
('consulta-seguimiento',    'Consulta de Seguimiento',             'Revisión de evolución de tratamiento o control posterior',                   20, 350, 0, 0),

('limpieza-podologica',     'Limpieza Podológica',                 'Corte de uñas, limado, limpieza de hiperqueratosis leve',                     45, 700, 1, 0),
('corte-unas-especial',     'Corte Especializado de Uñas',         'Corte clínico para uñas engrosadas o deformadas',                              30, 400, 0, 0),

('una-encarnada',           'Tratamiento de Uña Encarnada',        'Retiro parcial de espícula ungueal',                                            60, 1200, 1, 1),
('onicomicosis-control',    'Control de Onicomicosis',              'Limpieza y control de uñas con hongo',                                          45, 650, 1, 0),
('hiperqueratosis',         'Retiro de Hiperqueratosis',            'Eliminación de durezas profundas',                                              40, 600, 1, 0),
('helomas',                 'Eliminación de Helomas (Callos)',      'Retiro de callosidades nucleadas',                                              40, 650, 1, 1),

('valoracion-pie-diabetico','Valoración de Pie Diabético',          'Evaluación de riesgo y cuidado especializado',                                  30, 550, 0, 0),
('curacion-pie-diabetico',  'Curación Pie Diabético',               'Curación de lesiones en paciente diabético',                                    45, 750, 1, 1),

('curacion-simple',         'Curación Simple',                      'Limpieza y vendaje de lesión leve',                                             20, 300, 1, 0),
('curacion-avanzada',       'Curación Avanzada',                    'Curación de herida con mayor profundidad o riesgo',                             40, 600, 1, 1),

('valoracion-ortesis',      'Valoración para Órtesis',              'Evaluación para colocación de correctores ungueales',                           30, 400, 0, 0),
('colocacion-ortesis',      'Colocación de Órtesis Ungueal',        'Instalación de corrector para uña encarnada',                                   45, 900, 1, 1),

('terapia-laser-hongos',    'Terapia Láser para Hongos',            'Sesión de tratamiento láser para onicomicosis',                                 30, 1000, 1, 1),
('deslaminacion-ungueal',   'Deslaminación Ungueal',                'Reducción mecánica de grosor en uñas',                                          30, 500, 1, 0),

('pedicure-clinico',        'Pedicure Clínico',                     'Servicio estético con enfoque en salud del pie',                                60, 800, 1, 0);

CREATE TABLE personal_servicios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    personal                        INT NOT NULL,
    servicio                        SMALLINT NOT NULL,
    costo                           NUMERIC(18,2) NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_baja                          DATETIME DEFAULT NULL,
    CONSTRAINT FK_personalservicios_personal FOREIGN KEY (personal) REFERENCES personal(id),
    CONSTRAINT FK_personalservicios_servicio FOREIGN KEY (servicio) REFERENCES servicios(id)
);



INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 1, 500, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 4, 600, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 6, 840, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 7, 920, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 11, 300, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(1, 15, 1400, NOW());


INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(2, 1, 450, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(2, 4, 660, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(2, 6, 790, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(2, 11, 400, NOW());
INSERT INTO personal_servicios(personal, servicio, costo, f_registro) VALUES(2, 15, 1100, NOW());



CREATE TABLE indicaciones_tipo (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(20) NOT NULL
);

INSERT INTO indicaciones_tipo(codigo, tipo) VALUES('previa', 'Previa'), ('posterior', 'Posterior');

CREATE TABLE indicaciones (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    tipo                            SMALLINT NOT NULL,
    descripcion                     VARCHAR(500) NOT NULL,
    activo                          BOOLEAN NOT NULL DEFAULT 1,
    CONSTRAINT FK_indicaciones_tipo FOREIGN KEY(tipo) REFERENCES indicaciones_tipo(id)
);

INSERT INTO indicaciones(codigo, tipo, descripcion) VALUES('pies-limpios', 1, 'Acudir con pies limpios y sin esmalte'),
                                                                ('no-aplicar-crema', 1, 'No aplicar cremas 24h antes'),
                                                                ('no-mojar-area-tratada', 1, 'No mojar el área tratada por 12 horas'),
                                                                ('aplicar-medicamento', 1, 'Aplicar medicamento tópico indicado');

CREATE TABLE servicios_indicaciones (
    servicio                        SMALLINT NOT NULL,
    indicacion                      SMALLINT NOT NULL,
    obligatoria                     BOOLEAN NOT NULL DEFAULT 1,
    CONSTRAINT PK_serviciosindicaciones PRIMARY KEY (servicio, indicacion),
    CONSTRAINT FK_serviciosindicaciones_servicio FOREIGN KEY (servicio) REFERENCES servicios(id),
    CONSTRAINT FK_serviciosindicaciones_indicacion FOREIGN KEY (indicacion) REFERENCES indicaciones(id)
);

CREATE TABLE servicios_articulos (
    servicio                        SMALLINT NOT NULL,
    articulo                        INT NOT NULL,
    cantidad_base                   NUMERIC(18,2) NOT NULL,
    CONSTRAINT PK_serviciosarticulos PRIMARY KEY (servicio, articulo),
    CONSTRAINT FK_serviciosarticulos_servicio FOREIGN KEY (servicio) REFERENCES servicios(id),
    CONSTRAINT FK_serviciosarticulos_articulo FOREIGN KEY (articulo) REFERENCES articulos(id)
);

CREATE TABLE citas_asuntos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    asunto                          VARCHAR(40) NOT NULL
);

INSERT INTO citas_asuntos(codigo, asunto) VALUES('consulta', 'Consulta'),
                                                    ('seguimiento', 'Seguimiento'),
                                                    ('tratamiento', 'Tratamiento');

CREATE TABLE citas_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    estatus                         VARCHAR(40) NOT NULL,
    color                           VARCHAR(20) DEFAULT NULL
);

INSERT INTO citas_estatus(codigo, estatus, color) VALUES('agendada', 'Cita Agendada', '#AAAAAA'),
                                                        ('rechazada', 'Cita Rechazada', '#B22222'),
                                                        ('en_espera', 'En Espera', '#FA8B0C'),
                                                        ('en_proceso', 'En Proceso', '#5840FF'),
                                                        ('no_presento', 'No se presento', '#E9D502'),
                                                        ('finalizada', 'Cita Finalizada', '#3147D3'),
                                                        ('cancelada', 'Cita Cancelada', '#8E1B1B');

CREATE TABLE citas_formas (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    forma                           VARCHAR(40) NOT NULL
);

INSERT INTO citas_formas(codigo, forma) VALUES('presencial', 'Presencial'),
                                                    ('telefonica', 'Teléfono'),
                                                    ('correo', 'E-Mail'),
                                                    ('whatsapp', 'WhatsApp'),
                                                    ('agenda_digital', 'Agenda Digital');

CREATE TABLE citas (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    paciente                        INT NOT NULL,
    asunto                          SMALLINT DEFAULT NULL,
    forma                           SMALLINT DEFAULT NULL,
    descripcion                     TEXT DEFAULT NULL,
    motivo_consulta                 TEXT DEFAULT NULL,
    fecha                           DATE NOT NULL,
    h_inicio                        SMALLINT DEFAULT NULL,
    duracion                        SMALLINT NOT NULL,
    h_fin                           SMALLINT DEFAULT NULL,
    estatus                         SMALLINT NOT NULL,
    registro                        INT DEFAULT NULL,
    costo                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    adeudo                          NUMERIC(18, 2) NOT NULL DEFAULT 0,
    pagado                          NUMERIC(18, 2) NOT NULL DEFAULT 0,
    bonificacion                    NUMERIC(18, 2) NOT NULL DEFAULT 0,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME DEFAULT NULL,
    CONSTRAINT FK_citas_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_citas_asunto FOREIGN KEY(asunto) REFERENCES citas_asuntos(id),
    CONSTRAINT FK_citas_forma FOREIGN KEY(forma) REFERENCES citas_formas(id),
    CONSTRAINT FK_citas_estatus FOREIGN KEY(estatus) REFERENCES citas_estatus(id),
    CONSTRAINT FK_citas_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE citas_bloques_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    estatus                         VARCHAR(40) NOT NULL
);

INSERT INTO citas_bloques_estatus(codigo, estatus) VALUES('agendada', 'Cita Agendada'),
                                                                ('rechazada', 'Cita Rechazada'),
                                                                ('en_espera', 'En Espera'),
                                                                ('en_proceso', 'En Proceso'),
                                                                ('no_presento', 'No se presento'),
                                                                ('finalizada', 'Cita Finalizada'),
                                                                ('cancelada', 'Cita Cancelada');

CREATE TABLE citas_bloques (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cita                            INT NOT NULL,
    personal                        INT NOT NULL,
    servicio                        SMALLINT NOT NULL,
    descripcion                     TEXT DEFAULT NULL,
    orden                           SMALLINT NOT NULL DEFAULT 1,
    h_inicio                        SMALLINT NOT NULL,
    h_fin                           SMALLINT NOT NULL,
    duracion                        SMALLINT NOT NULL,
    estatus                         SMALLINT NOT NULL,
    CONSTRAINT FK_citasbloques_cita FOREIGN KEY (cita) REFERENCES citas(id),
    CONSTRAINT FK_citasbloques_personal FOREIGN KEY (personal) REFERENCES personal(id),
    CONSTRAINT FK_citasbloques_servicio FOREIGN KEY (servicio) REFERENCES servicios(id)
);

CREATE INDEX IDX_citasbloques_servicio ON citas_bloques(personal, servicio);
CREATE INDEX IDX_citasbloques_inicio ON citas_bloques(personal, h_inicio);
CREATE INDEX IDX_citasbloques_fin ON citas_bloques(personal, h_fin);

CREATE TABLE citas_servicios (
    cita                            INT NOT NULL,
    servicio                        SMALLINT NOT NULL,
    personal                        INT NOT NULL,
    costo                           NUMERIC(18,2) NOT NULL,
    bonificacion                    NUMERIC(18,2) NOT NULL DEFAULT 0,
    CONSTRAINT PK_citasservicios PRIMARY KEY (cita, servicio),
    CONSTRAINT FK_citasservicios_cita FOREIGN KEY (cita) REFERENCES citas(id),
    CONSTRAINT FK_citasservicios_servicio FOREIGN KEY (servicio) REFERENCES servicios(id),
    CONSTRAINT FK_citasservicios_personal FOREIGN KEY (personal) REFERENCES personal(id)
);

CREATE TABLE citas_servicios_articulos (
    cita                            INT NOT NULL,
    servicio                        SMALLINT NOT NULL,
    articulo                        INT NOT NULL,
    cantidad_utilizada              NUMERIC(18,2) NOT NULL,
    CONSTRAINT PK_citasserviciosarticulos PRIMARY KEY (cita, servicio, articulo),
    CONSTRAINT FK_citasserviciosarticulos_cita FOREIGN KEY (cita) REFERENCES citas(id),
    CONSTRAINT FK_citasserviciosarticulos_servicio FOREIGN KEY (servicio) REFERENCES servicios(id),
    CONSTRAINT FK_citasserviciosarticulos_articulo FOREIGN KEY (articulo) REFERENCES articulos(id)
);

CREATE TABLE tipos_pies (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL
);

CREATE TABLE tipos_pulso (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL
);

CREATE TABLE tipos_temperatura_pie (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL
);

CREATE TABLE tipos_sensibilidad (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL
);

CREATE TABLE formula_metatarsal (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    formula                            VARCHAR(60) NOT NULL
);

CREATE TABLE tipos_coloracion_pie (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL
);

CREATE TABLE exploracion_podologica (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    paciente                        INT NOT NULL,
    cita                            INT NOT NULL,
    personal                        INT NOT NULL,
    tipo_pie                        SMALLINT DEFAULT NULL,
    formula_metatarsal              SMALLINT DEFAULT NULL,
    alteraciones_marcha             VARCHAR(255) DEFAULT NULL,
    pulso_pedio_derecho             SMALLINT DEFAULT NULL,
    pulso_pedio_izquierdo           SMALLINT DEFAULT NULL,
    sensibilidad_derecho            SMALLINT DEFAULT NULL,
    sensibilidad_izquierdo          SMALLINT DEFAULT NULL,
    temperatura_pies                SMALLINT DEFAULT NULL,
    coloracion_pies                 SMALLINT DEFAULT NULL,
    observaciones                   TEXT DEFAULT NULL,
    recomendaciones                 TEXT DEFAULT NULL,
    f_exploracion                   DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_exploracionpodologica_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_exploracionpodologica_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_exploracionpodologica_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_exploracionpodologica_tipopie FOREIGN KEY(tipo_pie) REFERENCES tipos_pies(id),
    CONSTRAINT FK_exploracionpodologica_formulametatarsal FOREIGN KEY(formula_metatarsal) REFERENCES formula_metatarsal(id),
    CONSTRAINT FK_exploracionpodologica_pulsopedioderecho FOREIGN KEY(pulso_pedio_derecho) REFERENCES tipos_pulso(id),
    CONSTRAINT FK_exploracionpodologica_puslopedioizquierdo FOREIGN KEY(pulso_pedio_izquierdo) REFERENCES tipos_pulso(id),
    CONSTRAINT FK_exploracionpodologica_sensibilidadderecho FOREIGN KEY(sensibilidad_derecho) REFERENCES tipos_sensibilidad(id),
    CONSTRAINT FK_exploracionpodologica_sensibilidadizquierdo FOREIGN KEY(sensibilidad_izquierdo) REFERENCES tipos_sensibilidad(id),
    CONSTRAINT FK_exploracionpodologica_temperaturapies FOREIGN KEY(temperatura_pies) REFERENCES tipos_temperatura_pie(id),
    CONSTRAINT FK_exploracionpodologica_coloracionpies FOREIGN KEY(coloracion_pies) REFERENCES tipos_coloracion_pie(id)
);


CREATE TABLE parametros_medicos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    parametro                       VARCHAR(80) NOT NULL,
    descripcion                     VARCHAR(1024) DEFAULT NULL,
    unidad                          VARCHAR(8) NOT NULL,
    minimo                          NUMERIC(12, 8) NOT NULL DEFAULT 0,
    recomendado                     NUMERIC(12, 8) NOT NULL DEFAULT 0,
    maximo                          NUMERIC(12, 8) NOT NULL DEFAULT 0,
    digitos                         SMALLINT NOT NULL DEFAULT 0,
    CONSTRAINT FK_parametrosmedicos_unidad FOREIGN KEY(unidad) REFERENCES unidades(id)
);

CREATE TABLE seguimiento_parametros (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cita                            INT DEFAULT NULL,
    paciente                        INT NOT NULL,
    personal                        INT NOT NULL,
    parametro                       SMALLINT NOT NULL,
    valor                           NUMERIC(12, 8) NOT NULL DEFAULT 0,
    observaciones                   VARCHAR(1024) DEFAULT NULL,
    f_medicion                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_seguimientoparametros_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_seguimientoparametros_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_seguimientoparametros_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_seguimientoparametros_parametro FOREIGN KEY(parametro) REFERENCES parametros_medicos(id)
);

CREATE TABLE tipos_tratamiento (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(60) NOT NULL,
    descripcion                     VARCHAR(512) DEFAULT NULL
);

CREATE TABLE pacientes_tratamientos_podologicos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    paciente                        INT NOT NULL,
    cita                            INT NOT NULL,
    personal                        INT NOT NULL,
    tipo_tratamiento                SMALLINT NOT NULL,
    descripcion                     VARCHAR(1024) DEFAULT NULL,
    diagnostico_asociado            VARCHAR(1024) DEFAULT NULL,
    material_utilizado              VARCHAR(1024) DEFAULT NULL,
    observaciones                   VARCHAR(1024) DEFAULT NULL,
    f_tratamiento                   DATETIME NOT NULL,
    f_proxima_revision              DATETIME DEFAULT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_pacientestratamientospodologicos_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_pacientestratamientospodologicos_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_pacientestratamientospodologicos_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_pacientestratamientospodologicos_tipotratamiento FOREIGN KEY(tipo_tratamiento) REFERENCES tipos_tratamiento(id)
);

CREATE TABLE tipos_localizacion_lesion (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    localizacion                    VARCHAR(40) NOT NULL
);

CREATE TABLE tipos_evolucion (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    evolucion                       VARCHAR(40) NOT NULL
);

CREATE TABLE tipos_tejido (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tejido                          VARCHAR(40) NOT NULL
);

CREATE TABLE grado_wagner (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    grado                           VARCHAR(40) NOT NULL,
    descripcion                     VARCHAR(255) DEFAULT NULL
);

INSERT INTO grado_wagner(codigo, grado, descripcion) VALUES('grado-0', 'Grado 0', 'No hay lesiones, pie de riesgo. Callos gruesos y alguna deformidad ósea.'),
                                                                ('grado-1', 'Grado 1', 'Úlceras superficiales. Destrucción total del espesor de la piel.'),
                                                                ('grado-2', 'Grado 2', 'Úlceras profundas. Penetran la piel grasa pero no afecta la zona ósea.'),
                                                                ('grado-3', 'Grado 3', 'Úlcera más profunda con absceso (Osteomielitis). Compromete el tejido óseo y presencia de mal olor'),
                                                                ('grado-4', 'Grado 4', 'Gangrena limitada. Necrosis en una zona del pie, en los dedos, talón o planta.'),
                                                                ('grado-5', 'Grado 5', 'Gangrena extensa. La gangrena se extiende e invade todo el pie.');

CREATE TABLE pie_lado (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    lado                            VARCHAR(40) NOT NULL
);

INSERT INTO pie_lado(codigo, lado) VALUES('derecho', 'Pie derecho'),
                                                ('izquierdo', 'Pie izquierdo');

CREATE TABLE seguimiento_pie_diabetico (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cita                            INT DEFAULT NULL,
    paciente                        INT NOT NULL,
    personal                        INT NOT NULL,
    grado_wagner                    SMALLINT NOT NULL,
    localizacion_lesion             SMALLINT NOT NULL,
    pie_afectado                    SMALLINT NOT NULL,
    tamanyo_lesion_cm               NUMERIC(6, 4) NOT NULL DEFAULT 0,
    profundidad_lesion_cm           NUMERIC(6, 4) NOT NULL DEFAULT 0,
    presenta_infeccion              SMALLINT NOT NULL DEFAULT 0,
    presenta_necrosis               SMALLINT NOT NULL DEFAULT 0,
    tratamiento_aplicado            TEXT DEFAULT NULL,
    curas_semanales                 TEXT DEFAULT NULL,
    evolucion                       SMALLINT NOT NULL,
    observaciones                   TEXT DEFAULT NULL,
    registro                        INT NOT NULL,
    f_seguimiento                   DATETIME NOT NULL,
    f_proximo_control               DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_seguimientopiediabetico_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_seguimientopiediabetico_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_seguimientopiediabetico_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_seguimientopiediabetico_gradowagner FOREIGN KEY(grado_wagner) REFERENCES grado_wagner(id),
    CONSTRAINT FK_seguimientopiediabetico_localizacionlesion FOREIGN KEY(localizacion_lesion) REFERENCES tipos_localizacion_lesion(id),
    CONSTRAINT FK_seguimientopiediabetico_pieafectado FOREIGN KEY(pie_afectado) REFERENCES pie_lado(id),
    CONSTRAINT FK_seguimientopiediabetico_evolucion FOREIGN KEY(evolucion) REFERENCES tipos_evolucion(id),
    CONSTRAINT FK_seguimientopiediabetico_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE tipos_exudado (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(40) NOT NULL
);

CREATE TABLE color_exudado (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    color                           VARCHAR(40) NOT NULL
);

CREATE TABLE tipos_dolor (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    dolor                           VARCHAR(40) NOT NULL
);

CREATE TABLE registro_ulceras (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cita                            INT DEFAULT NULL,
    paciente                        INT NOT NULL,
    personal                        INT NOT NULL,
    ubicacion_anatomica             VARCHAR(255) NOT NULL,
    pie_afectado                    SMALLINT NOT NULL,
    largo_cm                        NUMERIC(6, 4) NOT NULL DEFAULT 0,
    ancho_cm                        NUMERIC(6, 4) NOT NULL DEFAULT 0,
    profundidad_cm                  NUMERIC(6, 4) NOT NULL DEFAULT 0,
    tejido                          SMALLINT NOT NULL,
    exudado                         SMALLINT NOT NULL,
    color_exudado                   SMALLINT NOT NULL,
    signos_infeccion                SMALLINT NOT NULL DEFAULT 0,
    olor_desagradable               SMALLINT NOT NULL DEFAULT 0,
    dolor                           SMALLINT NOT NULL,
    tratamiento_aplicado            TEXT DEFAULT NULL,
    tipo_aposito                    TEXT DEFAULT NULL,
    observaciones                   TEXT DEFAULT NULL,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_curacion                      DATETIME NOT NULL,
    f_proxima_curacion              DATETIME DEFAULT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_registroulceras_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_registroulceras_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_registroulceras_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_registroulceras_pieafectado FOREIGN KEY(pie_afectado) REFERENCES pie_lado(id),
    CONSTRAINT FK_registroulceras_tejido FOREIGN KEY(tejido) REFERENCES tipos_tejido(id),
    CONSTRAINT FK_registroulceras_exudado FOREIGN KEY(exudado) REFERENCES tipos_exudado(id),
    CONSTRAINT FK_registroulceras_colorexudado FOREIGN KEY(color_exudado) REFERENCES color_exudado(id),
    CONSTRAINT FK_registroulceras_dolor FOREIGN KEY(dolor) REFERENCES tipos_dolor(id),
    CONSTRAINT FK_registroulceras_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE material_plantillas (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    material                        VARCHAR(40) NOT NULL
);

CREATE TABLE tipos_efectividad (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    efectividad                     VARCHAR(40) NOT NULL
);

CREATE TABLE tipos_plantillas (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    plantillas                      VARCHAR(40) NOT NULL
);

CREATE TABLE plantillas_ortesis (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    cita                            INT DEFAULT NULL,
    paciente                        INT NOT NULL,
    personal                        INT NOT NULL,
    plantilla                       SMALLINT NOT NULL,
    descripcion                     VARCHAR(1024) DEFAULT NULL,
    material                        SMALLINT NOT NULL,
    caracteristicas_tecnicas        VARCHAR(1024) DEFAULT NULL,
    f_preinscripcion                DATETIME DEFAULT NULL,
    f_fabricacion                   DATETIME DEFAULT NULL,
    f_entrega                       DATETIME DEFAULT NULL,
    efectividad                     SMALLINT NOT NULL,
    observaciones_seguimiento       TEXT DEFAULT NULL,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_plantillasortesis_cita FOREIGN KEY(cita) REFERENCES citas(id),
    CONSTRAINT FK_plantillasortesis_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_plantillasortesis_personal FOREIGN KEY(personal) REFERENCES personal(id),
    CONSTRAINT FK_plantillasortesis_plantilla FOREIGN KEY(plantilla) REFERENCES tipos_plantillas(id),
    CONSTRAINT FK_plantillasortesis_material FOREIGN KEY(material) REFERENCES material_plantillas(id),
    CONSTRAINT FK_plantillasortesis_efectividad FOREIGN KEY(efectividad) REFERENCES tipos_efectividad(id),
    CONSTRAINT FK_plantillasortesis_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE tipo_contribuyente (
    id                              CHAR(1) PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    tipo                            VARCHAR(40) NOT NULL
);

INSERT INTO tipo_contribuyente(id, codigo, tipo) VALUES('F', 'fisica', 'Persona Fisica'), ('M', 'moral', 'Persona Moral');

CREATE TABLE proveedores (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    rfc                             VARCHAR(15) DEFAULT NULL,
    razon_social                    VARCHAR(255) NOT NULL,
    representante                   VARCHAR(255) NOT NULL,
    tipo_contribuyente              CHAR(1) NOT NULL,
    telefono_1                      VARCHAR(15) DEFAULT NULL,
    telefono_2                      VARCHAR(15) DEFAULT NULL,
    movil                         VARCHAR(15) DEFAULT NULL,
    email                           VARCHAR(255) DEFAULT NULL,
    calle                           VARCHAR(120) DEFAULT NULL,
    num_ext                         VARCHAR(12) DEFAULT NULL,
    num_int                         VARCHAR(12) DEFAULT NULL,
    colonia                         INT DEFAULT NULL,
    adeudos                         NUMERIC(18, 2) NOT NULL DEFAULT 0,
    registro                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_proveedores_tipocontribuyente FOREIGN KEY(tipo_contribuyente) REFERENCES tipo_contribuyente(id),
    CONSTRAINT FK_proveedores_colonia FOREIGN KEY(colonia) REFERENCES colonias(id),
    CONSTRAINT FK_proveedores_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE requisiciones_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL,
    estatus                         VARCHAR(40) NOT NULL
);

INSERT INTO requisiciones_estatus(codigo, estatus) VALUES('capturada', 'Orden Capturada'),
                                                                ('enviada', 'Orden Enviada'),
                                                                ('autorizada', 'Orden Autorizada'),
                                                                ('rechazada', 'Orden Rechazada'),
                                                                ('finalizada', 'Orden Finalizada');

CREATE TABLE requisiciones (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    solicito                        INT NOT NULL,
    autorizo                        INT NOT NULL,
    f_solicitud                     DATETIME NOT NULL,
    f_autorizacion                  DATETIME DEFAULT NULL,
    f_rechazada                     DATETIME DEFAULT NULL,
    estatus                         SMALLINT NOT NULL,
    notas                           VARCHAR(512) DEFAULT NULL,
    registro                        INT NOT NULL,
    f_registrada                    DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_requisiciones_solicito FOREIGN KEY(solicito) REFERENCES usuarios(id),
    CONSTRAINT FK_requisiciones_autorizo FOREIGN KEY(autorizo) REFERENCES usuarios(id),
    CONSTRAINT FK_requisiciones_estatus FOREIGN KEY(estatus) REFERENCES requisiciones_estatus(id),
    CONSTRAINT FK_requisiciones_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE requisiciones_articulos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    requisicion                     INT NOT NULL,
    articulo                        INT NOT NULL,
    cantidad_solicitada             NUMERIC(12, 4) NOT NULL DEFAULT 0,
    cantidad_autorizada             NUMERIC(12, 4) NOT NULL DEFAULT 0,
    notas                           VARCHAR(255) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_requisicionesarticulos_requisicion FOREIGN KEY(requisicion) REFERENCES requisiciones(id),
    CONSTRAINT FK_requisicionesarticulos_articulo FOREIGN KEY(articulo) REFERENCES articulos(id)
);

CREATE TABLE ordenes_compra_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL,
    estatus                         VARCHAR(40) NOT NULL
);

INSERT INTO ordenes_compra_estatus (codigo, estatus) VALUES('capturada', 'Capturada'),
                                                                ('autorizada', 'Lista para enviarse'),
                                                                ('enviada', 'Proveedor la recibio'),
                                                                ('confirmada', 'Proveedor acepto'),
                                                                ('parcial-recibida', 'Parcialmente recibida'),
                                                                ('recibida', 'Recibida'),
                                                                ('parcial-facturada', 'Facturación incompleta'),
                                                                ('facturada', 'Facturada'),
                                                                ('cancelada', 'Anulada antes de terminar'),
                                                                ('cerrada', 'Proceso terminado');

CREATE TABLE ordenes_compra (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    proveedor                       INT NOT NULL,
    requisicion                     INT DEFAULT NULL,
    f_orden                         DATETIME NOT NULL,
    f_autorizacion                  DATETIME DEFAULT NULL,
    f_enviada                       DATETIME DEFAULT NULL,
    f_cancelada                     DATETIME DEFAULT NULL,
    f_cerrada                       DATETIME DEFAULT NULL,
    plazo_dias                      INT NOT NULL,
    f_esperada                      DATETIME NOT NULL,
    estatus                         SMALLINT NOT NULL,
    subtotal                        NUMERIC(18, 2) NOT NULL DEFAULT 0,
    impuestos                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    total                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    pagado                          NUMERIC(18, 2) NOT NULL DEFAULT 0,
    f_ultimo_pago                   DATETIME DEFAULT NULL,
    solicito                        INT NOT NULL,
    autorizo                        INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_ordenescompra_proveedor FOREIGN KEY(proveedor) REFERENCES proveedores(id),
    CONSTRAINT FK_ordenescompra_requisicion FOREIGN KEY(requisicion) REFERENCES requisiciones(id),
    CONSTRAINT FK_ordenescompra_estatus FOREIGN KEY(estatus) REFERENCES ordenes_compra_estatus(id),
    CONSTRAINT FK_ordenescompra_solicito FOREIGN KEY(solicito) REFERENCES usuarios(id),
    CONSTRAINT FK_ordenescompra_autorizo FOREIGN KEY(autorizo) REFERENCES usuarios(id)
);

CREATE TABLE impuestos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    descripcion                     VARCHAR(60) NOT NULL
);

INSERT INTO impuestos(codigo, descripcion) VALUES('IVA', 'Impuesto al Valor Agregado'),
                                                    ('IEPS', 'Impuesto Especial sobre Producción y Servicios'),
                                                    ('ISR', 'Impuesto Sobre la Renta');

CREATE TABLE perfil_impuestos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    perfil                          VARCHAR(30) NOT NULL
);

INSERT INTO perfil_impuestos(codigo, perfil) VALUES('general', 'General'),
                                                    ('frontera', 'Frontera'),
                                                    ('exento', 'Exento');

CREATE TABLE perfil_impuestos_detalle (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    perfil                          SMALLINT NOT NULL,
    impuesto                        SMALLINT NOT NULL,
    tasa                            NUMERIC(8, 6) NOT NULL,
    vigente_desde                   DATETIME NOT NULL,
    vigente_hasta                   DATETIME NOT NULL,
    CONSTRAINT FK_perfilimpuestosdetalle_perfil FOREIGN KEY(perfil) REFERENCES perfil_impuestos(id),
    CONSTRAINT FK_perfilimpuestosdetalle_impuesto FOREIGN KEY(impuesto) REFERENCES impuestos(id)
);

CREATE TABLE ordenes_compra_articulos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    orden_compra                    INT NOT NULL,
    articulo                        INT NOT NULL,
    cantidad_solicitada             NUMERIC(12, 4) NOT NULL DEFAULT 0,
    cantidad_autorizada             NUMERIC(12, 4) NOT NULL DEFAULT 0,
    cantidad_recibida               NUMERIC(12, 4) NOT NULL DEFAULT 0,
    costo_unidad                    NUMERIC(18, 2) NOT NULL DEFAULT 0,
    subtotal                        NUMERIC(18, 2) NOT NULL DEFAULT 0,
    impuestos                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    total                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    tasa                            NUMERIC(8, 6) NOT NULL DEFAULT 0,
    perfil_impuesto                 SMALLINT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_ordenescompraarticulos_ordencompra FOREIGN KEY(orden_compra) REFERENCES ordenes_compra(id),
    CONSTRAINT FK_ordenescompraarticulos_articulo FOREIGN KEY(articulo) REFERENCES articulos(id),
    CONSTRAINT FK_ordenescompraarticulos_perfilimpuesto FOREIGN KEY(perfil_impuesto) REFERENCES perfil_impuestos(id)
);

CREATE TABLE productos_categoria (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    categoria                       VARCHAR(60) NOT NULL
);

CREATE TABLE productos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    clave                           VARCHAR(12) NOT NULL UNIQUE,
    codigo_barras                   VARCHAR(32) NOT NULL UNIQUE,
    nombre                          VARCHAR(100) NOT NULL,
    nombre_ticket                   VARCHAR(32) NOT NULL,
    categoria                       SMALLINT NOT NULL,
    descripcion                     VARCHAR(255) DEFAULT NULL,
    unidad                          VARCHAR(8) NOT NULL DEFAULT 1,
    habilitado_venta                SMALLINT NOT NULL DEFAULT 1,
    registro                        INT NOT NULL,
    f_registro                    DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_productos_categoria FOREIGN KEY(categoria) REFERENCES productos_categoria(id),
    CONSTRAINT FK_productos_unidad FOREIGN KEY(unidad) REFERENCES unidades(id),
    CONSTRAINT FK_productos_registro FOREIGN KEY(registro) REFERENCES usuarios(id)
);

CREATE TABLE productos_articulos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    producto                        INT NOT NULL,
    articulo                        INT NOT NULL,
    cantidad                        NUMERIC(12, 4) NOT NULL DEFAULT 0,
    vigente_desde                   DATETIME NOT NULL,
    vigente_hasta                   DATETIME DEFAULT NULL,
    CONSTRAINT FK_productosarticulos_producto FOREIGN KEY(producto) REFERENCES productos(id),
    CONSTRAINT FK_productosarticulos_articulo FOREIGN KEY(articulo) REFERENCES articulos(id)
);

CREATE TABLE productos_precios (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    producto                        INT NOT NULL,
    precio_base                     NUMERIC(18, 2) NOT NULL DEFAULT 0,
    perfil_impuesto                 SMALLINT NOT NULL,
    vigente_desde                   DATETIME NOT NULL,
    vigente_hasta                   DATETIME NOT NULL,
    CONSTRAINT FK_productosprecios_producto FOREIGN KEY(producto) REFERENCES productos(id),
    CONSTRAINT FK_productosprecios_perfilimpuesto FOREIGN KEY(perfil_impuesto) REFERENCES perfil_impuestos(id)
);

CREATE TABLE ventas_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    estatus                         VARCHAR(60) NOT NULL
);

CREATE TABLE ventas (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    folio                           VARCHAR(10) NOT NULL UNIQUE,
    consecutivo                     SMALLINT NOT NULL,
    f_venta                         DATETIME NOT NULL,
    cliente                         INT NOT NULL,
    paciente                        INT NOT NULL,
    registro                        INT NOT NULL,
    subtotal                        NUMERIC(18, 2) NOT NULL DEFAULT 0,
    impuestos                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    total                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    descuento                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    estatus                         SMALLINT NOT NULL,
    observaciones                   VARCHAR(1024) NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_ventas_cliente FOREIGN KEY(cliente) REFERENCES clientes(id),
    CONSTRAINT FK_ventas_paciente FOREIGN KEY(paciente) REFERENCES pacientes(id),
    CONSTRAINT FK_ventas_registro FOREIGN KEY(registro) REFERENCES usuarios(id),
    CONSTRAINT FK_ventas_estatus FOREIGN KEY(estatus) REFERENCES ventas_estatus(id)
);

CREATE TABLE tipos_descuentos (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    descuento                       VARCHAR(30) NOT NULL
);

CREATE TABLE ventas_productos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    venta                           INT NOT NULL,
    producto                        INT NOT NULL,
    cita                            INT DEFAULT NULL,
    cantidad                        NUMERIC(12, 4) NOT NULL DEFAULT 0,
    precio_base                     NUMERIC(18, 2) NOT NULL DEFAULT 0,
    subtotal                        NUMERIC(18, 2) NOT NULL DEFAULT 0,
    impuestos                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    total                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    descuento                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_ventasproductos_venta FOREIGN KEY(venta) REFERENCES ventas(id),
    CONSTRAINT FK_ventasproductos_producto FOREIGN KEY(producto) REFERENCES productos(id),
    CONSTRAINT FK_ventasproductos_cita FOREIGN KEY(cita) REFERENCES citas(id)
);

CREATE TABLE metodos_pago (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    metodo                          VARCHAR(60) NOT NULL,
    referencia                      SMALLINT DEFAULT 0
);

INSERT INTO metodos_pago(codigo, metodo, referencia) VALUES('efectivo', 'Efectivo', 0),
                                                                ('t-debito', 'Tarjeta Debito', 1),
                                                                ('t-credito', 'Tarjeta Credito', 1),
                                                                ('transferencia', 'Transferencia', 1),
                                                                ('cheque', 'Cheque', 1),
                                                                ('internet', 'Internet', 1),
                                                                ('credito', 'Credito', 1);

CREATE TABLE impresoras_conexion (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL UNIQUE,
    conexion                        VARCHAR(30) NOT NULL
);

INSERT INTO impresoras_conexion(codigo, conexion) VALUES('usb', 'Cable USB'),
                                                        ('network', 'Red'),
                                                        ('bluetooth', 'Bluetooth');

CREATE TABLE impresoras (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    tipo_conexion                   SMALLINT NOT NULL,
    direccion                       VARCHAR(30) NOT NULL,
    puerto                          VARCHAR(10) DEFAULT NULL,
    mac_address                     VARCHAR(20) DEFAULT NULL,
    ubicacion                       VARCHAR(60) DEFAULT NULL,
    CONSTRAINT FK_impresoras_tipoconexion FOREIGN KEY(tipo_conexion) REFERENCES impresoras_conexion(id)
);

CREATE TABLE cajas (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    caja                            VARCHAR(30) NOT NULL,
    codigo                          VARCHAR(30) NOT NULL UNIQUE,
    ubicacion                       VARCHAR(60) DEFAULT NULL,
    oculta                          SMALLINT NOT NULL DEFAULT 0,
    activa                          SMALLINT NOT NULL DEFAULT 1,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL                        
);

CREATE TABLE cajas_impresoras (
    caja                            SMALLINT NOT NULL,
    impresora                       SMALLINT NOT NULL,
    CONSTRAINT FK_cajasimpresoras_caja FOREIGN KEY(caja) REFERENCES cajas(id),
    CONSTRAINT FK_cajasimpresoras_impresora FOREIGN KEY(impresora) REFERENCES impresoras(id)
);

CREATE TABLE cortes_estatus (
    id                              SMALLINT AUTO_INCREMENT PRIMARY KEY,
    codigo                          VARCHAR(20) NOT NULL,
    estatus                         VARCHAR(30) NOT NULL
);

INSERT INTO cortes_estatus(codigo, estatus) VALUES('open', 'Abierta'),
                                                    ('closed', 'Cerrada'),
                                                    ('waiting', 'En Espera'),
                                                    ('busy', 'Ocupada');

CREATE TABLE cortes (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    caja                            SMALLINT NOT NULL,
    abierta_por                     INT NOT NULL,
    f_abierta                       DATETIME NOT NULL,
    monto_apertura                  NUMERIC(18, 2) NOT NULL,
    cerrada_por                     INT DEFAULT NULL,
    f_cierre                        DATETIME DEFAULT NULL,
    monto_cierre                    NUMERIC(18, 2) DEFAULT NULL,
    efectivo_esperado               NUMERIC(18, 2) DEFAULT NULL,
    retiros                         NUMERIC(18, 2) NOT NULL DEFAULT 0,
    depositos                       NUMERIC(18, 2) NOT NULL DEFAULT 0,
    diferencia                      NUMERIC(18, 2) DEFAULT NULL,
    estatus                         SMALLINT NOT NULL,
    observaciones                   VARCHAR(1024) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_cortes_caja FOREIGN KEY(caja) REFERENCES cajas(id),
    CONSTRAINT FK_cortes_abiertapor FOREIGN KEY(abierta_por) REFERENCES usuarios(id),
    CONSTRAINT FK_cortes_cerradapor FOREIGN KEY(cerrada_por) REFERENCES usuarios(id),
    CONSTRAINT FK_cortes_estatus FOREIGN KEY(estatus) REFERENCES cortes_estatus(id)
);

CREATE TABLE cortes_depositos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    corte                           INT NOT NULL,
    efectivo_antes                  NUMERIC(18, 2) NOT NULL DEFAULT 0,
    monto                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    efectivo_despues                NUMERIC(18, 2) NOT NULL DEFAULT 0,
    cajero                          INT NOT NULL,
    entrego                         INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_cortesdepositos_corte FOREIGN KEY(corte) REFERENCES cortes(id),
    CONSTRAINT FK_cortesdepositos_cajero FOREIGN KEY(cajero) REFERENCES usuarios(id),
    CONSTRAINT FK_cortesdepositos_entrego FOREIGN KEY(entrego) REFERENCES usuarios(id)
);

CREATE TABLE cortes_retiros (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    corte                           INT NOT NULL,
    efectivo_antes                  NUMERIC(18, 2) NOT NULL DEFAULT 0,
    monto                           NUMERIC(18, 2) NOT NULL DEFAULT 0,
    efectivo_despues                NUMERIC(18, 2) NOT NULL DEFAULT 0,
    cajero                          INT NOT NULL,
    retiro                          INT NOT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_cortesretiros_corte FOREIGN KEY(corte) REFERENCES cortes(id),
    CONSTRAINT FK_cortesretiros_cajero FOREIGN KEY(cajero) REFERENCES usuarios(id),
    CONSTRAINT FK_cortesretiros_retiro FOREIGN KEY(retiro) REFERENCES usuarios(id)
);

CREATE TABLE pagos (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    folio                           VARCHAR(10) NOT NULL UNIQUE,
    consecutivo                     SMALLINT NOT NULL DEFAULT 0,
    f_pago                          DATETIME NOT NULL,
    cliente                         INT DEFAULT NULL,
    registro                        INT NOT NULL,
    corte                           INT NOT NULL,
    metodo_pago                     SMALLINT NOT NULL,
    referencia                      VARCHAR(25) DEFAULT NULL,
    observaciones                   VARCHAR(500) DEFAULT NULL,
    f_registro                      DATETIME NOT NULL,
    f_actualizacion                 DATETIME NOT NULL,
    CONSTRAINT FK_pagos_cliente FOREIGN KEY(cliente) REFERENCES clientes(id),
    CONSTRAINT FK_pagos_registro FOREIGN KEY(registro) REFERENCES usuarios(id),
    CONSTRAINT FK_pagos_corte FOREIGN KEY(corte) REFERENCES cortes(id),
    CONSTRAINT FK_pagos_metodopago FOREIGN KEY(metodo_pago) REFERENCES metodos_pago(id)
);

CREATE TABLE pagos_ventas (
    id                              INT AUTO_INCREMENT PRIMARY KEY,
    uuid                            BINARY(16) NOT NULL UNIQUE,
    pago                            INT NOT NULL,
    venta                           INT NOT NULL,
    adeudo_anterior                 NUMERIC(18, 2) NOT NULL,
    monto_pago                      NUMERIC(18, 2) NOT NULL,
    adeudo_actual                   NUMERIC(18, 2) NOT NULL,
    CONSTRAINT FK_pagosventas_pago FOREIGN KEY(pago) REFERENCES pagos(id),
    CONSTRAINT FK_pagosventas_venta FOREIGN KEY(venta) REFERENCES ventas(id)
);











DELIMITER $$

CREATE TRIGGER validar_rango_bloque
BEFORE INSERT ON citas_bloques
FOR EACH ROW
BEGIN
    IF NEW.h_inicio < 0 OR NEW.h_inicio >= 1440 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hora inicio fuera de rango';
    END IF;

    IF NEW.h_fin <= 0 OR NEW.h_fin > 1440 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hora fin fuera de rango';
    END IF;

    IF NEW.h_fin <= NEW.h_inicio THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La hora fin debe ser mayor que inicio';
    END IF;

    IF NEW.duracion <> (NEW.h_fin - NEW.h_inicio) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Duración inconsistente con horas';
    END IF;
END$$

CREATE TRIGGER validar_empalme_personal
BEFORE INSERT ON citas_bloques
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM citas_bloques b
        INNER JOIN citas c ON c.id = b.cita
        WHERE b.personal = NEW.personal
          AND c.fecha = (SELECT fecha FROM citas WHERE id = NEW.cita)
          AND NEW.h_inicio < b.h_fin
          AND NEW.h_fin > b.h_inicio
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El personal ya tiene una cita en ese horario';
    END IF;
END$$

CREATE TRIGGER validar_rango_bloque_update
BEFORE UPDATE ON citas_bloques
FOR EACH ROW
BEGIN
    IF NEW.h_inicio < 0 OR NEW.h_inicio >= 1440 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hora inicio fuera de rango';
    END IF;

    IF NEW.h_fin <= 0 OR NEW.h_fin > 1440 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Hora fin fuera de rango';
    END IF;

    IF NEW.h_fin <= NEW.h_inicio THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La hora fin debe ser mayor que inicio';
    END IF;

    IF NEW.duracion <> (NEW.h_fin - NEW.h_inicio) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Duración inconsistente con horas';
    END IF;
END$$

CREATE TRIGGER validar_empalme_personal_update
BEFORE UPDATE ON citas_bloques
FOR EACH ROW
BEGIN
    IF EXISTS (
        SELECT 1
        FROM citas_bloques b
        INNER JOIN citas c ON c.id = b.cita
        WHERE b.personal = NEW.personal
          AND c.fecha = (SELECT fecha FROM citas WHERE id = NEW.cita)
          AND NEW.h_inicio < b.h_fin
          AND NEW.h_fin > b.h_inicio
          AND b.id <> NEW.id
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El personal ya tiene una cita en ese horario';
    END IF;
END$$

DELIMITER ;


/* SELECT count(*) FROM sqlite_master WHERE type = 'table'; */

/*

INSERT INTO pacientes(id, uuid, clave, nombre, paterno, materno, calle, num_ext, colonia, cp, genero, email, f_nacimiento, telefono, movil, registro, f_registro, f_actualizacion) VALUES(1, 1, 'PE-000001', 'Paciente', 'Numero', '1', 'Domicilio Conocido', '123', 1275, '66004', 'H', 'paciente_1@helix.com', '2001-01-12', '555 555 5555', '565 456 1245', 1, NOW(), NOW()),
                                                                                                                                                                                            (2, 2, 'PE-000002', 'Paciente', 'Numero', '2', 'Domicilio Conocido', '456', 1180, '66036', 'H', 'paciente_2@helix.com', '1994-03-28', '123 456 7890', '265 456 1245', 1, NOW(), NOW());
*/