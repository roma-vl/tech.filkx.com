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

// Raw shape from the API - same as /catalog/products/{slug}, deliberately not
// re-wrapped server-side (see PromoPageResource for the same convention) so
// mapCatalogProduct() can map it exactly as it does everywhere else.
export interface SupportTicketProduct {
  id: number;
  slug: string;
  name: string | Record<string, string>;
  variants?: any[];
}

// A pre-resolved, display-ready summary (already-picked-locale name, already-
// resolved image URL) - what a page passes in when starting a *new* chat
// about a product it already has mapped (e.g. the product detail page),
// before any ticket/SupportTicketProduct exists to derive one from.
export interface SupportTicketProductSummary {
  id: number;
  slug: string;
  name: string;
  image: string;
}

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
  product?: SupportTicketProduct | null;
  createdAt: string;
  updatedAt: string;
}
