import { defineStore } from "pinia";

export interface UiState {
  darkMode: boolean;
  initialized: boolean;
  // Height (px) of whatever page-specific bar is currently pinned to the
  // bottom of the viewport (e.g. the product page's sticky buy bar) - lets
  // globally-mounted fixed elements (the support widget bubble) move out of
  // its way instead of sitting on top of it. 0 when nothing is pinned.
  stickyBottomBarHeight: number;
}

export const useUiStore = defineStore("ui", {
  state: (): UiState => ({
    darkMode: false,
    initialized: false,
    stickyBottomBarHeight: 0,
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

    setStickyBottomBarHeight(px: number) {
      this.stickyBottomBarHeight = px;
    },
  },
});
export default useUiStore;
