CREATE TABLE JS_FS_FV_VV_Poderes (
    ID_Poder NUMBER PRIMARY KEY,
    Nombre_poder VARCHAR2(10)
);

CREATE TABLE JS_FS_FV_VV_Cargo (
    ID_Cargo NUMBER PRIMARY KEY,
    Nombre_Cargo VARCHAR2(20),
    Poder NUMBER,
    FOREIGN KEY (Poder) REFERENCES JS_FS_FV_VV_Poderes(ID_Poder)
);

CREATE TABLE JS_FS_FV_VV_Previsiones (
    ID_Prevision NUMBER PRIMARY KEY,
    Nombre_prevision VARCHAR2(20),
    Porcentaje NUMBER
);

CREATE TABLE JS_FS_FV_VV_Salud (
    ID_Salud NUMBER PRIMARY KEY,
    Nombre VARCHAR2(20),
    Afp_Porcentaje NUMBER
);

CREATE TABLE JS_FS_FV_VV_Horas_Disponibles (
    ID_Horas NUMBER PRIMARY KEY,
    Horas NUMBER
);

CREATE TABLE JS_FS_FV_VV_Contratos (
    ID_Contrato NUMBER PRIMARY KEY,
    Contrato VARCHAR2(20),
    Duracion_Meses NUMBER,
    Renovacion_Automatica VARCHAR2(1),
    Horas_Asignadas NUMBER,
    Fecha_contratacion DATE,
    FOREIGN KEY (Horas_Asignadas) REFERENCES JS_FS_FV_VV_Horas_Disponibles(ID_Horas)
);

CREATE TABLE JS_FS_FV_VV_Usuario (
    Rut_Usuario NUMBER PRIMARY KEY,
    Nombre1 VARCHAR2(20),
    Nombre2 VARCHAR2(20),
    Apellido1 VARCHAR2(20),
    Apellido2 VARCHAR2(20),
    Contraseña VARCHAR2(20),
    Telefono number,
    Correo VARCHAR2(20),
    Salario NUMBER,
    ID_Cargo NUMBER,
    ID_Contrato NUMBER,
    ID_Prevision NUMBER,
    ID_Salud NUMBER,
    FOREIGN KEY (ID_Cargo) REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo),
    FOREIGN KEY (ID_Contrato) REFERENCES JS_FS_FV_VV_Contratos(ID_Contrato),
    FOREIGN KEY (ID_Prevision) REFERENCES JS_FS_FV_VV_Previsiones(ID_Prevision),
    FOREIGN KEY (ID_Salud) REFERENCES JS_FS_FV_VV_Salud(ID_Salud)
);

CREATE TABLE JS_FS_FV_VV_Candidato (
    Rut NUMBER PRIMARY KEY,
    Correo VARCHAR2(50),
    Fecha_nacimiento DATE,
    Nombre1 VARCHAR2(20),
    Nombre2 VARCHAR2(20),
    Apellido1 VARCHAR2(20),
    Apellido2 VARCHAR2(20),
    Estado VARCHAR2(20),
    Cargo_Postular NUMBER,
    FOREIGN KEY (Cargo_Postular) REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo)
);

CREATE TABLE JS_FS_FV_VV_Proceso_Contratacion1 (
    ID_Proceso1 NUMBER PRIMARY KEY,
    Fecha_Inicio DATE,
    Fecha_fin DATE,
    Estado NUMBER,
    ID_Candidato NUMBER,
    ID_Entrevistador NUMBER,
    Comentario VARCHAR2(20),
    FOREIGN KEY (ID_Candidato) REFERENCES JS_FS_FV_VV_Candidato(Rut),
    FOREIGN KEY (ID_Entrevistador) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario)
);

CREATE TABLE JS_FS_FV_VV_Proceso_Contratacion2 (
    ID_Proceso2 NUMBER PRIMARY KEY,
    Fecha_Inicio DATE,
    Fecha_fin DATE,
    Estado NUMBER,
    ID_Proceso1 NUMBER,
    ID_Entrevistador NUMBER,
    ID_Candidato NUMBER,
    Cargo_Asignado NUMBER,
    FOREIGN KEY (ID_Proceso1) REFERENCES JS_FS_FV_VV_Proceso_Contratacion1(ID_Proceso1),
    FOREIGN KEY (ID_Candidato) REFERENCES JS_FS_FV_VV_Candidato(Rut),
    FOREIGN KEY (Cargo_Asignado) REFERENCES JS_FS_FV_VV_Cargo(ID_Cargo),
    FOREIGN KEY (ID_Entrevistador) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario)
);


CREATE TABLE JS_FS_FV_VV_UsuarioContratacion2 (
    ID_Usuario NUMBER,
    ID_Proceso2 NUMBER,
    PRIMARY KEY (ID_Usuario, ID_Proceso2),
    FOREIGN KEY (ID_Usuario) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario),
    FOREIGN KEY (ID_Proceso2) REFERENCES JS_FS_FV_VV_Proceso_Contratacion2(ID_Proceso2)
);

CREATE TABLE JS_FS_FV_VV_Solicitud_despido (
    ID_Solicitud NUMBER PRIMARY KEY,
    ID_Motivo_Solicitud NUMBER,
    Finiquito NUMBER,
    Fecha_Solicitud DATE,
    ID_UsuarioSolicitud NUMBER,
    Estado_Solicitud VARCHAR2(20),
    ID_Despedido NUMBER,
    FOREIGN KEY (ID_Motivo_Solicitud) REFERENCES JS_FS_FV_VV_Motivos_Despidos(ID_Motivo_Despido),
    FOREIGN KEY (ID_UsuarioSolicitud) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario),
    FOREIGN KEY (Estado_Solicitud) REFERENCES JS_FS_FV_VV_Estados(Estados),
    FOREIGN KEY (ID_Despedido) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario)
);


CREATE TABLE JS_FS_FV_VV_Motivos_Despidos (
    ID_Motivo_Despido NUMBER PRIMARY KEY,
    Motivo VARCHAR2(100),
    Comentario VARCHAR2(200)
);

CREATE TABLE JS_FS_FV_VV_Estados (
    ID_Estado NUMBER PRIMARY KEY,
    Estados VARCHAR2(20)
);

CREATE TABLE JS_FS_FV_VV_Solicitud_Renuncia (
    ID_Solicitud NUMBER PRIMARY KEY,
    Motivo_Renuncia NUMBER,
    Finiquito NUMBER,
    Fecha_Solicitud DATE,
    ID_UsuarioRenuncia NUMBER,
    Estado_Solicitud VARCHAR2(20),
    FOREIGN KEY (Motivo_Renuncia) REFERENCES JS_FS_FV_VV_Motivo_Renuncia(ID_Motivo_Renuncia),
    FOREIGN KEY (ID_UsuarioRenuncia) REFERENCES JS_FS_FV_VV_Usuario(Rut_Usuario),
);


CREATE TABLE JS_FS_FV_VV_Motivo_Renuncia (
    ID_Motivo_Renuncia NUMBER PRIMARY KEY,
    Motivo VARCHAR2(100),
    Comentario VARCHAR2(200)
);
