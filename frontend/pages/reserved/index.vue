<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { Reservation } from '~/types/reservation/Reservation';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

useHead({
  title: '予約済み',
  meta: [
    { name: 'description', content: '予約済みのスタジオ情報を表示します。' },
  ],
});

interface ReservationFetchData {
  id: number;
  studio_id: number;
  studio_name: string;
  start_at: string;
  finish_at: string;
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();

loadingOverlayStore.setActive();
const { data, error } = await useAsyncData<ReservationFetchData[]>('/me/reservations', () => $memberApi('/me/reservations'),
);
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const reservations = data.value?.map(reservation => new Reservation(
  reservation.id,
  reservation.studio_id,
  reservation.studio_name,
  reservation.start_at,
  reservation.finish_at,
),
);
const hasReservations = reservations!.length > 0;
</script>

<template>
  <div>
    <v-row>
      <v-col>
        <h3 class="text-h3">予約済み</h3>
      </v-col>
    </v-row>
    <div v-if="hasReservations">
      <v-card
        v-for="reservation in reservations" :key="reservation.id"
        class="mt-5 cursor-default"
        hover
        prepend-icon="mdi-guitar-electric"
        append-icon="mdi-piano"
      >
        <template #title>
          {{ reservation.startAtDateToJaLocale }}
        </template>
        <template #text>
          <p>{{ reservation.studioName }}</p>
          <p>{{ reservation.startAtTimeToJaLocale }}開始</p>
          <p class="mt-3">利用時間</p>
          <p>{{ reservation.usageHour }}時間</p>
        </template>
      </v-card>
    </div>
    <p v-else class="mt-5">予約情報がありません。</p>
  </div>
</template>

<style scoped>

</style>
