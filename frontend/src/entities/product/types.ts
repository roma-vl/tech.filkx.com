export interface Category {
  id: number;
  name: string;
  slug: string;
  parentId?: number | null;
  description?: string;
  image?: string;
  productsCount?: number;
  createdAt?: string;
  updatedAt?: string;
}

export interface Brand {
  id: number;
  name: string;
  slug: string;
  description?: string;
  logo?: string;
  website?: string;
  createdAt?: string;
  updatedAt?: string;
}

export interface AttributeValue {
  id: number;
  attributeId: number;
  value: string;
  label?: string;
}

export interface Attribute {
  id: number;
  name: string;
  code: string;
  type: string; // select, text, number, etc.
  values?: AttributeValue[];
  isRequired?: boolean;
}

export interface ProductVariant {
  id: number;
  productId: number;
  sku: string;
  price: number;
  stock: number;
  attributes?: Record<string, string>;
}

export interface Product {
  id: number;
  name: string;
  slug: string;
  sku: string;
  price: number;
  salePrice?: number | null;
  stock: number;
  image: string;
  gallery?: string[];
  description?: string;
  specs?: Record<string, string> | any[];
  rating?: number;
  reviews?: number;
  isFeatured?: boolean;
  isActive?: boolean;
  categoryId?: number;
  category?: Category | string;
  brandId?: number;
  brand?: Brand;
  variants?: ProductVariant[];
  createdAt?: string;
  updatedAt?: string;
}
