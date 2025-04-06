import React from 'react';
import { View, Text, StyleSheet, TouchableOpacity, Image, SafeAreaView } from 'react-native';
import { DrawerContentScrollView, DrawerItemList, createDrawerNavigator } from '@react-navigation/drawer';
import { useNavigation } from '@react-navigation/native';
import { useAuth } from '../context/AuthContext';
import Icon from 'react-native-vector-icons/FontAwesome5';

const Drawer = createDrawerNavigator();

// Componente para el contenido personalizado del drawer
const CustomDrawerContent = (props) => {
  const { user, logout } = useAuth();
  
  return (
    <DrawerContentScrollView {...props} 
      style={styles.drawerContent}
      contentContainerStyle={styles.drawerContainer}>
      
      {/* Header del Drawer */}
      <View style={styles.drawerHeader}>
        <View style={styles.userInfoSection}>
          <Image
            source={user?.profile_photo 
              ? { uri: user.profile_photo }
              : require('../assets/default-avatar.png')}
            style={styles.userPhoto}
          />
          <Text style={styles.userName}>{user?.full_name || user?.username}</Text>
          <Text style={styles.userEmail}>{user?.email}</Text>
        </View>
      </View>

      {/* Lista de opciones del menú */}
      <View style={styles.drawerSection}>
        <DrawerItemList {...props} />
      </View>

      {/* Botón de cerrar sesión */}
      <TouchableOpacity 
        style={styles.logoutButton}
        onPress={logout}>
        <Icon name="sign-out-alt" size={20} color="#FF4444" />
        <Text style={styles.logoutText}>Cerrar Sesión</Text>
      </TouchableOpacity>
    </DrawerContentScrollView>
  );
};

// Componente principal del Dashboard
const DashboardHome = () => {
  return (
    <SafeAreaView style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.welcomeText}>Bienvenido a tu Dashboard</Text>
      </View>
      <View style={styles.content}>
        <Text style={styles.contentText}>
          Selecciona una opción del menú para comenzar
        </Text>
      </View>
    </SafeAreaView>
  );
};

// Navegador del Dashboard
const DashboardScreen = () => {
  return (
    <Drawer.Navigator
      drawerContent={(props) => <CustomDrawerContent {...props} />}
      screenOptions={{
        drawerStyle: styles.drawer,
        drawerActiveBackgroundColor: '#e6e6e6',
        drawerActiveTintColor: '#000',
        drawerInactiveTintColor: '#333',
        headerStyle: styles.headerStyle,
        headerTintColor: '#fff',
      }}>
      <Drawer.Screen
        name="DashboardHome"
        component={DashboardHome}
        options={{
          title: 'Inicio',
          drawerIcon: ({ color }) => (
            <Icon name="home" size={20} color={color} />
          ),
        }}
      />
      <Drawer.Screen
        name="ProfileSettings"
        component={ProfileSettingsScreen}
        options={{
          title: 'Configuración y Perfil',
          drawerIcon: ({ color }) => (
            <Icon name="user-cog" size={20} color={color} />
          ),
        }}
      />
    </Drawer.Navigator>
  );
};

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f5f5',
  },
  drawer: {
    width: 280,
  },
  drawerContent: {
    flex: 1,
  },
  drawerContainer: {
    flex: 1,
    backgroundColor: '#fff',
  },
  drawerHeader: {
    padding: 20,
    backgroundColor: '#2c3e50',
    marginBottom: 10,
  },
  userInfoSection: {
    alignItems: 'center',
    marginTop: 10,
  },
  userPhoto: {
    width: 80,
    height: 80,
    borderRadius: 40,
    marginBottom: 10,
  },
  userName: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  userEmail: {
    color: '#ecf0f1',
    fontSize: 14,
  },
  drawerSection: {
    flex: 1,
    marginTop: 10,
  },
  logoutButton: {
    padding: 20,
    borderTopWidth: 1,
    borderTopColor: '#f4f4f4',
    flexDirection: 'row',
    alignItems: 'center',
  },
  logoutText: {
    marginLeft: 10,
    color: '#FF4444',
    fontWeight: '500',
  },
  header: {
    backgroundColor: '#2c3e50',
    padding: 20,
    alignItems: 'center',
  },
  welcomeText: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 20,
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentText: {
    fontSize: 16,
    color: '#666',
    textAlign: 'center',
  },
  headerStyle: {
    backgroundColor: '#2c3e50',
  },
});

export default DashboardScreen;