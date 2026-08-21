<template>
  <div class="min-h-screen flex flex-col justify-center items-center">
    <h1 class="text-6xl font-bold mb-4">
      404
    </h1>
    <p class="text-xl mb-6">
      Сторінку не знайдено
    </p>
    <div
      ref="container"
      class="w-[500px]"
    />
    <router-link
      to="/"
      class="text-blue-600 hover:underline"
    >
      Повернутися на головну
    </router-link>
  </div>
</template>

<script setup lang="ts">
import lottie from "lottie-web";
import { onMounted, ref } from "vue";
import CheckedAnimation from "@/assets/animation/404blue.json";

const container = ref<HTMLDivElement | null>(null);

onMounted(() => {
  if (!container.value) return;
  lottie.loadAnimation({
    container: container.value,
    renderer: "svg",
    loop: true,
    autoplay: true,
    animationData: CheckedAnimation,
  });
});
</script>
