import { ref, computed, onMounted, onUnmounted } from "vue";

export function useLayout() {
  const sidebarCollapsed = ref(false);
  const windowWidth = ref(
    typeof window !== "undefined" ? window.innerWidth : 1200,
  );

  const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
  };

  const handleResize = () => {
    windowWidth.value = window.innerWidth;
  };

  onMounted(() => {
    window.addEventListener("resize", handleResize);
  });

  onUnmounted(() => {
    window.removeEventListener("resize", handleResize);
  });

  const isMobile = computed(() => windowWidth.value < 768);

  return {
    sidebarCollapsed,
    toggleSidebar,
    isMobile,
    windowWidth,
  };
}
