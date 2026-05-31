export const OrderStatuses = {
  PENDING: "pending",
  PROCESSING: "processing",
  SHIPPED: "shipped",
  DELIVERED: "delivered",
  CANCELLED: "cancelled",
} as const;

export type OrderStatus = typeof OrderStatuses[keyof typeof OrderStatuses];

export const DeliveryMethods = {
  NOVA_POSHTA: "nova_poshta",
  UKR_POSHTA: "ukr_poshta",
  COURIER: "courier",
  PICKUP: "pickup",
} as const;

export const PaymentMethods = {
  COD: "cod",
  CARD: "card",
  BANK: "bank",
} as const;
