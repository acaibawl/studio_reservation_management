<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import {ReservationQuotaStatusEnum} from "~/types/reservation/ReservationQuotaStatusEnum";
import type {ReservationQuotasResponse} from "~/types/reservation/Reservation";

const props = defineProps<{
  dateString: string;
  isOwner: boolean;
}>();

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi, $memberApi } = useNuxtApp();

loadingOverlayStore.setActive();
const { data: quotaResponse, error } = props.isOwner ?
  await useAsyncData<ReservationQuotasResponse>(`/owner/reservations/get-quotas-by-date/${props.dateString}`, () => $ownerApi(`/owner/reservations/get-quotas-by-date/${props.dateString}`))
  : await useAsyncData<ReservationQuotasResponse>(`/reservation_availability/date/${props.dateString}`, () => $memberApi(`/reservation_availability/date/${props.dateString}`));
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const linkUrlPrefix = props.isOwner ? 'owner/' : '';
</script>

<template>
  <div class="table-scroll-x">
    <v-table
      fixed-header
      height="70vh"
      hover
      class="border mt-5"
      density="compact"
    >
      <thead>
      <tr>
        <th class="text-left pa-0">
          時刻
        </th>
        <th
          v-for="studio in quotaResponse?.studios"
          :key="studio.id"
          class="text-left with-divider pa-0"
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
        <td class="text-no-wrap pa-0">
          {{ hour }} 時
        </td>
        <td
          v-for="studio in quotaResponse?.studios"
          :key="`${studio.id}-${hour}`"
          class="with-divider pa-0 text-center"
          :class="{ 'not_available_quota': studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.NOT_AVAILABLE }"
        >
          <template v-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.NOT_AVAILABLE">
            <v-icon icon="mdi-close" color="blue-grey-lighten-3"/>
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.AVAILABLE">
            <NuxtLink :to="`/${linkUrlPrefix}reservations/studios/${studio.id}/reservation-quota/${dateString}/${hour}`"><v-icon icon="mdi-circle-outline" color="light-blue-darken-3"/></NuxtLink>
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.RESERVED">
            <NuxtLink :to="`/${linkUrlPrefix}reservations/${studio.reservation_quotas[hour].reservation_id}/studios/${studio.id}`"><v-icon icon="mdi-check" color="teal-darken-4"/></NuxtLink>
          </template>
        </td>
      </tr>
      </tbody>
    </v-table>
  </div>
</template>

<style scoped>
.not_available_quota {
  background-color: rgba(0, 0, 0, 0.05);
}
.with-divider {
  border-left: 1px solid;
  border-color: rgba(0, 0, 0, 0.12);
}
.table-scroll-x{
  overflow-x:scroll;
  max-width: 100vw;
}

@media (max-width: 600px) {
  th {
    padding: 0 !important;
    font-size: 0.6rem !important;
  }
  td {
    padding: 0 !important;
    font-size: 0.7rem !important;
  }
}
</style>
