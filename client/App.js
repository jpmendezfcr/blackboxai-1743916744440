import React from 'react';
import { StatusBar } from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import { AuthProvider } from './src/context/AuthContext';
import AppNavigator from './src/navigation/AppNavigator';
import { colors } from './src/styles/common';

const App = () => {
  return (
    <AuthProvider>
      <NavigationContainer>
        <StatusBar
          backgroundColor={colors.primary}
          barStyle="light-content"
        />
        <AppNavigator />
      </NavigationContainer>
    </AuthProvider>
  );
};

export default App;
