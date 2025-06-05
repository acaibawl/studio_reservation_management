<script setup lang="ts">
useHead({
  title: 'スタジオ予約管理システム',
});
const { $api } = useNuxtApp();
const { data, error } = await useAsyncData('health', () => $api('/health'));
</script>

<template>
  <div class="mt-10">
    api result: {{ data }} {{ error }}
  </div>
</template>

<style scoped>

</style>
