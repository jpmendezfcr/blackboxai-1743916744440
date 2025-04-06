import axios from 'axios';
import { API_URL } from '../config';

const userService = {
  // Obtener el perfil del usuario actual
  getProfile: async () => {
    try {
      const response = await axios.get(`${API_URL}/api/user.php`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { error: 'Error al obtener el perfil' };
    }
  },

  // Actualizar el perfil del usuario
  updateProfile: async (profileData) => {
    try {
      const formData = new FormData();

      // Agregar los campos de texto al FormData
      Object.keys(profileData).forEach(key => {
        if (key !== 'profile_photo') {
          formData.append(key, profileData[key]);
        }
      });

      // Si hay una foto nueva, agregarla al FormData
      if (profileData.profile_photo && profileData.profile_photo.uri) {
        const photoUri = profileData.profile_photo.uri;
        const filename = photoUri.split('/').pop();
        const match = /\.(\w+)$/.exec(filename);
        const type = match ? `image/${match[1]}` : 'image';

        formData.append('profile_photo', {
          uri: photoUri,
          name: filename,
          type
        });
      }

      const response = await axios.post(`${API_URL}/api/updateProfile.php`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });

      return response.data;
    } catch (error) {
      throw error.response?.data || { error: 'Error al actualizar el perfil' };
    }
  }
};

export default userService;