import MainPage from "@/pages/application/MainPage.vue";
// import VideosPage from "@/pages/application/VideosPage.vue";
// import StreamsPage from "@/pages/application/StreamsPage.vue";
// import SettingsPage from "@/pages/application/SettingsPage.vue";
// import StreamingCalendarPage from "@/pages/application/StreamingCalendarPage.vue";
// import PlaylistsPage from "@/pages/application/PlaylistsPage.vue";
// import VideosPlaylistsPage from "@/pages/application/VideosPlaylistsPage.vue";
// import ActivitiesPage from "@/pages/application/ActivitiesPage.vue";
// import AffiliateDashboard from "@/pages/application/AffiliateDashboard.vue";
// import AffiliateDocs from "@/pages/application/AffiliateDocs.vue";
// import FaqPage from "@/pages/application/FaqPage.vue";

import ApplicationLayoutWrapper from "@/pages/application/ApplicationLayoutWrapper.vue";

export default [
  {
    path: "/dashboard",
    children: [
      {
        path: "",
        component: MainPage,
        meta: {
          title: "Dashboard — Stream Studio",
          auth: true,
          verified: true,
          requiresSubscription: true,
        },
      },
    ],
  },
  // {
  //   path: "/settings",
  //   children: [
  //     {
  //       path: "",
  //       component: SettingsPage,
  //       meta: { title: "Settings — Stream Studio", auth: true, verified: true },
  //     },
  //   ],
  // },
];
