// Configuración de la aplicación
export const API_URL = 'http://localhost:8000'; // URL base de la API

// Configuración de idiomas disponibles
export const LANGUAGES = [
  { code: 'es', name: 'Español' },
  { code: 'en', name: 'English' },
  { code: 'pt', name: 'Português' },
  { code: 'fr', name: 'Français' }
];

// Configuración de países disponibles
export const COUNTRIES = [
  { code: 'CR', name: 'Costa Rica' },
  { code: 'US', name: 'Estados Unidos' },
  { code: 'ES', name: 'España' },
  { code: 'MX', name: 'México' },
  { code: 'AR', name: 'Argentina' },
  { code: 'CO', name: 'Colombia' },
  { code: 'PE', name: 'Perú' },
  { code: 'CL', name: 'Chile' }
];

// Configuración de la aplicación
export const APP_CONFIG = {
  defaultLanguage: 'es',
  maxImageSize: 5 * 1024 * 1024, // 5MB en bytes
  supportedImageTypes: ['image/jpeg', 'image/png'],
  defaultProfilePhoto: require('./assets/default-avatar.png'),
};

// Configuración de mensajes de error
export const ERROR_MESSAGES = {
  invalidImageType: 'El formato de imagen no es válido. Use JPG o PNG.',
  invalidImageSize: 'La imagen es demasiado grande. Máximo 5MB.',
  networkError: 'Error de conexión. Intente nuevamente.',
  updateError: 'Error al actualizar el perfil.',
  permissionDenied: 'Permiso denegado para acceder a la galería.',
};

// Configuración de mensajes de éxito
export const SUCCESS_MESSAGES = {
  profileUpdated: 'Perfil actualizado correctamente',
};