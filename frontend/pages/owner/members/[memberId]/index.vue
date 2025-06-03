<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { Reservation } from '~/types/reservation/Reservation';

interface Member {
  member: {
    id: number;
    name: string;
    email: string;
    address: string;
    tel: string;
    reservations: {
      id: number;
      member_id: number;
      studio_id: number;
      studio_name: string;
      start_at: string;
      finish_at: string;
      memo: string;
    }[];
  };
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const memberId = route.params.memberId;

loadingOverlayStore.setActive();
const { data, error } = await useAsyncData<Member>(`/owner/members/${memberId}`, () => $ownerApi(`/owner/members/${memberId}`));
if (error.value) {
  notifyBottomSheetStore.setMessage(error.value.message);
  console.error(error.value);
}
const reservations = data.value?.member.reservations.map(reservation => new Reservation(
  reservation.id,
  reservation.studio_id,
  reservation.studio_name,
  reservation.start_at,
  reservation.finish_at,
  undefined,
  reservation.member_id,
  data.value?.member.name,
  reservation.memo,
),
);
loadingOverlayStore.resetLoading();

const hasReservations = reservations!.length > 0;
</script>

<template>
  <div>
    <h3 class="text-h3">会員</h3>
    <p class="text-body-1 mt-5">ID:{{ data?.member.id }}</p>
    <p class="text-body-1 mt-5">{{ data?.member.name }}&nbsp;様</p>
    <p class="text-body-1 mt-5">住所：{{ data?.member.address }}</p>
    <p class="text-body-1 mt-5">電話：{{ data?.member.tel }}</p>
    <h5 class="text-h5 mt-5">予約</h5>
    <div v-if="hasReservations">
      <v-card
        v-for="reservation in reservations" :key="reservation.id"
        class="mt-5"
        hover
        prepend-icon="mdi-guitar-electric"
        append-icon="mdi-piano"
        :to="`/owner/studios/${reservation.studioId}/reservations/${reservation.id}`"
      >
        <template #title>
          {{ reservation.startAtDateToJaLocale }}
        </template>
        <template #text>
          <p>{{ reservation.studioName }}</p>
          <p>{{ reservation.startAtTimeToJaLocale }}開始</p>
          <p class="mt-3">利用時間</p>
          <p>{{ reservation.usageHour }}時間</p>
          <p class="mt-3">メモ：{{ reservation.memo }}</p>
        </template>
      </v-card>
    </div>
    <p v-else class="mt-5">予約情報がありません。</p>
  </div>
</template>

<style scoped>

</style>
