export const getPermissions = () => {
  const storagePermissions = localStorage.getItem('permission');
  const permissions = storagePermissions && JSON.parse(storagePermissions);
  return Array.isArray(permissions) ? permissions : [];
};

export const createPermissionChecker = (permissions = []) => (action) =>
  permissions.includes(action);

export const isAdmin = () => {
  const roles = JSON.parse(localStorage.getItem('roles') || '[]');
  return roles.includes('ROLE_SUPER_ADMIN') || roles.includes('ROLE_ADMIN');
};

export const isSuperAdmin = () => {
  const roles = JSON.parse(localStorage.getItem('roles') || '[]');
  return roles.includes('ROLE_SUPER_ADMIN');
};

export default (action) => {
  const hasPermission = createPermissionChecker(getPermissions());
  return hasPermission(action);
};
