<script setup>
const items = [
  {
    icon: 'local_shipping',
    title: 'Безкоштовна доставка',
    description: 'Для всіх замовлень від $500 по всій Україні.'
  },
  {
    icon: 'verified',
    title: 'Офіційна гарантія',
    description: '100% оригінальна продукція з гарантійним обслуговуванням.'
  },
  {
    icon: 'assignment_return',
    title: 'Повернення протягом 14 днів',
    description: 'Простий обмін або повернення коштів без зайвих питань.'
  },
  {
    icon: 'support_agent',
    title: 'Цілодобова підтримка 24/7',
    description: 'Наші експерти завжди на зв\'язку, щоб допомогти з вибором.'
  }
];
</script>

<template>
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-6 select-none font-sans">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div 
        v-for="item in items" 
        :key="item.title"
        class="flex items-start gap-4 p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-100 dark:border-zinc-800 rounded-2xl hover:border-zinc-200 dark:hover:border-zinc-700 transition-colors"
      >
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/5 flex items-center justify-center flex-shrink-0">
          <span class="material-symbols-outlined text-[#00a046] text-xl font-bold">
            {{ item.icon }}
          </span>
        </div>
        <div class="space-y-1">
          <h4 class="font-extrabold text-xs text-zinc-900 dark:text-white leading-tight">
            {{ item.title }}
          </h4>
          <p class="text-[11px] text-zinc-500 leading-relaxed">
            {{ item.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
</style>
