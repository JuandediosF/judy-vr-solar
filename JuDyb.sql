-- ============================================================
-- JuDyb.sql — Base de datos del proyecto JuDy Simulator
-- Charset: utf8mb4 | Collation: utf8mb4_unicode_ci | Engine: InnoDB
-- Compatible con InfinityFree (sin CREATE DATABASE ni USE)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------
-- Tabla: usuarios
-- -----------------------------------------------------------
DROP TABLE IF EXISTS `avances_vr`;
DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id_usuario`       INT          NOT NULL AUTO_INCREMENT,
  `matricula`        VARCHAR(20)  NOT NULL,
  `nombre_completo`  VARCHAR(100) NOT NULL,
  `email`            VARCHAR(100) DEFAULT NULL,
  `telefono`         VARCHAR(15)  DEFAULT NULL,
  `password`         VARCHAR(255) NOT NULL COMMENT 'Almacenada con bcrypt (password_hash)',
  `rol`              VARCHAR(20)  NOT NULL DEFAULT 'por_asignar',
  `estatus`          VARCHAR(20)  NOT NULL DEFAULT 'pendiente',
  `token_aprobacion` VARCHAR(64)  DEFAULT NULL COMMENT 'Token aleatorio para link de aprobación por correo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uq_matricula` (`matricula`)
) ENGINE=InnoDB
  AUTO_INCREMENT=5
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Usuarios de prueba
-- Contraseña de GER-001 y EMP-001: 12345 (hasheada con bcrypt)
INSERT INTO `usuarios`
  (`id_usuario`, `matricula`, `nombre_completo`, `email`, `telefono`, `password`, `rol`, `estatus`, `token_aprobacion`)
VALUES
  (1, 'GER-001', 'Dilan Rodríguez', NULL, NULL, '$2y$10$hKbh6Fx2Ysb4fWEQN6X9AefLLe9giUQG7.zftDxIen46XJ1yBrVmO', 'gerencia', 'aprobado', NULL),
  (2, 'EMP-001', 'Carlos Rivera', NULL, NULL, '$2y$10$iIAJk9ueUEegycaSBQnLQ.zTIP8VmTlzmUYF1CuIMe7FXQScySX9C', 'empleado', 'aprobado', NULL),
  (3, '142510685', 'Dilan Israel Rodriguez Cano', 'dilan.cano2025@gmail.com', '4691214870', '$2y$10$iIAJk9ueUEegycaSBQnLQ.zTIP8VmTlzmUYF1CuIMe7FXQScySX9C', 'por_asignar', 'pendiente', '2ecb1a0af427c3c4b6ecc8e2ee6892eb'),
  (4, '142565415', 'Usuario Prueba 2', 'dilan.cano2028@gmail.com', '4691214870', '$2y$10$iIAJk9ueUEegycaSBQnLQ.zTIP8VmTlzmUYF1CuIMe7FXQScySX9C', 'por_asignar', 'pendiente', '375be34e8063bdd0a5d7d3c727228a90');

-- -----------------------------------------------------------
-- Tabla: avances_vr
-- -----------------------------------------------------------
CREATE TABLE `avances_vr` (
  `id_avance`         INT          NOT NULL AUTO_INCREMENT,
  `id_usuario`        INT          NOT NULL,
  `modulo`            VARCHAR(100) NOT NULL,
  `porcentaje`        INT          NOT NULL DEFAULT 0,
  `estado`            VARCHAR(50)  NOT NULL DEFAULT 'En Progreso',
  `retroalimentacion` TEXT         DEFAULT NULL,
  PRIMARY KEY (`id_avance`),
  KEY `fk_usuario` (`id_usuario`),
  CONSTRAINT `fk_avances_usuario`
    FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
    ON DELETE CASCADE
) ENGINE=InnoDB
  AUTO_INCREMENT=5
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO `avances_vr`
  (`id_avance`, `id_usuario`, `modulo`, `porcentaje`, `estado`, `retroalimentacion`)
VALUES
  (1, 2, 'Fase 1: Instalación (NOM-029)', 100, 'Completado', 'Excelente dominio de los procedimientos eléctricos y uso de equipo de protección básico.'),
  (2, 2, 'Fase 2: Mantenimiento (NOM-009)', 40, 'En Progreso', 'Buen control en altura, reforzar la revisión de líneas de vida antes de ascender.'),
  (3, 2, 'Fase 1: Instalación (NOM-029)', 100, 'Completado', 'Excelente dominio de los procedimientos eléctricos y uso de equipo de protección básico.'),
  (4, 2, 'Fase 2: Mantenimiento (NOM-009)', 40, 'En Progreso', 'Buen control en altura, reforzar la revisión de líneas de vida antes de ascender.');

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Fin del dump | JuDy Simulator
-- ============================================================
