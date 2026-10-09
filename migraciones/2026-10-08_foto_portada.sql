-- Momentia · Foto de portada personal (módulo Deluxe mod_foto_portada)
-- Ejecutar una sola vez sobre una base existente (phpMyAdmin > SQL, o mysql < archivo).
-- Las instalaciones nuevas ya lo traen en tarjeta_virtual.sql.

ALTER TABLE `tarjetas`
  ADD COLUMN `mod_foto_portada` tinyint(1) DEFAULT 0,
  ADD COLUMN `foto_portada` varchar(255) DEFAULT NULL;

INSERT INTO `costos` (`modulo_key`, `nombre_amigable`, `precio`)
VALUES ('mod_foto_portada', 'Foto de portada (Deluxe)', 2500.00)
ON DUPLICATE KEY UPDATE `nombre_amigable` = VALUES(`nombre_amigable`);
