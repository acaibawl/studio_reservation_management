<script setup lang="ts">
import LoadingOverlay from '~/components/LoadingOverlay.vue';
import NotifyBottomSheet from '~/components/NotifyBottomSheet.vue';
</script>

<template>
  <v-app>
    <NuxtLayout>
      <NuxtPage />
      <loading-overlay />
      <notify-bottom-sheet />
    </NuxtLayout>
  </v-app>
</template>
