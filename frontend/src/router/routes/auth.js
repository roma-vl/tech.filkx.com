import LoginPage from "@/pages/auth/LoginPage.vue";
import RegisterPage from "@/pages/auth/RegisterPage.vue";
import ForgotPasswordPage from "@/pages/auth/ForgotPasswordPage.vue";
import ResetPasswordPage from "@/pages/auth/ResetPasswordPage.vue";
import VerifyEmailPage from "@/pages/auth/VerifyEmailPage.vue";
import VerifyEmailNoticePage from "@/pages/auth/VerifyEmailNoticePage.vue";
import OAuthCallbackPage from "@/pages/auth/OAuthCallbackPage.vue";

export default [
  {
    path: "/login",
    component: LoginPage,
    meta: { guest: true },
  },
  {
    path: "/register",
    component: RegisterPage,
    meta: { guest: true },
  },
  {
    path: "/forgot-password",
    component: ForgotPasswordPage,
    meta: { guest: true },
  },
  {
    path: "/reset-password",
    component: ResetPasswordPage,
    meta: { guest: true },
  },
  {
    path: "/verify-email",
    component: VerifyEmailPage,
    meta: { auth: false },
  },
  {
    path: "/verify-email-notice",
    component: VerifyEmailNoticePage,
    meta: { auth: true },
  },
  {
    path: "/auth/callback/:provider",
    component: OAuthCallbackPage,
    meta: { auth: false }, // Can be guest or auth
  },
];
