<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { useForm } from 'vee-validate';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

interface ReservationQuota {
  date: string;
  hour: number;
  max_available_hour: number;
  studio_id: number;
  studio_name: string;
  studio_start_at: number;
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();
const route = useRoute();
const studioId = route.params.studioId as string;
const dateString = route.params.date as string;
const hourString = route.params.hour as string;
const reservationCompleted = ref(false);

loadingOverlayStore.setActive();
const { data: fetchedReservationQuota, error } = await useAsyncData<ReservationQuota>(
  `/studios/${studioId}/reservation-quota/${dateString}/${hourString}`,
  () => $memberApi(`/studios/${studioId}/reservation-quota/${dateString}/${hourString}`),
);
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
// 予約不可能な枠を表示しようとした場合は空き状況確認に遷移する
if (fetchedReservationQuota.value?.max_available_hour === 0) {
  await navigateTo(`/reservations/availability/date/${dateString}`);
}

const startDate = new Date(dateString);
startDate.setHours(parseInt(hourString));
startDate.setMinutes(fetchedReservationQuota.value!.studio_start_at);

const schema = yup.object({
  start_at: yup.string().required().label('利用開始時間'),
  usage_hour: yup.number().required().min(1).max(6).label('利用時間'),
  memo: yup.string().nullable().max(512).label('メモ'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    start_at: startDate.toLocaleString('sv-SE'),
    usage_hour: 1,
  },
});
const [usageHour, usageHourProps] = defineField<'usage_hour', number>('usage_hour', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $memberApi<unknown>(`/studios/${studioId}/reservations`, {
      method: 'POST',
      body: values,
    });
    reservationCompleted.value = true;
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <div>
    <v-row>
      <v-col cols="12">
        <h3 class="text-h3">予約申し込み</h3>

        <p class="text-body-1 mt-5">{{
            startDate.toLocaleDateString('ja-JP', {
              year: 'numeric',
              month: 'short',
              day: 'numeric',
              weekday: 'short'
            })
          }}</p>
        <p class="text-body-1">{{
            startDate.getHours().toString().padStart(2, '0')
          }}時{{ startDate.getMinutes().toString().padStart(2, '0') }}分開始</p>
        <p class="text-body-1 mt-5">{{ fetchedReservationQuota?.studio_name }}</p>
      </v-col>
    </v-row>
    <template v-if="!reservationCompleted">
      <v-row>
        <v-form @submit="onSubmit">
          <v-select
            v-model="usageHour"
            v-bind="usageHourProps"
            label="利用時間"
            :items="[...Array(fetchedReservationQuota?.max_available_hour).keys()].map(i => i + 1)"
          />
          <v-row class="mt-5">
            <v-btn :to="`/reservations/availability/date/${dateString}`">戻る</v-btn>
            <v-btn type="submit" class="ml-5" color="primary">予約</v-btn>
          </v-row>
        </v-form>
      </v-row>
    </template>
    <template v-else>
      <p>利用時間：{{ usageHour }}時間</p>
      <p class="mt-5">上記の内容で予約を承りました。</p>
      <v-row class="mt-5">
        <v-btn :to="`/reservations/availability/date/${dateString}`">予約空き状況へ戻る</v-btn>
      </v-row>
    </template>
  </div>
</template>

<style scoped>

</style>
