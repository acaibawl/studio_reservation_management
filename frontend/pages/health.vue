<script setup lang="ts">
const { $api } = useNuxtApp();
const { data, error } = await useAsyncData('health', () => $api('/health'));
</script>

<template>
  <div>
    api result: {{ data }} {{ error }}
  </div>
</template>

<style scoped>

</style>
