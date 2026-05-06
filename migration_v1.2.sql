-- ============================================================
-- OCEANO WORKSPACE — Migración v1.2
-- Ejecutar en phpMyAdmin → pestaña SQL
-- ============================================================

ALTER TABLE users
    ADD COLUMN IF NOT EXISTS cedula        VARCHAR(20)  DEFAULT NULL AFTER area_id,
    ADD COLUMN IF NOT EXISTS telefono      VARCHAR(20)  DEFAULT NULL AFTER cedula,
    ADD COLUMN IF NOT EXISTS fecha_ingreso DATE         DEFAULT NULL AFTER telefono,
    ADD COLUMN IF NOT EXISTS renovacion_contrato DATE   DEFAULT NULL AFTER fecha_ingreso,
    ADD COLUMN IF NOT EXISTS foto_perfil   VARCHAR(255) DEFAULT NULL AFTER renovacion_contrato,
    ADD COLUMN IF NOT EXISTS notas_admin   TEXT         DEFAULT NULL AFTER foto_perfil;

ALTER TABLE areas
    ADD COLUMN IF NOT EXISTS descripcion  VARCHAR(255) DEFAULT NULL AFTER nombre,
    ADD COLUMN IF NOT EXISTS color        VARCHAR(7)   DEFAULT '#3b82f6' AFTER descripcion,
    ADD COLUMN IF NOT EXISTS icono        VARCHAR(50)  DEFAULT 'building-2' AFTER color;
