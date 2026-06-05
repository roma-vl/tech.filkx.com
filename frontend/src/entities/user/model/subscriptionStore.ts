import { defineStore } from "pinia";

export interface SubscriptionState {
  subscription: any | null;
  isActive: boolean;
  isTrial: boolean;
  isTrialExpired: boolean;
  subscriptionStatus: string;
}

export const useSubscriptionStore = defineStore("subscription", {
  state: (): SubscriptionState => ({
    subscription: null,
    isActive: false,
    isTrial: false,
    isTrialExpired: false,
    subscriptionStatus: "",
  }),

  actions: {
    async fetchSubscription() {
      // Stub for future subscription loading implementation
      return null;
    },
  },
});
export default useSubscriptionStore;
