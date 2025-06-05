<script setup lang="ts">
useHead({
  titleTemplate: (titleChunk) => {
    return titleChunk ? `${titleChunk} | スタジオ予約` : 'スタジオ予約';
  },
});
const date = new Date();
</script>

<template>
  <div>
    <v-system-bar color="secondary">
      <NuxtLink class="text-white text-decoration-none" :to="`/reservations/availability/date/${date.toLocaleDateString('sv-SE')}`">スタジオ予約管理システム</NuxtLink>
    </v-system-bar>
    <slot />
  </div>
</template>

<style scoped>
.v-container {
  min-width: 390px;
}
</style>
