<script setup>
import { ref } from "vue";
import { store } from "@/store.js";

const activeFaqIndex = ref(null);
const faqs = [
  {
    q: "Скільки часу займає доставка товарів?",
    a: "Стандартна доставка триває 1-3 робочих дні. Експрес-доставка кур'єром по місту — 1 день. Для наших постійних покупців та преміум-клієнтів діє безкоштовна доставка.",
  },
  {
    q: "Які умови повернення товару?",
    a: "Повернення товару можливе протягом 14 днів відповідно до законодавства України. Товар має бути в оригінальній упаковці, без слідів використання, з усіма заводськими плівками, аксесуарами та чеком.",
  },
  {
    q: "Як отримати гарантійне обслуговування?",
    a: "Вся техніка FilkxTech постачається з офіційною гарантією терміном від 12 до 24 місяців. Ви можете подати заявку на ремонт, створивши звернення тут, або звернутися до сервісного центру.",
  },
  {
    q: "Чи можна змінити адресу після оформлення замовлення?",
    a: 'Адресу доставки можна змінити протягом 1 години після оформлення замовлення. Коли замовлення переходить у статус "В дорозі", змінити адресу вже неможливо.',
  },
];
const toggleFaq = (i) => {
  activeFaqIndex.value = activeFaqIndex.value === i ? null : i;
};

const ticketForm = ref({
  subject: "",
  category: "Проблеми із замовленням",
  message: "",
});
const ticketsList = ref([
  {
    id: "TK-8492",
    subject: "Затримка доставки замовлення",
    category: "Проблеми із замовленням",
    date: "24 Тра, 2025",
    status: "В обробці",
    statusClass: "text-amber-500 bg-amber-500/10 border border-amber-500/20",
  },
  {
    id: "TK-3921",
    subject: "Пошкоджений кабель навушників",
    category: "Гарантія та повернення",
    date: "12 Січ, 2025",
    status: "Закрито",
    statusClass:
      "text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700",
  },
]);

const submitTicket = () => {
  if (!ticketForm.value.subject || !ticketForm.value.message) {
    store.addToast(
      "Будь ласка, вкажіть тему та детальний опис проблеми.",
      "warning",
    );
    return;
  }
  const newId = `TK-${Math.floor(1000 + Math.random() * 9000)}`;
  ticketsList.value.unshift({
    id: newId,
    subject: ticketForm.value.subject,
    category: ticketForm.value.category,
    date: new Date().toLocaleDateString("uk-UA", {
      month: "short",
      day: "2-digit",
      year: "numeric",
    }),
    status: "Відкрито",
    statusClass:
      "text-[#00a046] bg-emerald-500/10 border border-emerald-500/20",
  });
  store.addToast(
    `Звернення ${newId} створено! Наша служба підтримки відповість найближчим часом.`,
    "success",
  );
  ticketForm.value.subject = "";
  ticketForm.value.message = "";
};
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left: FAQ + History -->
      <div class="lg:col-span-2 space-y-6">
        <section
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4"
        >
          <h3
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
          >
            Часті запитання (FAQ)
          </h3>
          <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
            <div
              v-for="(faq, idx) in faqs"
              :key="idx"
              class="py-4.5 first:pt-0 last:pb-0"
            >
              <button
                class="w-full flex items-center justify-between text-left font-extrabold text-xs md:text-sm text-zinc-800 dark:text-zinc-200 hover:text-[#00a046] transition-colors py-1"
                @click="toggleFaq(idx)"
              >
                <span>{{ faq.q }}</span>
                <span
                  class="material-symbols-outlined text-[20px] transition-transform duration-300"
                  :class="
                    activeFaqIndex === idx
                      ? 'rotate-180 text-[#00a046]'
                      : 'text-zinc-400'
                  "
                >expand_more</span>
              </button>
              <div
                v-show="activeFaqIndex === idx"
                class="mt-2.5 text-xs md:text-sm text-zinc-500 dark:text-zinc-400 leading-relaxed"
              >
                {{ faq.a }}
              </div>
            </div>
          </div>
        </section>

        <section
          class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4"
        >
          <h3
            class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
          >
            Історія звернень
          </h3>
          <div
            v-if="ticketsList.length > 0"
            class="divide-y divide-zinc-100 dark:divide-zinc-800"
          >
            <div
              v-for="ticket in ticketsList"
              :key="ticket.id"
              class="py-4.5 first:pt-0 last:pb-0 flex flex-wrap justify-between items-center gap-4 text-xs md:text-sm"
            >
              <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                  <span
                    class="font-extrabold text-zinc-800 dark:text-zinc-200 text-sm md:text-base"
                  >{{ ticket.subject }}</span>
                  <span
                    class="text-[9px] font-black uppercase tracking-wider text-zinc-500 dark:text-zinc-400 bg-zinc-50 dark:bg-zinc-800 px-2.5 py-1 rounded-lg border border-zinc-100 dark:border-zinc-700"
                  >{{ ticket.category }}</span>
                </div>
                <p class="text-zinc-400 dark:text-zinc-500 text-xs mt-1.5">
                  ID звернення: {{ ticket.id }} • Створено: {{ ticket.date }}
                </p>
              </div>
              <span
                class="inline-block px-3.5 py-1.5 rounded-lg font-extrabold uppercase text-[10px] tracking-wider"
                :class="ticket.statusClass"
              >{{ ticket.status }}</span>
            </div>
          </div>
          <p
            v-else
            class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 italic text-center py-4"
          >
            У вас немає відкритих звернень.
          </p>
        </section>
      </div>

      <!-- Right: Submit Ticket -->
      <section
        class="bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl p-6 shadow-sm space-y-4 h-fit"
      >
        <h3
          class="font-extrabold text-base md:text-lg text-zinc-900 dark:text-white border-b border-zinc-100 dark:border-zinc-800 pb-3"
        >
          Створити звернення
        </h3>
        <form
          class="space-y-4"
          @submit.prevent="submitTicket"
        >
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-455 dark:text-zinc-500 uppercase tracking-wider"
            >Категорія</label>
            <select
              v-model="ticketForm.category"
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none"
            >
              <option>Проблеми із замовленням</option>
              <option>Гарантія та повернення</option>
              <option>Технічна підтримка</option>
              <option>Оплата та рахунки</option>
            </select>
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Тема звернення</label>
            <input
              v-model="ticketForm.subject"
              type="text"
              placeholder="Коротко опишіть тему..."
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none"
            >
          </div>
          <div class="space-y-1.5">
            <label
              class="text-[10px] font-extrabold text-zinc-450 dark:text-zinc-500 uppercase tracking-wider"
            >Опис проблеми</label>
            <textarea
              v-model="ticketForm.message"
              rows="4"
              placeholder="Детально опишіть вашу проблему..."
              required
              class="w-full bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-2.5 text-xs md:text-sm text-zinc-850 dark:text-zinc-150 focus:ring-1 focus:ring-[#00a046] focus:border-[#00a046] outline-none resize-none"
            />
          </div>
          <button
            type="submit"
            class="w-full bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 rounded-lg transition-all uppercase tracking-wider flex items-center justify-center gap-2 shadow-sm"
          >
            <span class="material-symbols-outlined text-[16px] md:text-[18px]">send</span>
            Надіслати
          </button>
        </form>
      </section>
    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.25s ease-out forwards;
}
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(4px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
