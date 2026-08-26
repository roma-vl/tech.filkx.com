import { defineStore } from "pinia";
import { supportApi } from "@/shared/services/api/supportApi";
import { SupportTicket, SupportTicketProductSummary } from "../types";

const POLL_INTERVAL_MS = 30000;
const NEW_TICKET_SUBJECT_MAX_LENGTH = 60;

export type SupportWidgetView = "home" | "chat";

export interface SupportState {
  isOpen: boolean;
  view: SupportWidgetView;
  tickets: SupportTicket[];
  activeTicket: SupportTicket | null;
  // Set only while composing a *new* ticket started from a product page -
  // lets the compose view show what product the shopper is asking about
  // before the ticket (and its own `product` relation) exists yet.
  pendingProduct: SupportTicketProductSummary | null;
  loadingTickets: boolean;
  loadingTicket: boolean;
  sending: boolean;
  pollIntervalId: ReturnType<typeof setInterval> | null;
}

export const useSupportStore = defineStore("support", {
  state: (): SupportState => ({
    isOpen: false,
    view: "home",
    tickets: [],
    activeTicket: null,
    pendingProduct: null,
    loadingTickets: false,
    loadingTicket: false,
    sending: false,
    pollIntervalId: null,
  }),

  getters: {
    unreadCount: (state): number =>
      state.tickets.reduce((sum, ticket) => sum + (ticket.unreadCount || 0), 0),

    activeTicketMessages: (state) =>
      state.activeTicket?.publicMessages ?? state.activeTicket?.messages ?? [],
  },

  actions: {
    open() {
      this.isOpen = true;
    },

    close() {
      this.isOpen = false;
    },

    toggleOpen() {
      this.isOpen = !this.isOpen;
    },

    goHome() {
      this.view = "home";
      this.activeTicket = null;
      this.pendingProduct = null;
    },

    startNewChat(product?: SupportTicketProductSummary) {
      this.isOpen = true;
      this.view = "chat";
      this.activeTicket = null;
      this.pendingProduct = product ?? null;
    },

    startPolling() {
      if (this.pollIntervalId !== null) return;

      this.fetchTickets();

      if (typeof window === "undefined") return;
      this.pollIntervalId = setInterval(
        () => this.fetchTickets(),
        POLL_INTERVAL_MS,
      );
    },

    stopPolling() {
      if (this.pollIntervalId === null) return;

      clearInterval(this.pollIntervalId);
      this.pollIntervalId = null;
    },

    async fetchTickets() {
      this.loadingTickets = true;
      try {
        const { data } = await supportApi.getTickets();
        this.tickets = data?.data ?? [];
      } catch (error) {
        console.error("Failed to fetch support tickets", error);
      } finally {
        this.loadingTickets = false;
      }
    },

    async selectTicket(ticket: SupportTicket) {
      this.view = "chat";
      this.activeTicket = ticket;
      this.pendingProduct = null;
      this.loadingTicket = true;
      try {
        const { data } = await supportApi.getTicket(ticket.id);
        this.activeTicket = data?.data ?? ticket;
        this.markAsRead(ticket.id);
      } catch (error) {
        console.error("Failed to load support ticket", error);
      } finally {
        this.loadingTicket = false;
      }
    },

    async markAsRead(ticketId: number | string) {
      const ticket = this.tickets.find((t) => t.id === ticketId);
      if (ticket) ticket.unreadCount = 0;

      try {
        await supportApi.markAsRead(ticketId);
      } catch (error) {
        console.error("Failed to mark support ticket as read", error);
      }
    },

    async sendMessage(text: string) {
      const trimmed = text.trim();
      if (!trimmed || this.sending) return;

      if (!this.activeTicket) {
        await this.createTicket(trimmed);
        return;
      }

      this.sending = true;
      try {
        const { data } = await supportApi.sendMessage(
          this.activeTicket.id,
          trimmed,
        );
        const newMessage = data?.data;
        if (!newMessage) return;

        if (this.activeTicket.publicMessages) {
          this.activeTicket.publicMessages.push(newMessage);
        } else {
          this.activeTicket.publicMessages = [newMessage];
        }
        this.fetchTickets();
      } catch (error) {
        console.error("Failed to send support message", error);
      } finally {
        this.sending = false;
      }
    },

    async createTicket(text: string) {
      this.sending = true;
      try {
        const subject =
          text.length > NEW_TICKET_SUBJECT_MAX_LENGTH
            ? `${text.slice(0, NEW_TICKET_SUBJECT_MAX_LENGTH)}…`
            : text;
        const { data } = await supportApi.createTicket(
          subject,
          text,
          this.pendingProduct?.id,
        );
        this.activeTicket = data?.data ?? null;
        this.pendingProduct = null;
        this.fetchTickets();
      } catch (error) {
        console.error("Failed to create support ticket", error);
      } finally {
        this.sending = false;
      }
    },

    reset() {
      this.stopPolling();
      this.isOpen = false;
      this.view = "home";
      this.tickets = [];
      this.activeTicket = null;
      this.pendingProduct = null;
      this.loadingTickets = false;
      this.loadingTicket = false;
      this.sending = false;
    },
  },
});

export default useSupportStore;
