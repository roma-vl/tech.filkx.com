import { defineStore } from "pinia";

export const useUiStore = defineStore("ui", {
  state: () => ({
    darkMode: false,
    initialized: false,
  }),

  actions: {
    initTheme() {
      if (this.initialized) return;
      if (typeof window === "undefined") return;

      // Theme is already applied by inline script in index.html
      // Just sync the store state with what's in the DOM
      this.darkMode = document.documentElement.classList.contains("dark");
      this.initialized = true;
    },

    toggleDark() {
      this.setDark(!this.darkMode);
    },

    setDark(v) {
      this.darkMode = v;
      if (typeof document !== "undefined") {
        document.documentElement.classList.toggle("dark", v);
      }
      if (typeof localStorage !== "undefined") {
        localStorage.theme = v ? "dark" : "light";
      }
    },
  },
});
