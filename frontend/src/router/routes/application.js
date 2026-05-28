import HomePage from "@/pages/home/HomePage.vue";
import CatalogPage from "@/pages/catalog/CatalogPage.vue";
import ProductDetailPage from "@/pages/product/ProductDetailPage.vue";
import ShoppingCart from "@/pages/cart/ShoppingCart.vue";
import UserCabinetPage from "@/pages/account/UserCabinetPage.vue";
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

import MainLayout from "@/layouts/main/MainLayout.vue";

export default [
  {
    path: "/",
    component: MainLayout,
    children: [
      {
        path: "",
        name: "home",
        component: HomePage,
        meta: {
          title: "Home — Stream Studio",
          auth: false,
        },
      },
      {
        path: "dashboard",
        name: "dashboard",
        component: HomePage,
        meta: {
          title: "Dashboard — Stream Studio",
          auth: true,
          verified: true,
          requiresSubscription: true,
        },
      },
      {
        path: "catalog",
        name: "catalog",
        component: CatalogPage,
        meta: { title: "Catalog" },
      },
      {
        path: "product/:id",
        name: "product-detail",
        component: ProductDetailPage,
        meta: { title: "Product Detail" },
      },
      {
        path: "cart",
        name: "cart",
        component: ShoppingCart,
        meta: { title: "Shopping Cart" },
      },
      {
        path: "account",
        name: "account",
        component: UserCabinetPage,
        meta: { title: "Account — FilkxTech" },
      },
    ],
  },
];
