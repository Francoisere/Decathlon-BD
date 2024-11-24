-- Tabla de Deportes
CREATE TABLE JS_FS_FV_VV_Deportes (
    ID_Deportes NUMBER PRIMARY KEY,
    Deporte VARCHAR2(20) NOT NULL
);

-- Tabla de Poderes
CREATE TABLE JS_FS_FV_VV_Poderes (
    ID_Poder NUMBER PRIMARY KEY,
    Nombre_Poder VARCHAR2(20) NOT NULL
);

-- Tabla de Cargos
CREATE TABLE JS_FS_FV_VV_Cargo (
    ID_Cargo NUMBER PRIMARY KEY,
    Nombre_Cargo VARCHAR2(50) NOT NULL,
    Poder NUMBER REFERENCES JS_FS_FV_VV_Poderes(ID_Poder)
);

-- Tabla de Previsiones
CREATE TABLE JS_FS_FV_VV_Previsiones (
    ID_Prevision NUMBER PRIMARY KEY,
    Nombre_Prevision VARCHAR2(50) NOT NULL,
    ID_Tipo_Prevision NUMBER REFERENCES JS_FS_FV_VV_Tipo_Previsiones(ID_Tipo_Prevision)
);

-- Tabla de Tipos de Previsiones
CREATE TABLE JS_FS_FV_VV_Tipo_Previsiones (
    ID_Tipo_Prevision NUMBER PRIMARY KEY,
    Nombre_Tipo_Prevision VARCHAR2(50) NOT NULL,
    Porcentaje NUMBER NOT NULL
);

-- Tabla de Caja de Compensación
CREATE TABLE JS_FS_FV_VV_Caja_Compensacion (
    ID_CC NUMBER PRIMARY KEY,
    Nombre VARCHAR2(50) NOT NULL,
    Porcentaje NUMBER NOT NULL
);

-- Tabla de Horas Disponibles
CREATE TABLE JS_FS_FV_VV_Horas_Disponibles (
    ID_Horas NUMBER PRIMARY KEY,
    Horas NUMBER NOT NULL
);

-- Tabla de Usuarios
CREATE TABLE JS_FS_FV_VV_Usuario (
    Rut_Usuario NUMBER PRIMARY KEY,
    Nombre1 VARCHAR2(50) NOT NULL,
    Nombre2 VARCHAR2(50),
    Apellido1 VARCHAR2(50) NOT NULL,
    Apellido2 VARCHAR2(50),
    Clave VARCHAR2(255) NOT NULL, -- Contraseña cifrada
    Correo VARCHAR2(100) UNIQUE NOT NULL,
    Telefono NUMBER,
    Salario NUMBER NOT NULL,
    ID_Cargo NUMBER REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo),
    ID_Prevision NUMBER REFERENCES JS_FS_FV_VV_Previsiones(ID_Prevision),
    ID_CC NUMBER REFERENCES JS_FS_FV_VV_Caja_Compensacion(ID_CC)
);

-- Tabla de Candidatos
CREATE TABLE JS_FS_FV_VV_Candidato (
    Rut NUMBER PRIMARY KEY,
    Correo VARCHAR2(100) UNIQUE NOT NULL,
    Fecha_Nacimiento DATE NOT NULL,
    Nombre1 VARCHAR2(50) NOT NULL,
    Nombre2 VARCHAR2(50),
    Apellido1 VARCHAR2(50) NOT NULL,
    Apellido2 VARCHAR2(50),
    Cargo_Postular NUMBER REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo),
    Deporte_Postular NUMBER REFERENCES JS_FS_FV_VV_Deportes(ID_Deportes)
);

-- Tabla de Contratos
CREATE TABLE JS_FS_FV_VV_Contratos (
    ID_Contrato NUMBER PRIMARY KEY,
    Duracion_Meses NUMBER NOT NULL,
    Renovacion_Automatica VARCHAR2(10) NOT NULL,
    Fecha_Contratacion DATE NOT NULL,
    ID_Cargo NUMBER REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo),
    ID_Usuario NUMBER REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario)
);

-- Tabla de Estados
CREATE TABLE JS_FS_FV_VV_Estados (
    ID_Estado NUMBER PRIMARY KEY,
    Estados VARCHAR2(20) NOT NULL
);

-- Tabla de Motivos de Renuncia
CREATE TABLE JS_FS_FV_VV_Motivo_Renuncia (
    ID_Motivo_Renuncia NUMBER PRIMARY KEY,
    Motivo VARCHAR2(200) NOT NULL
);

-- Tabla de Solicitudes de Renuncia
CREATE TABLE JS_FS_FV_VV_Solicitud_Renuncia (
    ID_Solicitud NUMBER PRIMARY KEY,
    ID_Motivo_Renuncia NUMBER REFERENCES JS_FS_FV_VV_Motivo_Renuncia(ID_Motivo_Renuncia),
    Finiquito NUMBER NOT NULL,
    Fecha_Solicitud DATE NOT NULL,
    ID_UsuarioRenuncia NUMBER REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario),
    Estado_Solicitud NUMBER REFERENCES JS_FS_FV_VV_Estados(ID_Estado),
    Comentario VARCHAR2(255)
);

-- Tabla de Procesos de Contratación (Unificada)
CREATE TABLE JS_FS_FV_VV_Proceso_Contratacion (
    ID_Proceso NUMBER PRIMARY KEY,
    Fecha_Inicio DATE NOT NULL,
    Estado NUMBER REFERENCES JS_FS_FV_VV_Estados(ID_Estado),
    ID_Candidato NUMBER REFERENCES JS_FS_FV_VV_Candidato(Rut),
    ID_Entrevistador NUMBER REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario),
    Comentario VARCHAR2(255)
);
