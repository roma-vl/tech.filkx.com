import HomePage from "@/pages/home/HomePage.vue";
import CatalogPage from "@/pages/catalog/CatalogPage.vue";
import ProductDetailPage from "@/pages/product/ProductDetailPage.vue";
import ShoppingCart from "@/pages/cart/ShoppingCart.vue";
import UserCabinetPage from "@/pages/account/UserCabinetPage.vue";
import BlogPage from "@/pages/blog/BlogPage.vue";
import BlogPostPage from "@/pages/blog/BlogPostPage.vue";
import StaticPage from "@/pages/static/StaticPage.vue";

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
      {
        path: "blog",
        name: "blog",
        component: BlogPage,
        meta: { title: "Блог — FilkxTech" },
      },
      {
        path: "blog/:slug",
        name: "blog-post",
        component: BlogPostPage,
        meta: { title: "Стаття — FilkxTech" },
      },
      {
        path: "pages/:slug",
        name: "static-page",
        component: StaticPage,
        meta: { title: "Сторінка — FilkxTech" },
      },
      {
        path: "shipping-payment",
        component: StaticPage,
        props: { slug: "shipping-payment" },
        meta: { title: "Оплата та доставка — FilkxTech" },
      },
      {
        path: "warranty-returns",
        component: StaticPage,
        props: { slug: "warranty-returns" },
        meta: { title: "Гарантія та обмін — FilkxTech" },
      },
      {
        path: "service",
        component: StaticPage,
        props: { slug: "service" },
        meta: { title: "Сервіс — FilkxTech" },
      },
      {
        path: "services",
        component: StaticPage,
        props: { slug: "services" },
        meta: { title: "Послуги — FilkxTech" },
      },
      {
        path: "installments",
        component: StaticPage,
        props: { slug: "installments" },
        meta: { title: "Розстрочка — FilkxTech" },
      },
      {
        path: "sota-exchange",
        component: StaticPage,
        props: { slug: "filkx-exchange" },
        meta: { title: "Filkx Обмін — FilkxTech" },
      },
      {
        path: "filkx-exchange",
        component: StaticPage,
        props: { slug: "filkx-exchange" },
        meta: { title: "Filkx Обмін — FilkxTech" },
      },
      {
        path: "contacts",
        component: StaticPage,
        props: { slug: "contacts" },
        meta: { title: "Контакти — FilkxTech" },
      },
      {
        path: "about",
        component: StaticPage,
        props: { slug: "about" },
        meta: { title: "Про нас — FilkxTech" },
      },
      {
        path: "terms",
        component: StaticPage,
        props: { slug: "terms" },
        meta: { title: "Умови використання — FilkxTech" },
      },
      {
        path: "careers",
        component: StaticPage,
        props: { slug: "careers" },
        meta: { title: "Вакансії — FilkxTech" },
      },
      {
        path: "franchising",
        component: StaticPage,
        props: { slug: "franchising" },
        meta: { title: "Франчайзинг — FilkxTech" },
      },
      {
        path: "promo-rules",
        component: StaticPage,
        props: { slug: "promo-rules" },
        meta: { title: "Правила акцій — FilkxTech" },
      },
      {
        path: "privacy",
        component: StaticPage,
        props: { slug: "privacy" },
        meta: { title: "Конфіденційність — FilkxTech" },
      },
      {
        path: "oferta",
        component: StaticPage,
        props: { slug: "oferta" },
        meta: { title: "Публічна оферта — FilkxTech" },
      },
      {
        path: "cookies",
        component: StaticPage,
        props: { slug: "cookies" },
        meta: { title: "Cookies — FilkxTech" },
      },
    ],
  },
];
