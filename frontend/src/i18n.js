import {createI18n} from "vue-i18n";
import messages from "@/lang";

const DEFAULT_LOCALE = "en";
const savedLocale =
  (typeof localStorage !== "undefined"
    ? localStorage.getItem("locale")
    : null) || DEFAULT_LOCALE;

export const i18n = createI18n({
  legacy: false,
  locale: savedLocale,
  fallbackLocale: "en",
  messages,
});
