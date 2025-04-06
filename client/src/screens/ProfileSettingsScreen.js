import React, { useState, useEffect } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  Image,
  ActivityIndicator,
  Alert
} from 'react-native';
import * as ImagePicker from 'expo-image-picker';
import { Picker } from '@react-native-picker/picker';
import CustomInput from '../components/CustomInput';
import CustomButton from '../components/CustomButton';
import userService from '../services/userService';
import { useAuth } from '../context/AuthContext';
import Icon from 'react-native-vector-icons/FontAwesome5';

// Lista de idiomas disponibles
const languages = [
  { code: 'es', name: 'Español' },
  { code: 'en', name: 'English' },
  { code: 'pt', name: 'Português' },
  { code: 'fr', name: 'Français' }
];

// Lista de países (ejemplo reducido)
const countries = [
  { code: 'CR', name: 'Costa Rica' },
  { code: 'US', name: 'Estados Unidos' },
  { code: 'ES', name: 'España' },
  { code: 'MX', name: 'México' },
  { code: 'AR', name: 'Argentina' },
  { code: 'CO', name: 'Colombia' },
  { code: 'PE', name: 'Perú' },
  { code: 'CL', name: 'Chile' }
];

const ProfileSettingsScreen = () => {
  const { user, updateUser } = useAuth();
  const [loading, setLoading] = useState(false);
  const [profileData, setProfileData] = useState({
    full_name: user?.full_name || '',
    phone: user?.phone || '',
    language: user?.language || 'es',
    country: user?.country || '',
    profile_photo: user?.profile_photo
  });

  // Solicitar permisos de la cámara al montar el componente
  useEffect(() => {
    (async () => {
      const { status } = await ImagePicker.requestMediaLibraryPermissionsAsync();
      if (status !== 'granted') {
        Alert.alert(
          'Permisos requeridos',
          'Se necesitan permisos para acceder a la galería'
        );
      }
    })();
  }, []);

  // Función para seleccionar una imagen
  const handleImagePick = async () => {
    try {
      const result = await ImagePicker.launchImageLibraryAsync({
        mediaTypes: ImagePicker.MediaTypeOptions.Images,
        allowsEditing: true,
        aspect: [1, 1],
        quality: 0.8,
      });

      if (!result.canceled) {
        setProfileData(prev => ({
          ...prev,
          profile_photo: result.assets[0]
        }));
      }
    } catch (error) {
      Alert.alert('Error', 'No se pudo cargar la imagen');
    }
  };

  // Función para guardar los cambios
  const handleSave = async () => {
    try {
      setLoading(true);
      const response = await userService.updateProfile(profileData);
      
      if (response.success) {
        updateUser(response.user);
        Alert.alert('Éxito', 'Perfil actualizado correctamente');
      } else {
        throw new Error(response.error || 'Error al actualizar el perfil');
      }
    } catch (error) {
      Alert.alert('Error', error.message || 'No se pudo actualizar el perfil');
    } finally {
      setLoading(false);
    }
  };

  return (
    <ScrollView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.title}>Configuración del Perfil</Text>
      </View>

      {/* Sección de foto de perfil */}
      <View style={styles.photoSection}>
        <TouchableOpacity onPress={handleImagePick} style={styles.photoContainer}>
          {profileData.profile_photo ? (
            <Image
              source={{ 
                uri: profileData.profile_photo.uri || profileData.profile_photo 
              }}
              style={styles.profilePhoto}
            />
          ) : (
            <View style={styles.photoPlaceholder}>
              <Icon name="user" size={40} color="#666" />
            </View>
          )}
          <View style={styles.editIconContainer}>
            <Icon name="camera" size={16} color="#fff" />
          </View>
        </TouchableOpacity>
        <Text style={styles.photoText}>Toca para cambiar la foto</Text>
      </View>

      {/* Formulario */}
      <View style={styles.form}>
        <CustomInput
          label="Nombre Completo"
          value={profileData.full_name}
          onChangeText={(text) => setProfileData(prev => ({ ...prev, full_name: text }))}
          placeholder="Ingresa tu nombre completo"
        />

        <CustomInput
          label="Teléfono"
          value={profileData.phone}
          onChangeText={(text) => setProfileData(prev => ({ ...prev, phone: text }))}
          placeholder="Ingresa tu número de teléfono"
          keyboardType="phone-pad"
        />

        <Text style={styles.label}>Idioma</Text>
        <View style={styles.pickerContainer}>
          <Picker
            selectedValue={profileData.language}
            onValueChange={(value) => setProfileData(prev => ({ ...prev, language: value }))}
            style={styles.picker}
          >
            {languages.map(lang => (
              <Picker.Item 
                key={lang.code} 
                label={lang.name} 
                value={lang.code} 
              />
            ))}
          </Picker>
        </View>

        <Text style={styles.label}>País</Text>
        <View style={styles.pickerContainer}>
          <Picker
            selectedValue={profileData.country}
            onValueChange={(value) => setProfileData(prev => ({ ...prev, country: value }))}
            style={styles.picker}
          >
            <Picker.Item label="Selecciona un país" value="" />
            {countries.map(country => (
              <Picker.Item 
                key={country.code} 
                label={country.name} 
                value={country.code} 
              />
            ))}
          </Picker>
        </View>

        <CustomButton
          title={loading ? 'Guardando...' : 'Guardar Cambios'}
          onPress={handleSave}
          disabled={loading}
        />
      </View>

      {loading && (
        <View style={styles.loadingOverlay}>
          <ActivityIndicator size="large" color="#2c3e50" />
        </View>
      )}
    </ScrollView>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  header: {
    padding: 20,
    backgroundColor: '#2c3e50',
    alignItems: 'center',
  },
  title: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#fff',
  },
  photoSection: {
    alignItems: 'center',
    padding: 20,
  },
  photoContainer: {
    width: 120,
    height: 120,
    borderRadius: 60,
    backgroundColor: '#e1e1e1',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 10,
    position: 'relative',
  },
  profilePhoto: {
    width: '100%',
    height: '100%',
    borderRadius: 60,
  },
  photoPlaceholder: {
    width: '100%',
    height: '100%',
    borderRadius: 60,
    backgroundColor: '#e1e1e1',
    justifyContent: 'center',
    alignItems: 'center',
  },
  editIconContainer: {
    position: 'absolute',
    bottom: 0,
    right: 0,
    backgroundColor: '#2c3e50',
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 3,
    borderColor: '#fff',
  },
  photoText: {
    color: '#666',
    marginTop: 10,
  },
  form: {
    padding: 20,
  },
  label: {
    fontSize: 16,
    color: '#333',
    marginBottom: 8,
    marginTop: 16,
  },
  pickerContainer: {
    backgroundColor: '#fff',
    borderRadius: 8,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: '#ddd',
  },
  picker: {
    height: 50,
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(255,255,255,0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
});

export default ProfileSettingsScreen;