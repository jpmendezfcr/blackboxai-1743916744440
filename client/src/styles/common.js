import { StyleSheet } from 'react-native';

export const colors = {
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
};

export const commonStyles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: colors.light,
  },
  header: {
    backgroundColor: colors.primary,
    padding: 20,
    alignItems: 'center',
  },
  headerText: {
    color: colors.white,
    fontSize: 24,
    fontWeight: 'bold',
  },
  content: {
    flex: 1,
    padding: 20,
  },
  inputContainer: {
    marginBottom: 20,
  },
  label: {
    fontSize: 16,
    color: colors.dark,
    marginBottom: 8,
  },
  input: {
    backgroundColor: colors.white,
    borderRadius: 8,
    padding: 12,
    borderWidth: 1,
    borderColor: colors.lightGray,
  },
  button: {
    backgroundColor: colors.primary,
    padding: 15,
    borderRadius: 8,
    alignItems: 'center',
    marginVertical: 10,
  },
  buttonText: {
    color: colors.white,
    fontSize: 16,
    fontWeight: '600',
  },
  errorText: {
    color: colors.danger,
    fontSize: 14,
    marginTop: 5,
  },
  successText: {
    color: colors.success,
    fontSize: 14,
    marginTop: 5,
  },
  drawerHeader: {
    padding: 20,
    backgroundColor: colors.primary,
    marginBottom: 10,
  },
  drawerUserInfo: {
    alignItems: 'center',
    marginTop: 10,
  },
  drawerUserPhoto: {
    width: 80,
    height: 80,
    borderRadius: 40,
    marginBottom: 10,
  },
  drawerUserName: {
    color: colors.white,
    fontSize: 18,
    fontWeight: 'bold',
    marginBottom: 5,
  },
  drawerUserEmail: {
    color: colors.light,
    fontSize: 14,
  },
  drawerSection: {
    flex: 1,
    marginTop: 10,
  },
  drawerItem: {
    padding: 15,
    borderBottomWidth: 1,
    borderBottomColor: colors.lightGray,
    flexDirection: 'row',
    alignItems: 'center',
  },
  drawerItemText: {
    marginLeft: 15,
    fontSize: 16,
    color: colors.dark,
  },
  pickerContainer: {
    backgroundColor: colors.white,
    borderRadius: 8,
    marginBottom: 16,
    borderWidth: 1,
    borderColor: colors.lightGray,
  },
  loadingOverlay: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(255,255,255,0.7)',
    justifyContent: 'center',
    alignItems: 'center',
  },
});