import { describe, it, expect, vi, beforeEach, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";

vi.mock("@/shared/services/api/supportApi", () => ({
  supportApi: {
    getTickets: vi.fn(),
    createTicket: vi.fn(),
    getTicket: vi.fn(),
    sendMessage: vi.fn(),
    markAsRead: vi.fn(),
  },
}));

import { supportApi } from "@/shared/services/api/supportApi";
import { useSupportStore } from "./supportStore";
import type { SupportTicket } from "../types";

function makeTicket(overrides: Partial<SupportTicket> = {}): SupportTicket {
  return {
    id: 1,
    subject: "Test ticket",
    status: "new",
    handledBy: "human",
    unreadCount: 0,
    readAt: null,
    createdAt: "2026-01-01T00:00:00Z",
    updatedAt: "2026-01-01T00:00:00Z",
    ...overrides,
  };
}

describe("supportStore getters", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("unreadCount is 0 with no tickets", () => {
    const store = useSupportStore();
    expect(store.unreadCount).toBe(0);
  });

  it("unreadCount sums unreadCount across tickets", () => {
    const store = useSupportStore();
    store.tickets = [
      makeTicket({ id: 1, unreadCount: 2 }),
      makeTicket({ id: 2, unreadCount: 3 }),
      makeTicket({ id: 3, unreadCount: undefined }),
    ];
    expect(store.unreadCount).toBe(5);
  });

  it("activeTicketMessages prefers publicMessages over messages", () => {
    const store = useSupportStore();
    store.activeTicket = makeTicket({
      messages: [{ id: 1 } as any],
      publicMessages: [{ id: 2 } as any, { id: 3 } as any],
    });
    expect(store.activeTicketMessages).toHaveLength(2);
  });

  it("activeTicketMessages falls back to messages when publicMessages is absent", () => {
    const store = useSupportStore();
    store.activeTicket = makeTicket({ messages: [{ id: 1 } as any] });
    expect(store.activeTicketMessages).toHaveLength(1);
  });

  it("activeTicketMessages is empty with no active ticket", () => {
    const store = useSupportStore();
    expect(store.activeTicketMessages).toEqual([]);
  });
});

describe("supportStore view transitions", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  it("startNewChat opens the panel, switches to chat, and clears the active ticket", () => {
    const store = useSupportStore();
    store.isOpen = false;
    store.activeTicket = makeTicket();

    store.startNewChat();

    expect(store.isOpen).toBe(true);
    expect(store.view).toBe("chat");
    expect(store.activeTicket).toBeNull();
  });

  it("goHome switches back to home and clears the active ticket", () => {
    const store = useSupportStore();
    store.view = "chat";
    store.activeTicket = makeTicket();

    store.goHome();

    expect(store.view).toBe("home");
    expect(store.activeTicket).toBeNull();
  });

  it("toggleOpen flips isOpen", () => {
    const store = useSupportStore();
    expect(store.isOpen).toBe(false);
    store.toggleOpen();
    expect(store.isOpen).toBe(true);
    store.toggleOpen();
    expect(store.isOpen).toBe(false);
  });
});

describe("supportStore polling lifecycle", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.useFakeTimers();
    vi.mocked(supportApi.getTickets).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);
  });

  afterEach(() => {
    vi.useRealTimers();
    vi.clearAllMocks();
  });

  it("startPolling fetches immediately and then on every interval", async () => {
    const store = useSupportStore();
    store.startPolling();
    await vi.advanceTimersByTimeAsync(0);
    expect(supportApi.getTickets).toHaveBeenCalledTimes(1);

    await vi.advanceTimersByTimeAsync(30000);
    expect(supportApi.getTickets).toHaveBeenCalledTimes(2);

    await vi.advanceTimersByTimeAsync(30000);
    expect(supportApi.getTickets).toHaveBeenCalledTimes(3);
  });

  it("startPolling is a no-op if already polling", async () => {
    const store = useSupportStore();
    store.startPolling();
    await vi.advanceTimersByTimeAsync(0);
    store.startPolling();
    expect(supportApi.getTickets).toHaveBeenCalledTimes(1);
  });

  it("stopPolling clears the interval so no further fetches happen", async () => {
    const store = useSupportStore();
    store.startPolling();
    await vi.advanceTimersByTimeAsync(0);
    store.stopPolling();

    await vi.advanceTimersByTimeAsync(60000);
    expect(supportApi.getTickets).toHaveBeenCalledTimes(1);
  });

  it("reset stops polling and clears widget state", async () => {
    const store = useSupportStore();
    store.startPolling();
    await vi.advanceTimersByTimeAsync(0);
    store.isOpen = true;
    store.activeTicket = makeTicket();

    store.reset();

    expect(store.isOpen).toBe(false);
    expect(store.activeTicket).toBeNull();
    expect(store.tickets).toEqual([]);

    await vi.advanceTimersByTimeAsync(60000);
    expect(supportApi.getTickets).toHaveBeenCalledTimes(1);
  });
});

describe("supportStore markAsRead", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.mocked(supportApi.markAsRead).mockResolvedValue({} as any);
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it("optimistically zeroes the ticket's unreadCount", async () => {
    const store = useSupportStore();
    store.tickets = [makeTicket({ id: 5, unreadCount: 4 })];

    await store.markAsRead(5);

    expect(store.tickets[0].unreadCount).toBe(0);
    expect(supportApi.markAsRead).toHaveBeenCalledWith(5);
  });
});

describe("supportStore sendMessage", () => {
  beforeEach(() => {
    setActivePinia(createPinia());
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it("creates a new ticket when there is no active ticket", async () => {
    const store = useSupportStore();
    vi.mocked(supportApi.createTicket).mockResolvedValue({
      data: { status: "success", data: makeTicket({ id: 9 }) },
    } as any);
    vi.mocked(supportApi.getTickets).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);

    await store.sendMessage("Hello, I need help");

    expect(supportApi.createTicket).toHaveBeenCalledWith(
      "Hello, I need help",
      "Hello, I need help",
    );
    expect(store.activeTicket?.id).toBe(9);
  });

  it("truncates long messages into the new ticket's subject", async () => {
    const store = useSupportStore();
    const longText = "a".repeat(80);
    vi.mocked(supportApi.createTicket).mockResolvedValue({
      data: { status: "success", data: makeTicket() },
    } as any);
    vi.mocked(supportApi.getTickets).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);

    await store.sendMessage(longText);

    const [subject, message] = vi.mocked(supportApi.createTicket).mock.calls[0];
    expect(subject.length).toBe(61); // 60 chars + ellipsis
    expect(message).toBe(longText);
  });

  it("appends the sent message to the active ticket instead of creating a new one", async () => {
    const store = useSupportStore();
    store.activeTicket = makeTicket({ id: 2, publicMessages: [] });
    vi.mocked(supportApi.sendMessage).mockResolvedValue({
      data: { status: "success", data: { id: 100, message: "Hi" } },
    } as any);
    vi.mocked(supportApi.getTickets).mockResolvedValue({
      data: { status: "success", data: [] },
    } as any);

    await store.sendMessage("Hi");

    expect(supportApi.createTicket).not.toHaveBeenCalled();
    expect(supportApi.sendMessage).toHaveBeenCalledWith(2, "Hi");
    expect(store.activeTicket?.publicMessages).toHaveLength(1);
  });

  it("ignores blank input", async () => {
    const store = useSupportStore();
    await store.sendMessage("   ");
    expect(supportApi.createTicket).not.toHaveBeenCalled();
    expect(supportApi.sendMessage).not.toHaveBeenCalled();
  });
});
