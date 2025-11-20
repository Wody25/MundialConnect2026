-- db.sql - Base de datos para MundialConnect 2026 en SQL Server
 CREATE DATABASE MundialConnect2026DB;
GO

USE MundialConnect2026DB;
GO

-- Tabla de usuarios
CREATE TABLE Usuarios (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre NVARCHAR(100) NOT NULL,
    email NVARCHAR(255) NOT NULL UNIQUE,
    password_hash NVARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT GETDATE()
);
GO

-- Tabla de perfiles
CREATE TABLE Perfiles (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario_id INT NOT NULL FOREIGN KEY REFERENCES Usuarios(id),
    equipo_favorito NVARCHAR(100) NULL,
    avatar_url NVARCHAR(500) NULL
);
GO

-- Tabla de amistades
CREATE TABLE Amistades (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario1 INT NOT NULL FOREIGN KEY REFERENCES Usuarios(id),
    usuario2 INT NOT NULL FOREIGN KEY REFERENCES Usuarios(id),
    estado NVARCHAR(20) NOT NULL DEFAULT 'pendiente',
    fecha_solicitud DATETIME NOT NULL DEFAULT GETDATE()
);
GO

CREATE TABLE Notificaciones (
    id INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NOT NULL FOREIGN KEY REFERENCES Usuarios(id),
    tipo NVARCHAR(50) NOT NULL,
    mensaje NVARCHAR(255) NOT NULL,
    fecha DATETIME NOT NULL DEFAULT GETDATE(),
    leida BIT NOT NULL DEFAULT 0
);
GO

ALTER TABLE Notificaciones
ADD id_amistad INT NULL;
ALTER TABLE Notificaciones
ADD CONSTRAINT FK_Notif_Amistad FOREIGN KEY (id_amistad) REFERENCES Amistades(id);

UPDATE Notificaciones
SET id_amistad = A.id
FROM Notificaciones N
JOIN Amistades A ON A.usuario2 = N.id_usuario
WHERE N.tipo = 'solicitud_amistad' AND N.id_amistad IS NULL;

ALTER TABLE Notificaciones
ADD id_receptor INT NULL;
GO

ALTER TABLE Notificaciones
ADD CONSTRAINT FK_Notif_Receptor FOREIGN KEY (id_receptor) REFERENCES Usuarios(id);
GO



-- NUEVAS TABLAS PARA FAVORITOS Y EQUIPOS

-- Tabla de equipos del Mundial
CREATE TABLE Equipos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    nombre NVARCHAR(50) NOT NULL,
    grupo CHAR(1),
    pais_bandera NVARCHAR(255) NULL
);
GO

-- Tabla de favoritos de cada usuario
CREATE TABLE Favoritos (
    id INT IDENTITY(1,1) PRIMARY KEY,
    usuario_id INT NOT NULL FOREIGN KEY REFERENCES Usuarios(id),
    equipo_id INT NOT NULL FOREIGN KEY REFERENCES Equipos(id),
    fecha_seleccion DATETIME NOT NULL DEFAULT GETDATE()
);
GO

-- Insertar algunos equipos de ejemplo
INSERT INTO Equipos (nombre, grupo, pais_bandera) VALUES
('México', 'A', NULL),
('Canadá', 'A', NULL),
('USA', 'B', NULL),
('Brasil', 'B', NULL),
('Alemania', 'C', NULL),
('Japón', 'C', NULL),
('Argentina', 'D', NULL),
('España', 'D', NULL);
GO

SELECT *FROM Equipos;
SELECT *FROM Usuarios;
SELECT *FROM Favoritos;
SELECT *FROM Notificaciones;