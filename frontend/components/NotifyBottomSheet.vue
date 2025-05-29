<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';

const notifyBottomSheet = useNotifyBottomSheetStore();
</script>

<template>
  <v-bottom-sheet
    v-model="notifyBottomSheet.isVisible"
  >
    <v-card
      :text="notifyBottomSheet.message"
    />
  </v-bottom-sheet>
</template>

<style scoped>

</style>
