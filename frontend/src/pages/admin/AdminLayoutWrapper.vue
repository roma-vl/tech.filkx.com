<template>
  <AdminLayout>
    <router-view v-slot="{ Component }">
      <transition
        name="page-fade"
        mode="out-in"
      >
        <component :is="Component" />
      </transition>
    </router-view>
  </AdminLayout>
</template>

<script setup>
import AdminLayout from "@/layouts/admin/AdminLayout.vue";
</script>

<style>
.page-fade-enter-active,
.page-fade-leave-active {
  transition:
    opacity 0.2s ease,
    transform 0.2s ease;
}

.page-fade-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.page-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
