<script setup lang="ts">
const error = useError();
</script>

<template>
  <div>
    <p>{{ error?.message }}</p>
    <NuxtLink to="/">トップページへ戻る</NuxtLink>
  </div>
</template>
