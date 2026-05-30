import StreamingIcon from "@/components/Icon/StreamingIcon.vue";
import ScreenPlayIcon from "@/components/Icon/ScreenPlayIcon.vue";
import DashboardIcon from "@/components/Icon/DashboardIcon.vue";
import CalendarIcon from "@/components/Icon/CalendarIcon.vue";
import SettingsIcon from "@/components/Icon/SettingsIcon.vue";
import HelperCenterIcon from "@/components/Icon/HelperCenterIcon.vue";
import SubscriptionIcon from "@/components/Icon/SubscriptionIcon.vue";
import AdminIcon from "@/components/Icon/AdminIcon.vue";

export const navigationGroups = [
  {
    id: "main",
    items: [
      {
        id: "dashboard",
        labelKey: "menu.dashboard",
        icon: DashboardIcon,
        to: "/dashboard",
      },
      {
        id: "videos",
        labelKey: "menu.storage",
        icon: ScreenPlayIcon,
        to: "/media/videos",
        activeMatch: "/media",
      },
      {
        id: "streams",
        labelKey: "menu.streams",
        icon: StreamingIcon,
        to: "/streams",
      },
      {
        id: "scheduler",
        labelKey: "menu.scheduler",
        icon: CalendarIcon,
        to: "/scheduler",
        featureKey: "hasScheduler",
      },
    ],
  },
  {
    id: "account",
    items: [
      {
        id: "subscription",
        labelKey: "navigation.subscription",
        icon: SubscriptionIcon,
        to: "/account/subscription",
      },
      {
        id: "faq",
        labelKey: "navigation.helperCenter",
        icon: HelperCenterIcon,
        to: "/faq",
      },
      {
        id: "settings",
        labelKey: "menu.settings",
        icon: SettingsIcon,
        to: "/settings",
      },
    ],
  },
  {
    id: "admin",
    requiredRoles: ["admin", "administrator", "moderator", "support"],
    items: [
      {
        id: "admin",
        labelKey: "navigation.adminPanel",
        icon: AdminIcon,
        to: "/admin",
      },
    ],
  },
];
