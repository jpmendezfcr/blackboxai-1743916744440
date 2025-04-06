// API Configuration
export const API_URL = 'http://localhost:8000';

// Default avatar image (as base64 or URL)
export const DEFAULT_AVATAR = 'https://via.placeholder.com/150';

// Available languages
export const LANGUAGES = [
  { code: 'es', name: 'Español' },
  { code: 'en', name: 'English' },
  { code: 'pt', name: 'Português' },
  { code: 'fr', name: 'Français' }
];

// Available countries
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

// App Configuration
export const APP_CONFIG = {
  appName: 'AVI',
  version: '1.0.0',
  defaultLanguage: 'es',
  maxImageSize: 5 * 1024 * 1024, // 5MB in bytes
  supportedImageTypes: ['image/jpeg', 'image/png'],
  sessionTimeout: 24 * 60 * 60 * 1000, // 24 hours in milliseconds
};

// Error Messages
export const ERROR_MESSAGES = {
  invalidEmail: 'Por favor ingrese un email válido',
  invalidPassword: 'La contraseña debe tener al menos 6 caracteres',
  passwordMismatch: 'Las contraseñas no coinciden',
  requiredField: 'Este campo es requerido',
  invalidImageType: 'El formato de imagen no es válido. Use JPG o PNG',
  invalidImageSize: 'La imagen es demasiado grande. Máximo 5MB',
  networkError: 'Error de conexión. Intente nuevamente',
  loginError: 'Credenciales inválidas',
  registerError: 'Error al registrar usuario',
  updateError: 'Error al actualizar el perfil',
  permissionDenied: 'Permiso denegado para acceder a la galería',
};

// Success Messages
export const SUCCESS_MESSAGES = {
  loginSuccess: 'Inicio de sesión exitoso',
  registerSuccess: 'Registro exitoso',
  profileUpdated: 'Perfil actualizado correctamente',
  passwordReset: 'Contraseña restablecida correctamente',
  emailSent: 'Correo enviado correctamente',
};

// Navigation Routes
export const ROUTES = {
  LOGIN: 'Login',
  REGISTER: 'Register',
  FORGOT_PASSWORD: 'ForgotPassword',
  DASHBOARD: 'Dashboard',
  PROFILE_SETTINGS: 'ProfileSettings',
};

// Storage Keys
export const STORAGE_KEYS = {
  TOKEN: 'auth_token',
  USER: 'user_data',
  SETTINGS: 'app_settings',
};

// API Endpoints
export const ENDPOINTS = {
  LOGIN: '/api/auth.php',
  REGISTER: '/api/auth.php',
  FORGOT_PASSWORD: '/api/auth.php',
  UPDATE_PROFILE: '/api/updateProfile.php',
  UPDATE_SETTINGS: '/api/updateSettings.php',
  UPLOAD_PHOTO: '/api/uploadPhoto.php',
};

// Theme Configuration
export const THEME = {
  colors: {
    primary: '#2c3e50',
    secondary: '#34495e',
    accent: '#3498db',
    success: '#2ecc71',
    danger: '#e74c3c',
    warning: '#f1c40f',
    info: '#3498db',
    light: '#ecf0f1',
    dark: '#2c3e50',
    white: '#ffffff',
    black: '#000000',
    gray: '#95a5a6',
    lightGray: '#bdc3c7',
  },
  fonts: {
    regular: 'System',
    medium: 'System',
    bold: 'System',
  },
  spacing: {
    xs: 4,
    sm: 8,
    md: 16,
    lg: 24,
    xl: 32,
  },
  borderRadius: {
    sm: 4,
    md: 8,
    lg: 16,
    xl: 24,
  },
};