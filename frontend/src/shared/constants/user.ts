export const UserRoles = {
  ADMIN: "admin",
  MANAGER: "manager",
  USER: "user",
} as const;

export type UserRole = typeof UserRoles[keyof typeof UserRoles];

export const Permissions = {
  VIEW_ANALYTICS: "view_analytics",
  MANAGE_CATALOG: "manage_catalog",
  MANAGE_ORDERS: "manage_orders",
  MANAGE_USERS: "manage_users",
  MANAGE_SETTINGS: "manage_settings",
} as const;
