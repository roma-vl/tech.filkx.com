<script setup>
import { store } from '@/store.js';
</script>

<template>
  <div class="space-y-6 animate-fade font-sans select-none">
    <div v-if="store.compare.length > 0" class="overflow-x-auto bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 shadow-sm">
      <table class="w-full min-w-[800px] border-collapse text-left text-sm">
        <thead>
          <tr class="bg-zinc-50 dark:bg-zinc-850 border-b border-zinc-100 dark:border-zinc-800 text-zinc-400 dark:text-zinc-500 font-extrabold uppercase text-[10px] tracking-wider">
            <th class="p-5 w-1/4">Параметри</th>
            <th v-for="product in store.compare" :key="product.id" class="p-5 relative text-center">
              <button @click="store.removeFromCompare(product.id)" class="absolute top-3 right-3 text-zinc-400 hover:text-rose-500 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
              </button>
              <span class="inline-block py-1">Товар</span>
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 text-zinc-700 dark:text-zinc-300">
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">Зображення</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5">
              <div class="flex flex-col gap-3 items-center">
                <img :src="product.image" :alt="product.name" class="w-20 h-20 object-contain mx-auto bg-white rounded-lg border border-zinc-100 dark:border-zinc-800 p-2" />
                <h4 class="font-extrabold text-center text-xs md:text-sm line-clamp-2 text-zinc-800 dark:text-zinc-200">{{ product.name }}</h4>
              </div>
            </td>
          </tr>
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">Ціна</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 font-black text-[#00a046] text-center text-sm md:text-base">{{ product.price.toFixed(2) }} ₴</td>
          </tr>
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">Бренд</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 text-center text-xs md:text-sm">{{ product.brand || 'FilkxTech' }}</td>
          </tr>
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">ОЗУ (RAM)</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 text-center text-xs md:text-sm">{{ product.ram || 'Не вказано' }}</td>
          </tr>
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">Рейтинг</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 text-center">
              <div class="flex items-center justify-center gap-1.5 text-amber-400">
                <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">star</span>
                <span class="font-extrabold text-zinc-650 dark:text-zinc-350 text-xs md:text-sm">{{ product.rating || '5.0' }} ({{ product.reviews || 0 }})</span>
              </div>
            </td>
          </tr>
          <tr>
            <td class="p-5 font-extrabold bg-zinc-50/50 dark:bg-zinc-850/30 text-zinc-900 dark:text-white">Опис</td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 text-zinc-500 dark:text-zinc-400 text-xs md:text-sm leading-relaxed max-w-[250px]">{{ product.description }}</td>
          </tr>
          <tr>
            <td class="p-5 bg-zinc-50/50 dark:bg-zinc-850/30"></td>
            <td v-for="product in store.compare" :key="product.id" class="p-5 text-center">
              <button @click="store.addToCart(product)" class="bg-[#00a046] hover:bg-[#00b050] text-white px-4 py-2.5 rounded-lg font-extrabold text-xs transition-all uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
                <span class="material-symbols-outlined text-[16px] md:text-[18px]">shopping_cart</span> У кошик
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="bg-white dark:bg-zinc-900 rounded-xl border border-zinc-100 dark:border-zinc-800 p-12 text-center shadow-sm">
      <span class="material-symbols-outlined text-[48px] text-zinc-350 dark:text-zinc-600 mb-4">compare_arrows</span>
      <h3 class="font-extrabold text-lg text-zinc-850 dark:text-zinc-150">Немає товарів для порівняння</h3>
      <p class="text-xs md:text-sm text-zinc-450 dark:text-zinc-500 max-w-sm mx-auto mt-2">Додайте товари до порівняння, натиснувши кнопку порівняння на картках товарів.</p>
      <a href="/catalog" class="inline-block bg-[#00a046] hover:bg-[#00b050] text-white font-extrabold text-xs md:text-sm py-3 px-6 rounded-lg transition-all mt-6 shadow-sm">Перейти до каталогу</a>
    </div>
  </div>
</template>

<style scoped>
.animate-fade { animation: fadeIn .25s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(4px); } to { opacity:1; transform:translateY(0); } }
</style>
