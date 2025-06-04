<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { useForm } from 'vee-validate';

definePageMeta({
  layout: 'owner',
  middleware: ['only-owner'],
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
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const studioId = route.params.studioId as string;
const dateString = route.params.date as string;
const hourString = route.params.hour as string;

loadingOverlayStore.setActive();
const { data: fetchedReservationQuota, error } = await useAsyncData<ReservationQuota>(
  `/owner/studios/${studioId}/reservation-quota/${dateString}/${hourString}`,
  () => $ownerApi(`/owner/studios/${studioId}/reservation-quota/${dateString}/${hourString}`),
);
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
// 予約不可能な枠を表示しようとした場合は予約状況確認に遷移する
if (fetchedReservationQuota.value?.max_available_hour === 0) {
  await navigateTo(`/owner/reservations/date/${dateString}`);
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
    memo: '',
  },
});
const [usageHour, usageHourProps] = defineField<'usage_hour', number>('usage_hour', yupFieldLazyVuetifyConfig);
const [memo, memoProps] = defineField('memo', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    const { reservation_id } = await $ownerApi<{ reservation_id: number }>(`/owner/studios/${fetchedReservationQuota.value?.studio_id}/reservations`, {
      method: 'POST',
      body: values,
    });
    navigateTo(`/owner/reservations/${reservation_id}/studios/${studioId}`);
    notifyBottomSheetStore.setMessage('予約を登録しました。');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-form @submit="onSubmit">
    <h3 class="text-h3">予約登録</h3>

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
    <v-select
      v-model="usageHour"
      v-bind="usageHourProps"
      label="利用時間"
      :items="[...Array(fetchedReservationQuota?.max_available_hour).keys()].map(i => i + 1)"
    />
    <p class="text-body-1 mt-5">会員ID</p>
    <p class="text-body-1 ml-5">9999999（オーナー予約用）</p>
    <p class="text-body-1 mt-5">メモ</p>
    <v-textarea
      v-model="memo"
      v-bind="memoProps"
      label="メモ"
      counter
      variant="solo-filled"
      auto-grow
      class="mt-5"
    />
    <v-row class="mt-5">
      <v-btn :to="`/owner/reservations/date/${dateString}`">戻る</v-btn>
      <v-btn type="submit" class="ml-5" color="primary">登録</v-btn>
    </v-row>
  </v-form>
</template>

<style scoped>

</style>
