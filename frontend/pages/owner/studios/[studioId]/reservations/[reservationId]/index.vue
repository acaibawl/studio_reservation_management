<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { Reservation, type ReservationResponse } from '~/types/reservation/Reservation';

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const studioId = route.params.studioId as string;
const reservationId = route.params.reservationId as string;

loadingOverlayStore.setActive();
const { data: reservationData, error } = await useAsyncData<ReservationResponse>(
  `/owner/studios/${studioId}/reservations/${reservationId}`,
  () => $ownerApi(`/owner/studios/${studioId}/reservations/${reservationId}`),
);
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
const reservation = new Reservation(reservationData.value!);

const handleDeleteClick = async () => {
  if (!confirm('予約を取り消しますか？')) {
    return;
  }
  try {
    loadingOverlayStore.setActive();
    await $ownerApi(`/owner/studios/${reservation.studioId}/reservations/${reservation.id}`, {
      method: 'DELETE',
    });
    navigateTo(`/owner/reservations/date/${reservation.startAtDateToYYYYMMDDKebab}`);
    notifyBottomSheetStore.setMessage(`${reservation.startAtDateToJaLocale} ${reservation.startAtTimeToJaLocale}の予約を取り消しました。`);
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};
</script>

<template>
  <div>
    <h3 class="text-h3">予約確認</h3>

    <v-row class="mt-4 justify-end">
      <p class="text-red text-caption cursor-pointer" @click="handleDeleteClick">予約を取り消す</p>
    </v-row>
    <p class="text-body-1 mt-5">{{ reservation.startAtDateToJaLocale }}</p>
    <p class="text-body-1">{{ reservation.startAtTimeToJaLocale }}開始</p>
    <p class="text-body-1 mt-5">{{ reservation.studioName }}</p>
    <p class="text-body-1 mt-5">利用時間</p>
    <p class="text-body-1 ml-5">{{ reservation.usageHour }}時間</p>
    <p class="text-body-1 mt-5">会員ID</p>
    <p class="text-body-1 ml-5">{{ reservation.memberId }}</p>
    <p class="text-body-1 mt-5">{{ reservation.memberName }}様</p>
    <p class="text-body-1 mt-5">メモ</p>
    <p class="text-body-1 ml-5">{{ reservation.memo }}</p>
    <v-row class="mt-5">
      <v-btn :to="`/owner/reservations/date/${reservation.startAtDateToYYYYMMDDKebab}`">戻る</v-btn>
      <v-btn :to="`/owner/studios/${studioId}/reservations/${reservationId}/edit`" class="ml-5" color="primary">修正</v-btn>
    </v-row>
  </div>
</template>

<style scoped>

</style>
