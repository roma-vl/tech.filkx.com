<script setup>
import { ref } from 'vue';
import { store } from '@/store.js';

const carouselRef = ref(null);

const scrollCarousel = (direction) => {
  if (carouselRef.value) {
    const scrollAmount = direction === 'left' ? -320 : 320;
    carouselRef.value.scrollBy({
      left: scrollAmount,
      behavior: 'smooth'
    });
  }
};

const products = [
  {
    id: 10,
    name: 'Флагманський смартфон FilkxTech Titan X Pro Max 512GB',
    category: 'Смартфони',
    price: 1299.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCD4mJ768diZ-3ohTcgiepF2WHyNgmK4O62kdfBqnXW26Z5JHFeznt335OWFBdTwki9yXaAiUyq9Q7u2iBU4LYrgn-E0C9aG3Fv8Bky0_kZLcZiRjMOe6cbDYUayleaLx4NVA4sjA2RgGRydL-H3j9etBD_FH7eauOIjS0Hfq8woxSFaXXSbRIouRCyGxlCXx7Iyghg58Y6rX3Gl55sPn4ut4Rv5zmj9lAJzc2iAOTLpTAU5EEd6r0y4V8J_KZ6vmI_Xjfo1wOWIYo',
    description: 'Флагманський смартфон нового покоління в титановому корпусі з 512 ГБ надшвидкої пам\'яті та професійною потрійною камерою.'
  },
  {
    id: 11,
    name: 'Ультрабук FilkxTech ProBook Air M3 Titanium',
    category: 'Ноутбуки',
    price: 1599.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBJQNj0EC0emUkDyzp90oAj9LJPzpKPXT6LWZapnUxdjLR5TAEy9kuHaJr94uG2PzVeehWJTvry4Ee2usgri_YrTONN_qiozc1qDVA0IvjUJcjmfX4tlyXbG7cS55CYK0qdtU_5nNwNG1cvHRVWZ4RDHp1qQpcIlcPkq0kc54-dRRkNH3kiZOkglgTPglzSvbgSmBPllFt0kRkaUl6e5wMCrocXFoM-7JxcSFJb9mOT7tc5df8zDbGD5gV5FgaFM7ihaGDzCu-bE7Q',
    description: 'Тонкий, безвентиляторний титановий ультрабук на базі потужного чіпа M3, з дисплеєм Liquid Retina та зарядом на весь день.'
  },
  {
    id: 12,
    name: 'Ігровий ПК FilkxTech Aurora RGB Liquid Edition',
    category: 'Геймінг',
    price: 2199.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuAE9_IW8qjD9Nc7mEzTBtpVASRLILY00pNvfzJBc0ScXAbqlVP5-MBnPCK1_FQAYoA00CnZnnOf3DXUx5-3bjC9TfwHbFKDwu7kn1eTD6_9iS1v7zsuQvBzZd7Pui64wUk0nmM8DVTBsm1JxygZ0gvXHmgO2SnukHBkKJO8m_0nmqJ_M0lTRaSc9yTEpmjg7GtNXu6z3I_GpVNqH8x5MAutaaD-vKU1feM5vrr1dtaIrvVf5pqHczZWNOnPC4QB6kuwjAOed-Fn338',
    description: 'Високопродуктивний ігровий комп\'ютер із кастомним рідинним охолодженням, відеокартою RTX 4080 та процесором Ryzen 9.'
  },
  {
    id: 13,
    name: 'Аудіофільські навушники Vintage Series Walnut',
    category: 'Аудіотехніка',
    price: 450.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuBVfw-53YUOGGnBoNP1DcRvTzsoRmMHBmiwJBD1k7x9SS1j74hj221NxSS2aY4rrQ2TGdYlU-TZEQTQrQL8VOYnj71VUijn7oUH0epVhUD9j-HYXKvcxT_WNN-QsdhR2CCjNflwttO8BRSJfO9nWXZqAwQGND-hcNG8NeHUOUtZqWnD0RJ65tirhUidkCux8eJ2F0J9AuoAZQzu9vG1rGiQD0_CM-36L3Z5vrvwifWeVwUUNFTLB0rYDFLbuosmr50rxsLVJl-ZuPA',
    description: 'Відкриті динамічні навушники ручної роботи з преміального горіхового дерева та амбушурами з натуральної шкіри.'
  },
  {
    id: 14,
    name: 'Розумна колонка FilkxTech EchoSphere G2 Alexa/Google',
    category: 'Аудіотехніка',
    price: 299.00,
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuDPj2jX_NI6YnhpUgDqDHkjRyeW2EKyfTQoHQmuLzpIobASQ4gxKuixqe5u8J708jdxV2Zaw2z1-PM4SOnpiwkUKpWsEYu1Xii-NQJ1Jipsi2nl68gkV_FcIr8iE4C7FaoN5xPauziyMouuW158Nmv0Ps76AzgZ_V6rOgna1wINGvttbUBNXrNG25VrdbNfqVa8yASlBuYZHn5qBvutaAW_iE4HCbzyslvJrI4BzUQuNKGnSK3UTe12q37KdLS-Gb_o_h9l1gHkV44',
    description: 'Преміальна акустична система з голосовим асистентом, адаптивним налаштуванням звуку під кімнату та потужним басом.'
  }
];
</script>

<template>
  <!-- Recommended for You Section -->
  <section class="max-w-container-max mx-auto px-4 md:px-8 py-12 select-none font-sans">
    
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-10">
      <div class="space-y-1">
        <h2 class="font-extrabold text-2xl md:text-3xl text-zinc-900 dark:text-white tracking-tight">Рекомендовано для вас</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">На основі ваших інтересів та історії переглядів</p>
      </div>
      
      <!-- Arrow Controls -->
      <div class="flex gap-2">
        <button
          @click="scrollCarousel('left')"
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
        >
          <span class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350">chevron_left</span>
        </button>
        <button
          @click="scrollCarousel('right')"
          class="w-10 h-10 rounded-full border border-zinc-200 dark:border-zinc-800 flex items-center justify-center hover:bg-zinc-50 dark:hover:bg-zinc-850 hover:border-zinc-300 dark:hover:border-zinc-700 transition-all shadow-sm"
        >
          <span class="material-symbols-outlined text-[22px] text-zinc-650 dark:text-zinc-350">chevron_right</span>
        </button>
      </div>
    </div>

    <!-- Carousel Row -->
    <div
      ref="carouselRef"
      class="flex overflow-x-auto gap-6 hide-scrollbar scroll-smooth snap-x snap-mandatory px-1 py-4"
    >
      <!-- Recommendation Item -->
      <router-link
        v-for="prod in products"
        :key="prod.id"
        :to="{ name: 'product-detail', params: { id: prod.id } }"
        class="group cursor-pointer flex-shrink-0 w-[calc(50%-12px)] md:w-[calc(33.33%-16px)] lg:w-[calc(20%-20px)] min-w-[220px] snap-start block"
        @click="store.viewProduct(prod)"
      >
        <!-- Product Image Container -->
        <div class="aspect-square bg-zinc-50 dark:bg-zinc-900 rounded-3xl mb-4 overflow-hidden relative border border-zinc-100 dark:border-zinc-800 shadow-sm transition-all duration-300 group-hover:shadow-lg group-hover:border-zinc-200 dark:group-hover:border-zinc-700">
          <img
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            :src="prod.image"
            :alt="prod.name"
          />
          <!-- Wishlist -->
          <button
            @click.stop="store.toggleWishlist(prod)"
            class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 dark:bg-zinc-800/90 backdrop-blur-md shadow hover:scale-105 active:scale-95 transition-all flex items-center justify-center text-zinc-400 hover:text-rose-600 opacity-0 group-hover:opacity-100 z-10"
          >
            <span
              class="material-symbols-outlined text-[18px]"
              :class="{ 'fill text-rose-600': store.isInWishlist(prod.id) }"
              :style="store.isInWishlist(prod.id) ? 'font-variation-settings: \'FILL\' 1;' : ''"
            >
              favorite
            </span>
          </button>
        </div>

        <!-- Product Text Info -->
        <div class="flex flex-col gap-2">
          <span class="text-zinc-400 dark:text-zinc-500 font-extrabold text-[10px] uppercase tracking-wider">{{ prod.category }}</span>
          <h3 class="font-extrabold text-sm md:text-base text-zinc-800 dark:text-zinc-200 group-hover:text-[#00a046] transition-colors line-clamp-2 leading-snug min-h-[40px] md:min-h-[44px]">
            {{ prod.name }}
          </h3>
          <div class="flex items-center justify-between mt-1">
            <span class="font-black text-lg md:text-xl text-zinc-900 dark:text-white">${{ prod.price.toFixed(2) }}</span>
            <div class="flex gap-2">
              <!-- Compare -->
              <button
                @click.stop="store.toggleCompare(prod)"
                class="w-9 h-9 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-650 dark:text-zinc-350 flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 hover:scale-105 active:scale-95 transition-all"
                :class="{ 'bg-emerald-500/10 border-emerald-500/20 text-[#00a046]': store.isInCompare(prod.id) }"
                title="Порівняти"
              >
                <span class="material-symbols-outlined text-[18px]" :class="{ 'fill': store.isInCompare(prod.id) }">compare_arrows</span>
              </button>
              <!-- Buy -->
              <button
                @click.stop="store.addToCart(prod)"
                class="w-9 h-9 rounded-xl bg-[#00a046] hover:bg-[#00b050] text-white flex items-center justify-center hover:scale-105 active:scale-95 transition-all shadow-sm"
                title="В кошик"
              >
                <span class="material-symbols-outlined text-[18px]">add_shopping_cart</span>
              </button>
            </div>
          </div>
        </div>
      </router-link>
    </div>
  </section>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
