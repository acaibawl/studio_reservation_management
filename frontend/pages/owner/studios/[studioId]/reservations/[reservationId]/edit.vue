<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { Reservation, type ReservationResponse } from '~/types/reservation/Reservation';
import * as yup from 'yup';
import { useForm } from 'vee-validate';

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
const reservation = new Reservation(
  reservationData.value!.reservation.id,
  reservationData.value!.reservation.studio_id,
  reservationData.value!.reservation.studio_name,
  reservationData.value!.reservation.start_at,
  reservationData.value!.reservation.finish_at,
  reservationData.value!.reservation.max_usage_hour,
  reservationData.value!.reservation.member_id,
  reservationData.value!.reservation.member_name,
  reservationData.value!.reservation.memo,
);

const schema = yup.object({
  usage_hour: yup.number().required().min(1).max(6).label('利用時間'),
  memo: yup.string().nullable().max(512).label('メモ'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    usage_hour: reservation.usageHour,
    memo: reservation.memo,
  },
});
const [usageHour, usageHourProps] = defineField('usage_hour', yupFieldLazyVuetifyConfig);
const [memo, memoProps] = defineField('memo', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $ownerApi<any>(`/owner/studios/${studioId}/reservations/${reservationId}`, {
      method: 'PATCH',
      body: values,
    });
    navigateTo(`/owner/studios/${studioId}/reservations/${reservationId}`);
    notifyBottomSheetStore.setMessage('予約を修正しました。');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-form @submit="onSubmit">
    <h3 class="text-h3">予約修正</h3>

    <p class="text-body-1 mt-5">{{ reservation.startAtDateToJaLocale }}</p>
    <p class="text-body-1">{{ reservation.startAtTimeToJaLocale }}開始</p>
    <p class="text-body-1 mt-5">{{ reservation.studioName }}</p>
    <!-- @vue-ignore -->
    <v-select
      v-model="usageHour"
      v-bind="usageHourProps"
      label="利用時間"
      :items="[...Array(reservation.maxUsageHour).keys()].map(i => i + 1)"
    />
    <p class="text-body-1 mt-5">会員ID</p>
    <p class="text-body-1 ml-5">{{ reservation.memberId }}</p>
    <p class="text-body-1 mt-5">{{ reservation.memberName }}様</p>
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
      <v-btn :to="`/owner/studios/${studioId}/reservations/${reservationId}`">戻る</v-btn>
      <v-btn type="submit" class="ml-5" color="primary">修正</v-btn>
    </v-row>
  </v-form>
</template>

<style scoped>

</style>
