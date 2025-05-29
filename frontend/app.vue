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

<style>
/* Vuetifyと同じBreakpointsを設定 */
html {
  font-size: 90%;
  @media only screen and (min-width: 600px) {
    font-size: 93%;
  }
  @media only screen and (min-width: 960px) {
    font-size: 97%;
  }
  @media only screen and (min-width: 1280px) {
    font-size: 100%;
  }
}
</style>
