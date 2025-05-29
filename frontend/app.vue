<script setup lang="ts">
import LoadingOverlay from "~/components/LoadingOverlay.vue";
</script>

<template>
  <v-app>
    <NuxtLayout>
      <NuxtPage />
      <loading-overlay />
    </NuxtLayout>
  </v-app>
</template>
