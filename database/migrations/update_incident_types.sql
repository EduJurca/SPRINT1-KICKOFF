-- Migración: Actualizar tipos de incidencias
-- Fecha: 2024
-- Descripción: Cambiar ENUM de tipos de incidencias a los nuevos valores

-- Paso 1: Actualizar los valores existentes para mapearlos a los nuevos tipos
UPDATE incidents SET type = 'technical' WHERE type = 'mechanical';
UPDATE incidents SET type = 'maintenance' WHERE type = 'electrical';
-- 'other' se mantiene igual

-- Paso 2: Modificar la columna con los nuevos valores ENUM
ALTER TABLE incidents 
MODIFY COLUMN type ENUM('technical', 'maintenance', 'user_complaint', 'accident', 'other') NOT NULL;
