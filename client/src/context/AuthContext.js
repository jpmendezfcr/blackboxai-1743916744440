import React, { createContext, useState, useContext, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import authService from '../services/authService';

const AuthContext = createContext({});

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  // Verificar si hay un usuario almacenado al iniciar la app
  useEffect(() => {
    checkUser();
  }, []);

  const checkUser = async () => {
    try {
      const userData = await AsyncStorage.getItem('user');
      if (userData) {
        setUser(JSON.parse(userData));
      }
    } catch (error) {
      console.error('Error al recuperar usuario:', error);
    } finally {
      setIsLoading(false);
    }
  };

  // Función de login
  const login = async (email, password) => {
    try {
      const response = await authService.login(email, password);
      const userData = response.user;
      
      await AsyncStorage.setItem('user', JSON.stringify(userData));
      setUser(userData);
      
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.message || 'Error al iniciar sesión'
      };
    }
  };

  // Función de registro
  const register = async (userData) => {
    try {
      const response = await authService.register(userData);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.message || 'Error en el registro'
      };
    }
  };

  // Función para actualizar datos del usuario
  const updateUser = async (newUserData) => {
    try {
      // Actualizar el estado y el almacenamiento local
      const updatedUser = { ...user, ...newUserData };
      await AsyncStorage.setItem('user', JSON.stringify(updatedUser));
      setUser(updatedUser);
      
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.message || 'Error al actualizar usuario'
      };
    }
  };

  // Función de logout
  const logout = async () => {
    try {
      await authService.logout();
      await AsyncStorage.removeItem('user');
      setUser(null);
    } catch (error) {
      console.error('Error al cerrar sesión:', error);
    }
  };

  // Función para recuperar contraseña
  const forgotPassword = async (email) => {
    try {
      await authService.forgotPassword(email);
      return { success: true };
    } catch (error) {
      return {
        success: false,
        error: error.message || 'Error al procesar la solicitud'
      };
    }
  };

  return (
    <AuthContext.Provider
      value={{
        user,
        isLoading,
        login,
        logout,
        register,
        updateUser,
        forgotPassword
      }}>
      {children}
    </AuthContext.Provider>
  );
};

// Hook personalizado para usar el contexto
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth debe ser usado dentro de un AuthProvider');
  }
  return context;
};

export default AuthContext;