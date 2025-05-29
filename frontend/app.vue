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
html {
  font-size: 90%;
  @media only screen and (min-width: 600px) {
    font-size: 93%;
  }
  @media only screen and (min-width: 1000px) {
    font-size: 97%;
  }
  @media only screen and (min-width: 1200px) {
    font-size: 100%;
  }
}
</style>
