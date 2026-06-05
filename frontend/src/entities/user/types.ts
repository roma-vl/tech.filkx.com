export interface User {
  id: number;
  name: string;
  email: string;
  phone?: string | null;
  avatar?: string | null;
  avatarUrl?: string | null;
  role?: string;
  locale?: string;
  emailVerifiedAt?: string | null;
  createdAt?: string;
  updatedAt?: string;
}

export interface UserSession {
  id: string;
  ipAddress: string;
  userAgent: string;
  isCurrent: boolean;
  lastActiveAt: string;
}

export interface SystemNotification {
  id: string | number;
  type: string;
  title: string;
  message: string;
  readAt?: string | null;
  createdAt: string;
}
