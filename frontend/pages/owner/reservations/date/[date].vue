<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { isValidDateString } from '~/utils/isValidDateString';
import {
  ReservationQuotaStatusEnum,
  reservationQuotaStatusEnumLabel
} from "~/types/reservation/ReservationQuotaStatusEnum";

interface Reservations {
  date: string;
  studios: Studio[]
}

interface ReservationQuotaNotReserved {
  hour: number;
  status: ReservationQuotaStatusEnum.NOT_AVAILABLE | ReservationQuotaStatusEnum.AVAILABLE;
}

interface ReservationQuotaReserved {
  hour: number;
  status: ReservationQuotaStatusEnum.RESERVED;
  reservation_id: number;
}

interface Studio {
  id: number;
  name: string;
  start_at: number;
  reservation_quotas: (ReservationQuotaNotReserved | ReservationQuotaReserved)[];
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const date = route.params.date as string;

// パスパラメータdateが正しい日付じゃない場合は404とする
if (!isValidDateString(date)) {
  throw createError({ statusCode: 404, statusMessage: 'ページが見つかりません。' });
}

loadingOverlayStore.setActive();
const { data: reservations, error } = await useAsyncData<Reservations>(`/owner/reservations/get-quotas-by-date/${date}`, () => $ownerApi(`/owner/reservations/get-quotas-by-date/${date}`));
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
// スタジオをidの昇順に並び替え
reservations.value?.studios.sort((a: Studio, b: Studio) => a.id - b.id);
</script>

<template>
  <div>
    <h3 class="text-h3">予約状況確認</h3>

    <v-table
      fixed-header
      height="70vh"
      hover
      class="border mt-5"
    >
      <thead>
      <tr>
        <th class="text-left">
          時刻
        </th>
        <th
          v-for="studio in reservations?.studios"
          :key="studio.id"
          class="text-left with-divider"
        >
          {{ studio.name }} （{{ studio.start_at }} 分開始）
        </th>
      </tr>
      </thead>
      <tbody>
      <tr
        v-for="hour in Array.from(Array(24).keys())"
        :key="hour"
      >
        <td>
          {{ hour }} 時
        </td>
        <td
          v-for="studio in reservations?.studios"
          :key="`${studio.id}-${hour}`"
          class="with-divider"
        >
          <template v-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.NOT_AVAILABLE">
            {{ reservationQuotaStatusEnumLabel(studio.reservation_quotas[hour].status) }}
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.AVAILABLE">
            {{ reservationQuotaStatusEnumLabel(studio.reservation_quotas[hour].status) }}
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.RESERVED">
            <NuxtLink :to="`/owner/reservations/${studio.reservation_quotas[hour].reservation_id}`">{{ reservationQuotaStatusEnumLabel(studio.reservation_quotas[hour].status) }}</NuxtLink>
          </template>
        </td>
      </tr>
      </tbody>
    </v-table>
  </div>
</template>

<style scoped>
.with-divider {
  border-left: 1px solid;
  border-color: rgba(0, 0, 0, 0.12);
}
</style>
