export interface SupportMessage {
  id: number;
  message: string | null;
  filePath: string | null;
  fileType: string | null;
  fileName: string | null;
  fileSize: number | null;
  isAdmin: boolean;
  createdAt: string;
}

export type SupportTicketStatus =
  | "new"
  | "accepted"
  | "done"
  | "archived"
  | "deleted";

export interface SupportTicket {
  id: number;
  subject: string;
  status: SupportTicketStatus;
  handledBy: "human" | "ai" | null;
  unreadCount?: number;
  readAt: string | null;
  lastMessage?: SupportMessage | null;
  messages?: SupportMessage[];
  publicMessages?: SupportMessage[];
  createdAt: string;
  updatedAt: string;
}
