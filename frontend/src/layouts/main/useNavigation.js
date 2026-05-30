import { computed } from "vue";
import { useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";
// import { useSubscriptionStore } from "@/stores/subscription";
import { navigationGroups } from "@/layouts/appllication/navigation.js";

export function useNavigation() {
  const route = useRoute();
  const auth = useAuthStore();

  const isVisible = (itemOrGroup) => {
    // Hidden features (legacy logic)
    if (itemOrGroup.featureGate && !itemOrGroup.featureGate(auth.user)) {
      return false;
    }

    // Role-based access
    if (itemOrGroup.requiredRoles) {
      if (!auth.user?.roles) return false;
      return itemOrGroup.requiredRoles.some((role) =>
        auth.user.roles.includes(role),
      );
    }

    return true;
  };

  const getIsLocked = (item) => {
    if (!item.featureKey) return false;
    // Check if subscription exists and feature is explicitly false
  };

  const processedGroups = computed(() =>
    navigationGroups
      .filter(isVisible)
      .map((group) => ({
        ...group,
        items: group.items.filter(isVisible).map((item) => ({
          ...item,
          isActive: item.activeMatch
            ? route.path.startsWith(item.activeMatch)
            : route.path === item.to,
          isLocked: getIsLocked(item),
        })),
      }))
      .filter((group) => group.items.length > 0),
  );

  const flattenedItems = computed(() =>
    processedGroups.value.flatMap((group) => group.items),
  );

  const activeItemId = computed(() => {
    const matched = flattenedItems.value.find((item) => route.path === item.to);
    return matched?.id;
  });

  return {
    navigationGroups: processedGroups,
    activeItemId,
    flattenedItems,
  };
}
