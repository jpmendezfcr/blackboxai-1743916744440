# AVI - Asistente Virtual Inteligente

## Descripción
AVI es una aplicación móvil desarrollada con React Native que proporciona una interfaz de usuario para gestionar perfiles de usuario y configuraciones personalizadas.

## Características
- Gestión de perfil de usuario
- Soporte multiidioma
- Personalización de interfaz
- Sistema de autenticación seguro
- Gestión de imágenes de perfil

## Tecnologías Utilizadas
- React Native
- PHP (Backend)
- MySQL
- React Navigation
- Context API para gestión de estado

## Estructura del Proyecto

```
project/
├── client/
│   ├── src/
│   │   ├── assets/         # Imágenes y recursos estáticos
│   │   ├── components/     # Componentes reutilizables
│   │   ├── context/       # Contextos de React (Auth, etc.)
│   │   ├── navigation/    # Configuración de navegación
│   │   ├── screens/       # Pantallas de la aplicación
│   │   ├── services/      # Servicios API
│   │   └── styles/        # Estilos compartidos
│   └── App.js             # Punto de entrada de la aplicación
│
└── server/
    ├── api/              # Endpoints de la API
    ├── config.php        # Configuración del servidor
    └── uploads/          # Directorio para archivos subidos

```

## Instalación

### Requisitos Previos
- Node.js (v14 o superior)
- PHP 7.4 o superior
- MySQL 5.7 o superior
- Composer

### Configuración del Cliente
1. Instalar dependencias:
```bash
cd client
npm install
```

2. Iniciar la aplicación:
```bash
npm start
```

### Configuración del Servidor
1. Configurar la base de datos:
```bash
mysql -u root < avi_db.sql
```

2. Configurar el archivo config.php con las credenciales de la base de datos

3. Asegurarse de que el directorio uploads/ tenga los permisos correctos:
```bash
chmod 755 server/uploads
```

## Uso
1. Iniciar sesión con sus credenciales
2. Navegar al perfil de usuario para personalizar la configuración
3. Actualizar la foto de perfil y la información personal según sea necesario

## Seguridad
- Todas las contraseñas se almacenan hasheadas
- Validación de datos tanto en cliente como en servidor
- Protección contra SQL injection
- Manejo seguro de archivos subidos

## Contribución
1. Fork el repositorio
2. Crear una rama para su característica (`git checkout -b feature/AmazingFeature`)
3. Commit sus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir un Pull Request

## Licencia
Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.
