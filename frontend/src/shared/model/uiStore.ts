import { defineStore } from "pinia";

export interface UiState {
  darkMode: boolean;
  initialized: boolean;
}

export const useUiStore = defineStore("ui", {
  state: (): UiState => ({
    darkMode: false,
    initialized: false,
  }),

  actions: {
    initTheme() {
      if (this.initialized) return;
      if (typeof window === "undefined") return;

      this.darkMode = document.documentElement.classList.contains("dark");
      this.initialized = true;
    },

    toggleDark() {
      this.setDark(!this.darkMode);
    },

    setDark(v: boolean) {
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
export default useUiStore;
