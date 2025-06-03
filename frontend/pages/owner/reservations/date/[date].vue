<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { isValidDateString } from '~/utils/isValidDateString';
import { ReservationQuotaStatusEnum } from '~/types/reservation/ReservationQuotaStatusEnum';
import { weekDays } from '~/utils/weekDay';
import * as yup from 'yup';
import { useForm } from 'vee-validate';
import { yupFieldImmediateVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

interface Reservations {
  date: string;
  studios: Studio[];
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
const dateString = route.params.date as string;
const date = new Date(dateString);

// パスパラメータdateが正しい日付じゃない場合は404とする
if (!isValidDateString(dateString)) {
  throw createError({ statusCode: 404, statusMessage: 'ページが見つかりません。' });
}

loadingOverlayStore.setActive();
const { data: reservations, error } = await useAsyncData<Reservations>(`/owner/reservations/get-quotas-by-date/${dateString}`, () => $ownerApi(`/owner/reservations/get-quotas-by-date/${dateString}`));
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
// スタジオをidの昇順に並び替え
reservations.value?.studios.sort((a: Studio, b: Studio) => a.id - b.id);

const schema = yup.object({
  selectDate: yup.string().required().label('日付'),
});
const { defineField } = useForm({
  validationSchema: schema,
});
const [selectDate, selectDateProps] = defineField('selectDate', yupFieldImmediateVuetifyConfig);

const showDateDialog = ref(false);
</script>

<template>
  <div>
    <v-row class="d-flex align-center justify-end justify-sm-center fill-height">
      <h3 class="text-h3">予約状況確認</h3>
      <h5 class="text-h5">{{ date.getFullYear() }}年{{ date.getMonth() + 1 }}月{{ date.getDate() }}日({{ weekDays[date.getDay()] }})</h5>
      <v-btn
        color="primary"
        size="x-small"
        text="日付変更"
        class="ml-5"
        @click="showDateDialog = true"
      />
      <v-dialog v-model="showDateDialog" max-width="500">
        <v-card title="日付変更">
          <v-text-field
            v-model="selectDate"
            v-bind="selectDateProps"
            label="日付"
            type="date"
            prepend-inner-icon="mdi-calendar-edit"
          />
          <v-btn
            color="primary"
            :to="`/owner/reservations/date/${selectDate}`"
            :disabled="!selectDate || selectDate === dateString"
          >
            確定
          </v-btn>
        </v-card>
      </v-dialog>
    </v-row>

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
          v-for="studio in reservations?.studios"
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
          v-for="studio in reservations?.studios"
          :key="`${studio.id}-${hour}`"
          class="with-divider pa-0 text-center"
          :class="{ 'not_available_quota': studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.NOT_AVAILABLE }"
        >
          <template v-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.NOT_AVAILABLE">
            <v-icon icon="mdi-close" color="blue-grey-lighten-3"/>
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.AVAILABLE">
            <NuxtLink :to="`/owner/reservations/studios/${studio.id}/reservation-quota/${date.toLocaleDateString('sv-SE')}/${hour}`"><v-icon icon="mdi-circle-outline" color="light-blue-darken-3"/></NuxtLink>
          </template>
          <template v-else-if="studio.reservation_quotas[hour].status === ReservationQuotaStatusEnum.RESERVED">
            <NuxtLink :to="`/owner/reservations/${studio.reservation_quotas[hour].reservation_id}/studios/${studio.id}`"><v-icon icon="mdi-check" color="teal-darken-4"/></NuxtLink>
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
.v-table__wrapper {
  overflow-x: scroll;
}
table {
  width: 100%;
  border-collapse: collapse;
}
td {
  width: auto;
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
