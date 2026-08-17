import { describe, it, expect } from "vitest";
import {
  resolveProductImage,
  resolveGalleryImages,
} from "./resolveProductImage";
import productPlaceholder from "@/assets/images/product-placeholder.svg";

describe("resolveProductImage", () => {
  it("returns the primary image from the variant's own photos", () => {
    const variant = {
      dimensions: {
        images: [
          { url: "https://cdn.example/side.webp", isPrimary: false },
          { url: "https://cdn.example/front.webp", isPrimary: true },
        ],
      },
    };

    expect(resolveProductImage(variant)).toBe("https://cdn.example/front.webp");
  });

  it("falls back to the first image when none is marked primary", () => {
    const variant = {
      dimensions: {
        images: [
          { url: "https://cdn.example/a.webp" },
          { url: "https://cdn.example/b.webp" },
        ],
      },
    };

    expect(resolveProductImage(variant)).toBe("https://cdn.example/a.webp");
  });

  it("recognizes the snake_case is_primary flag from the API", () => {
    const variant = {
      dimensions: {
        images: [
          { url: "https://cdn.example/a.webp", is_primary: false },
          { url: "https://cdn.example/b.webp", is_primary: true },
        ],
      },
    };

    expect(resolveProductImage(variant)).toBe("https://cdn.example/b.webp");
  });

  it("falls back to a sibling variant's photos when the variant has none of its own", () => {
    const variant = { dimensions: { images: [] } };
    const siblings = [
      { dimensions: {} },
      {
        dimensions: {
          images: [
            { url: "https://cdn.example/sibling.webp", isPrimary: true },
          ],
        },
      },
    ];

    expect(resolveProductImage(variant, siblings)).toBe(
      "https://cdn.example/sibling.webp",
    );
  });

  it("returns the placeholder when neither the variant nor any sibling has photos", () => {
    expect(resolveProductImage(null, [{ dimensions: {} }])).toBe(
      productPlaceholder,
    );
  });

  it("returns the placeholder for a variant with no dimensions at all", () => {
    expect(resolveProductImage(undefined)).toBe(productPlaceholder);
  });
});

describe("resolveGalleryImages", () => {
  it("maps every one of the variant's own images, flagging the primary one", () => {
    const variant = {
      dimensions: {
        images: [
          { url: "https://cdn.example/a.webp", isPrimary: false },
          { url: "https://cdn.example/b.webp", isPrimary: true },
        ],
      },
    };

    expect(resolveGalleryImages(variant)).toEqual([
      { url: "https://cdn.example/a.webp", isPrimary: false },
      { url: "https://cdn.example/b.webp", isPrimary: true },
    ]);
  });

  it("drops entries without a url", () => {
    const variant = {
      dimensions: {
        images: [{ url: "" }, { url: "https://cdn.example/b.webp" }],
      },
    };

    expect(resolveGalleryImages(variant)).toEqual([
      { url: "https://cdn.example/b.webp", isPrimary: false },
    ]);
  });

  it("falls back to a sibling variant's gallery when the variant has no photos", () => {
    const variant = { dimensions: { images: [] } };
    const siblings = [
      {
        dimensions: {
          images: [
            { url: "https://cdn.example/sibling.webp", isPrimary: true },
          ],
        },
      },
    ];

    expect(resolveGalleryImages(variant, siblings)).toEqual([
      { url: "https://cdn.example/sibling.webp", isPrimary: true },
    ]);
  });

  it("always returns at least the placeholder when nothing has photos", () => {
    expect(resolveGalleryImages(null)).toEqual([
      { url: productPlaceholder, isPrimary: true },
    ]);
  });
});
