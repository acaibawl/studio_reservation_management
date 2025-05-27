<script setup lang="ts">
definePageMeta({
  middleware: ['only-owner'],
});

const route = useRoute();
const id = route.params.id;
</script>

<template>
  <h1>{{ id }}</h1>
</template>

<style scoped>

</style>
