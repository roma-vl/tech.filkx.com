<script setup>
const items = [
  {
    icon: "local_shipping",
    title: "Безкоштовна доставка",
    description: "Для всіх замовлень від 2000 ₴ по всій Україні.",
    accent: "text-blue-500",
    bg: "bg-blue-500/10 dark:bg-blue-500/5",
  },
  {
    icon: "verified",
    title: "Офіційна гарантія",
    description: "100% оригінальна продукція з гарантійним обслуговуванням.",
    accent: "text-[#00a046]",
    bg: "bg-emerald-500/10 dark:bg-emerald-500/5",
  },
  {
    icon: "assignment_return",
    title: "Повернення протягом 14 днів",
    description: "Простий обмін або повернення коштів без зайвих питань.",
    accent: "text-amber-500",
    bg: "bg-amber-500/10 dark:bg-amber-500/5",
  },
  {
    icon: "support_agent",
    title: "Цілодобова підтримка 24/7",
    description: "Наші експерти завжди на зв'язку, щоб допомогти з вибором.",
    accent: "text-purple-500",
    bg: "bg-purple-500/10 dark:bg-purple-500/5",
  },
];
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-6 font-sans">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div
        v-for="item in items"
        :key="item.title"
        class="group flex items-start gap-4 p-5 bg-white dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-xl hover:border-zinc-200 dark:hover:border-zinc-700 hover:shadow-md transition-all duration-300"
      >
        <div
          class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-transform duration-300 group-hover:scale-110"
          :class="item.bg"
        >
          <span
            class="material-symbols-outlined text-[22px]"
            :class="item.accent"
          >
            {{ item.icon }}
          </span>
        </div>
        <div class="space-y-0.5 min-w-0">
          <h4
            class="font-extrabold text-sm text-zinc-900 dark:text-white leading-tight"
          >
            {{ item.title }}
          </h4>
          <p
            class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed"
          >
            {{ item.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped></style>
