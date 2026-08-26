import { defineStore } from "pinia";

const CITY_STORAGE_KEY = "filkx_delivery_city";

export interface DeliveryCity {
  ref: string;
  name: string;
}

interface DeliveryState {
  city: DeliveryCity | null;
}

function loadStoredCity(): DeliveryCity | null {
  if (typeof window === "undefined") {
    return null;
  }

  const raw = localStorage.getItem(CITY_STORAGE_KEY);
  if (!raw) {
    return null;
  }

  try {
    const parsed = JSON.parse(raw);
    if (
      parsed &&
      typeof parsed.ref === "string" &&
      typeof parsed.name === "string"
    ) {
      return { ref: parsed.ref, name: parsed.name };
    }
  } catch {
    // Malformed/foreign value under this key - treat as "no city set".
  }

  return null;
}

// Sitewide "which city am I shopping from" - guest-friendly (localStorage-backed, no
// account/order schema involved) so a delivery-date estimate can be shown without login.
export const useDeliveryStore = defineStore("delivery", {
  state: (): DeliveryState => ({
    city: loadStoredCity(),
  }),

  actions: {
    setCity(city: DeliveryCity) {
      this.city = city;
      if (typeof window !== "undefined") {
        localStorage.setItem(CITY_STORAGE_KEY, JSON.stringify(city));
      }
    },

    clearCity() {
      this.city = null;
      if (typeof window !== "undefined") {
        localStorage.removeItem(CITY_STORAGE_KEY);
      }
    },
  },
});

export default useDeliveryStore;
