<script setup lang="ts">
import { ref } from "vue";
import { RouterLink } from "vue-router";
import { useCartStore } from "@/entities/order/model/cartStore";

const cartStore = useCartStore();
const email = ref("");

const handleSubscribe = () => {
  if (email.value.trim()) {
    cartStore.addToast(
      `Дякуємо за підписку! На вказану адресу ${email.value} надіслано лист.`,
      "success",
    );
    email.value = "";
  }
};
</script>

<template>
  <!-- Main Footer -->
  <footer
    class="bg-[#19191b] text-[#c4c4c7] pt-16 pb-8 border-t border-zinc-800 font-sans"
  >
    <div class="max-w-container-max mx-auto px-4 md:px-8">
      <!-- Top Section: Sitemap & Form Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-12">
        <!-- Column 1: Brand Info & Support -->
        <div class="space-y-4">
          <a
            class="flex items-center gap-2 hover:opacity-90 transition-opacity"
            href="/"
          >
            <div
              class="w-7 h-7 rounded-full bg-[#00a046] flex items-center justify-center"
            >
              <span
                class="material-symbols-outlined text-white text-[18px] font-bold"
              >sentiment_very_satisfied</span>
            </div>
            <span class="font-extrabold text-lg tracking-tight text-white">
              FilkxTech
            </span>
          </a>
          <p class="text-xs leading-relaxed text-zinc-400">
            Найбільший інтернет-магазин преміальної техніки та електроніки в
            Україні. Надійна якість, офіційна гарантія та швидка доставка з 2010
            року.
          </p>
          <div class="space-y-2 pt-2">
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-zinc-500 text-lg">call</span>
              <a
                href="tel:0800300100"
                class="text-sm font-black text-white hover:text-[#00a046] transition-colors"
              >0 800 300 100</a>
            </div>
            <p class="text-[10px] text-zinc-500 leading-none pl-7">
              (Безкоштовно по всій Україні)
            </p>
            <div class="flex items-center gap-2">
              <span class="material-symbols-outlined text-zinc-500 text-lg">schedule</span>
              <span class="text-xs text-zinc-400">Пн-Нд: 08:00 - 22:00</span>
            </div>
          </div>
        </div>

        <!-- Column 2: Company -->
        <div>
          <h4
            class="text-xs font-black uppercase text-white tracking-widest mb-4"
          >
            Компанія
          </h4>
          <ul class="space-y-2.5 text-xs">
            <li>
              <router-link
                to="/about"
                class="hover:text-white transition-colors"
              >
                Про нас
              </router-link>
            </li>
            <li>
              <router-link
                to="/terms"
                class="hover:text-white transition-colors"
              >
                Умови використання сайту
              </router-link>
            </li>
            <li>
              <router-link
                to="/careers"
                class="hover:text-white transition-colors"
              >
                Вакансії
              </router-link>
            </li>
            <li>
              <router-link
                to="/contacts"
                class="hover:text-white transition-colors"
              >
                Контакти
              </router-link>
            </li>
            <li>
              <router-link
                to="/franchising"
                class="hover:text-white transition-colors"
              >
                Франчайзинг
              </router-link>
            </li>
            <li>
              <router-link
                to="/promo-rules"
                class="hover:text-white transition-colors"
              >
                Офіційні правила акцій
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Column 3: Help & Services -->
        <div>
          <h4
            class="text-xs font-black uppercase text-white tracking-widest mb-4"
          >
            Допомога
          </h4>
          <ul class="space-y-2.5 text-xs">
            <li>
              <router-link
                to="/shipping-payment"
                class="hover:text-white transition-colors"
              >
                Доставка та оплата
              </router-link>
            </li>
            <li>
              <router-link
                to="/installments"
                class="hover:text-white transition-colors"
              >
                Кредит та оплата частинами
              </router-link>
            </li>
            <li>
              <router-link
                to="/warranty-returns"
                class="hover:text-white transition-colors"
              >
                Гарантійні умови
              </router-link>
            </li>
            <li>
              <router-link
                to="/warranty-returns"
                class="hover:text-white transition-colors"
              >
                Повернення та обмін товару
              </router-link>
            </li>
            <li>
              <router-link
                to="/service"
                class="hover:text-white transition-colors"
              >
                Сервісні центри партнери
              </router-link>
            </li>
            <li>
              <router-link
                to="/account"
                class="hover:text-white transition-colors"
              >
                Відстежити замовлення
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Column 4: Categories -->
        <div>
          <h4
            class="text-xs font-black uppercase text-white tracking-widest mb-4"
          >
            Популярні товари
          </h4>
          <ul class="space-y-2.5 text-xs">
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Смартфони та аксесуари</a>
            </li>
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Ноутбуки та комп'ютери</a>
            </li>
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Геймінг та консолі</a>
            </li>
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Аудіо системи та навушники</a>
            </li>
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Wearable смарт-годинники</a>
            </li>
            <li>
              <a
                href="#"
                class="hover:text-white transition-colors"
              >Розумний будинок</a>
            </li>
          </ul>
        </div>

        <!-- Column 5: Subscription Form -->
        <div class="space-y-4">
          <h4
            class="text-xs font-black uppercase text-white tracking-widest mb-2"
          >
            Спецпропозиції та новини
          </h4>
          <p class="text-xs text-zinc-400 leading-relaxed">
            Підпишіться, щоб першими дізнаватися про знижки, розіграші та
            надходження нових преміум товарів.
          </p>
          <form
            class="flex gap-1.5"
            @submit.prevent="handleSubscribe"
          >
            <input
              v-model="email"
              class="bg-zinc-900 border border-zinc-800 px-3 py-2 rounded-lg text-xs flex-grow focus:outline-none focus:ring-1 focus:ring-zinc-600 focus:bg-zinc-850 text-white transition-all"
              placeholder="Введіть ваш email"
              type="email"
              required
            >
            <button
              class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2 rounded-lg text-xs font-bold transition-colors shrink-0"
            >
              OK
            </button>
          </form>

          <!-- Social Icons -->
          <div class="flex items-center gap-3 pt-2">
            <a
              href="#"
              class="w-8 h-8 rounded-lg bg-zinc-900 hover:bg-[#00a046] hover:text-white border border-zinc-800 flex items-center justify-center transition-all"
              title="Facebook"
            >
              <svg
                class="w-4 h-4 fill-current"
                viewBox="0 0 24 24"
              >
                <path
                  d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"
                />
              </svg>
            </a>
            <a
              href="#"
              class="w-8 h-8 rounded-lg bg-zinc-900 hover:bg-[#00a046] hover:text-white border border-zinc-800 flex items-center justify-center transition-all"
              title="Instagram"
            >
              <svg
                class="w-4 h-4 fill-current"
                viewBox="0 0 24 24"
              >
                <path
                  d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"
                />
              </svg>
            </a>
            <a
              href="#"
              class="w-8 h-8 rounded-lg bg-zinc-900 hover:bg-[#00a046] hover:text-white border border-zinc-800 flex items-center justify-center transition-all"
              title="YouTube"
            >
              <svg
                class="w-4 h-4 fill-current"
                viewBox="0 0 24 24"
              >
                <path
                  d="M23.498 6.163c-.272-1.025-1.077-1.83-2.101-2.102C19.531 3.5 12 3.5 12 3.5s-7.53 0-9.397.561c-1.024.272-1.83 1.077-2.102 2.102C0 8.03 0 12 0 12s0 3.97.501 5.837c.272 1.025 1.078 1.83 2.102 2.102C4.47 20.5 12 20.5 12 20.5s7.53 0 9.397-.561c1.024-.272 1.83-1.077 2.102-2.102C24 15.97 24 12 24 12s0-3.97-.502-5.837zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"
                />
              </svg>
            </a>
            <a
              href="#"
              class="w-8 h-8 rounded-lg bg-zinc-900 hover:bg-[#00a046] hover:text-white border border-zinc-800 flex items-center justify-center transition-all"
              title="Telegram"
            >
              <svg
                class="w-4 h-4 fill-current"
                viewBox="0 0 24 24"
              >
                <path
                  d="M22.05 1.577c-.39-.187-.85-.138-1.2.13L1.08 13.916c-.46.33-.61.94-.36 1.45.25.5.76.81 1.32.78l4.43-.24 3.73 4.88c.31.41.81.65 1.34.65.25 0 .5-.05.74-.16.59-.28.93-.83 1.05-1.46l2.12-11.45 6.07 4.77c.36.28.84.34 1.25.16.4-.17.67-.54.71-.97L24 2.87c.03-.49-.24-.95-.69-1.15l-1.26-.143zM9.54 14.86l.22-3.15 6.43-5.8c.2-.18.23-.48.06-.7-.17-.22-.48-.25-.7-.08l-8.08 5.6-3.66.2 14.07-9.76L9.54 14.86z"
                />
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Middle Section: Download App Badges -->
      <div
        class="flex flex-col sm:flex-row items-center justify-between border-t border-b border-zinc-800 py-6 my-8 gap-4"
      >
        <div>
          <h5
            class="text-xs font-bold text-white uppercase tracking-wider mb-1"
          >
            Мобільні додатки FilkxTech
          </h5>
          <p class="text-[11px] text-zinc-550">
            Завантажуйте наш безкоштовний додаток для швидких та зручних покупок
            на ходу.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <!-- App Store Badge -->
          <a
            href="#"
            class="flex items-center gap-2 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-850 border border-zinc-800 rounded-lg transition-colors text-white"
          >
            <svg
              class="w-5 h-5 text-white fill-current"
              viewBox="0 0 24 24"
            >
              <path
                d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 4.17c.66-.81 1.11-1.93.99-3.06-1 .04-2.22.67-2.94 1.51-.64.74-1.2 1.88-1.05 3 .96.08 2.11-.53 2.76-1.32z"
              />
            </svg>
            <div class="text-left leading-none">
              <span
                class="text-[8px] block text-zinc-500 uppercase tracking-wide"
              >Download on the</span>
              <span class="text-[11px] font-black font-sans tracking-tight">App Store</span>
            </div>
          </a>

          <!-- Google Play Badge -->
          <a
            href="#"
            class="flex items-center gap-2 px-3 py-1.5 bg-zinc-900 hover:bg-zinc-850 border border-zinc-800 rounded-lg transition-colors text-white"
          >
            <svg
              class="w-5 h-5 text-white fill-current"
              viewBox="0 0 24 24"
            >
              <path
                d="M3 5.27v13.46c0 .82.68 1.42 1.47 1.26l11.14-6.43L4.47 4.01C3.68 3.85 3 4.45 3 5.27zm13.14 6.73L5.68 6.09l9.31 5.37c.36.21.36.73 0 .94l-9.31 5.37 10.46-5.77z"
              />
            </svg>
            <div class="text-left leading-none">
              <span
                class="text-[8px] block text-zinc-500 uppercase tracking-wide"
              >Get it on</span>
              <span class="text-[11px] font-black font-sans tracking-tight">Google Play</span>
            </div>
          </a>
        </div>
      </div>

      <!-- Bottom Section: Legal & Payments -->
      <div
        class="flex flex-col md:flex-row justify-between items-center gap-6 text-[11px] text-zinc-500"
      >
        <!-- Trademark / Copyright -->
        <div class="space-y-1 text-center md:text-left">
          <p>© 2026 ТОВ «ФілксТех Преміум». Всі права захищені.</p>
          <p class="text-[10px] text-zinc-600">
            Інформація про ціни, характеристики та наявність товарів може бути
            змінена без попередження.
          </p>
        </div>

        <!-- Sitemap Links -->
        <div
          class="flex flex-wrap justify-center gap-6 font-semibold uppercase tracking-wider text-[9px]"
        >
          <router-link
            to="/privacy"
            class="hover:text-white transition-colors"
          >
            Конфіденційність
          </router-link>
          <router-link
            to="/oferta"
            class="hover:text-white transition-colors"
          >
            Оферта
          </router-link>
          <a
            href="#"
            class="hover:text-white transition-colors"
          >Мапа сайту</a>
          <router-link
            to="/cookies"
            class="hover:text-white transition-colors"
          >
            Cookies
          </router-link>
        </div>

        <!-- Payment Brands -->
        <div
          class="flex items-center gap-4 opacity-50 hover:opacity-75 transition-opacity"
        >
          <!-- Visa -->
          <svg
            class="h-5 w-auto"
            viewBox="0 0 36 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M12.986 0.165l-2.428 11.235h-2.316l2.428-11.235h2.316zm12.388 4.256c0.007-0.089 0.012-0.18 0.012-0.274 0-1.849-2.545-1.951-2.569-2.775 0-0.25 0.228-0.518 0.723-0.584 0.245-0.033 0.923-0.06 1.696 0.297l0.301-1.398c-0.412-0.15-0.947-0.307-1.616-0.307-1.693 0-2.887 0.901-2.898 2.193-0.013 1.624 2.247 1.714 2.235 2.502-0.003 0.24-0.288 0.493-0.852 0.558-0.426 0.049-1.442 0.026-1.996-0.297l-0.316 1.46c0.536 0.245 1.23 0.384 1.9 0.385 1.795 0 2.969-0.89 2.98-2.295zm9.58-4.256l-1.802 8.497c-0.12 0.559-0.51 1.055-1.042 1.341l-2.527 1.397 0.065-0.306 4.316-10.929h2.99zm-26.685 0l-3.327 7.784-0.36-1.844c-0.505-1.722-2.072-3.585-3.837-4.512l3.415 9.807 2.378-0.004 3.541-11.231h-1.81z"
              fill="currentColor"
            />
          </svg>
          <!-- Mastercard -->
          <svg
            class="h-5 w-auto"
            viewBox="0 0 20 12"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <circle
              cx="6"
              cy="6"
              r="6"
              fill="#EB001B"
            />
            <circle
              cx="14"
              cy="6"
              r="6"
              fill="#F79E1B"
            />
            <path
              d="M10 1.643a5.986 5.986 0 0 0 2.146 4.357A5.986 5.986 0 0 0 10 10.357a5.986 5.986 0 0 0-2.146-4.357A5.986 5.986 0 0 0 10 1.643z"
              fill="#FF5F00"
            />
          </svg>
          <!-- Apple Pay -->
          <span
            class="text-[10px] font-black tracking-tighter text-white font-sans uppercase"
          > Pay</span>
          <!-- Google Pay -->
          <span
            class="text-[10px] font-black tracking-tighter text-white font-sans uppercase"
          >Google Pay</span>
        </div>
      </div>
    </div>
  </footer>
</template>
