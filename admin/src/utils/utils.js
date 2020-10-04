import { isSuperAdmin, isAdmin } from '../auth/permissions';

export const defaultExporterFunctionSuperAdmin = () => (isSuperAdmin() ? undefined : false);

export const defaultExporterFunctionAdmin = () => (isAdmin() ? undefined : false);
