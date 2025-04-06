USE avi_db;

-- Agregar nuevos campos a la tabla de usuarios
ALTER TABLE users
    ADD COLUMN full_name VARCHAR(255) DEFAULT NULL AFTER email,
    ADD COLUMN phone VARCHAR(20) DEFAULT NULL,
    ADD COLUMN profile_photo VARCHAR(255) DEFAULT NULL,
    ADD COLUMN language VARCHAR(50) DEFAULT 'es' COMMENT 'Default language code',
    ADD COLUMN country VARCHAR(2) DEFAULT NULL COMMENT 'ISO 2-letter country code';

-- Crear directorio para almacenar fotos de perfil si no existe
-- Nota: Esto debe ejecutarse manualmente en el servidor:
-- mkdir -p /uploads/profile_photos
-- chmod 755 /uploads/profile_photos

-- Actualizar el índice existente
DROP INDEX idx_email ON users;
CREATE INDEX idx_user_search ON users(email, full_name);