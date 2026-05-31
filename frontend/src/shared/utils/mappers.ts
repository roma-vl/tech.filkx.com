import { Product } from "@/entities/product/types";
import { Order } from "@/entities/order/types";
import { Invoice } from "@/entities/invoice/types";
import { User } from "@/entities/user/types";

/**
 * Check if value is a plain object
 */
function isObject(val: any): boolean {
  return val !== null && typeof val === "object" && !Array.isArray(val) && !(val instanceof Date);
}

/**
 * Convert string to camelCase
 */
export function toCamelCase(str: string): string {
  return str.replace(/([-_][a-z])/gi, ($1) => {
    return $1.toUpperCase().replace("-", "").replace("_", "");
  });
}

/**
 * Convert string to snake_case
 */
export function toSnakeCase(str: string): string {
  return str.replace(/[A-Z]/g, (letter) => `_${letter.toLowerCase()}`);
}

/**
 * Recursively convert object keys to camelCase
 */
export function toCamelCaseKeys<T = any>(obj: any): T {
  if (Array.isArray(obj)) {
    return obj.map((v) => toCamelCaseKeys(v)) as any;
  } else if (isObject(obj)) {
    const n: Record<string, any> = {};
    Object.keys(obj).forEach((k) => {
      n[toCamelCase(k)] = toCamelCaseKeys(obj[k]);
    });
    return n as T;
  }
  return obj;
}

/**
 * Recursively convert object keys to snake_case
 */
export function toSnakeCaseKeys<T = any>(obj: any): T {
  if (Array.isArray(obj)) {
    return obj.map((v) => toSnakeCaseKeys(v)) as any;
  } else if (isObject(obj)) {
    const n: Record<string, any> = {};
    Object.keys(obj).forEach((k) => {
      n[toSnakeCase(k)] = toSnakeCaseKeys(obj[k]);
    });
    return n as T;
  }
  return obj;
}

/**
 * DTO Mappers for specific models
 */
export const Mappers = {
  product(dto: any): Product {
    const camel = toCamelCaseKeys(dto);
    return {
      id: Number(camel.id),
      name: camel.name || "",
      slug: camel.slug || "",
      sku: camel.sku || "",
      price: Number(camel.price) || 0,
      salePrice: camel.salePrice ? Number(camel.salePrice) : null,
      stock: Number(camel.stock) || 0,
      image: camel.image || "",
      gallery: camel.gallery || [],
      description: camel.description || "",
      specs: camel.specs || {},
      rating: Number(camel.rating) || 4.5,
      reviews: Number(camel.reviews) || 0,
      isFeatured: Boolean(camel.isFeatured),
      isActive: Boolean(camel.isActive),
      categoryId: camel.categoryId ? Number(camel.categoryId) : undefined,
      category: camel.category,
      brandId: camel.brandId ? Number(camel.brandId) : undefined,
      brand: camel.brand,
      variants: camel.variants,
      createdAt: camel.createdAt,
      updatedAt: camel.updatedAt,
    };
  },

  order(dto: any): Order {
    const camel = toCamelCaseKeys(dto);
    return {
      id: Number(camel.id),
      orderNumber: camel.orderNumber || "",
      customerId: camel.customerId ? Number(camel.customerId) : null,
      customerName: camel.customerName || "",
      customerPhone: camel.customerPhone || "",
      customerEmail: camel.customerEmail || "",
      shippingCountry: camel.shippingCountry || "",
      shippingCity: camel.shippingCity || "",
      shippingAddress: camel.shippingAddress || "",
      deliveryMethod: camel.deliveryMethod || "",
      paymentMethod: camel.paymentMethod || "",
      status: camel.status || "pending",
      itemsCount: Number(camel.itemsCount) || 0,
      subtotal: Number(camel.subtotal) || 0,
      discount: Number(camel.discount) || 0,
      shipping: Number(camel.shipping) || 0,
      tax: Number(camel.tax) || 0,
      totalPrice: Number(camel.totalPrice) || 0,
      items: camel.items || [],
      createdAt: camel.createdAt,
      updatedAt: camel.updatedAt,
    };
  },

  invoice(dto: any): Invoice {
    const camel = toCamelCaseKeys(dto);
    return {
      id: Number(camel.id),
      invoiceNumber: camel.invoiceNumber || "",
      orderNumber: camel.orderNumber || "",
      customerName: camel.customerName || "",
      customerEmail: camel.customerEmail || "",
      amount: Number(camel.amount) || 0,
      status: camel.status || "unpaid",
      dueDate: camel.dueDate || "",
      issuedDate: camel.issuedDate || "",
      paidDate: camel.paidDate || null,
    };
  },

  user(dto: any): User {
    const camel = toCamelCaseKeys(dto);
    return {
      id: Number(camel.id),
      name: camel.name || "",
      email: camel.email || "",
      phone: camel.phone || null,
      avatar: camel.avatar || null,
      role: camel.role || "user",
      locale: camel.locale || "en",
      emailVerifiedAt: camel.emailVerifiedAt || null,
      createdAt: camel.createdAt,
      updatedAt: camel.updatedAt,
    };
  },
};
