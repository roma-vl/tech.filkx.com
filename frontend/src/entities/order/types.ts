import { Product } from "../product/types";

export interface OrderItem {
  id: number;
  orderId: number;
  productId: number;
  product?: Product;
  sku: string;
  name: string;
  price: number;
  quantity: number;
  total: number;
}

export interface Order {
  id: number;
  orderNumber: string;
  customerId?: number | null;
  customerName: string;
  customerPhone: string;
  customerEmail: string;
  shippingCountry: string;
  shippingCity: string;
  shippingAddress: string;
  deliveryMethod: string;
  paymentMethod: string;
  status: "pending" | "processing" | "shipped" | "delivered" | "cancelled";
  itemsCount: number;
  subtotal: number;
  discount: number;
  shipping: number;
  tax: number;
  totalPrice: number;
  items?: OrderItem[];
  createdAt: string;
  updatedAt: string;
}

export interface CartItem {
  id: number;
  productId: number;
  variantId?: number;
  name: string;
  sku: string;
  price: number;
  quantity: number;
  image: string;
}

export interface Coupon {
  id: number;
  code: string;
  type: "fixed" | "percentage";
  value: number;
  minAmount?: number;
  isActive: boolean;
  startsAt?: string;
  expiresAt?: string;
  usageLimit?: number;
  usageCount?: number;
}

export interface Promotion {
  id: number;
  name: string;
  description?: string;
  type: "discount" | "banner";
  value?: number;
  isActive: boolean;
  startsAt?: string;
  expiresAt?: string;
}
