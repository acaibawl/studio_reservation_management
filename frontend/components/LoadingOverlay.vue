<script setup lang="ts">
import { useLoadingOverlayStore } from '~/store/loadingOverlay';

const loadingOverlayStore = useLoadingOverlayStore();
</script>

<template>
  <v-overlay
    :model-value="loadingOverlayStore.isLoading"
    class="align-center justify-center"
    persistent
  >
    <v-progress-circular
      color="primary"
      size="64"
      indeterminate
    />
  </v-overlay>
</template>

<style scoped>

</style>
