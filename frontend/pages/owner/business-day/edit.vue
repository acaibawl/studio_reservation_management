<script setup lang="ts">
import { weekDays } from '~/utils/weekDay';
import * as yup from 'yup';
import { ErrorMessage, useForm } from 'vee-validate';
import { FetchError } from 'ofetch';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { formatTimeToHHmm } from '~/utils/formatTimeToHHmm';
import type { BusinessDay } from '~/types/owner/BusinessDay';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const { $ownerApi } = useNuxtApp();
const { data, error } = await useAsyncData<BusinessDay>('/owner/business-day', () => $ownerApi('/owner/business-day'));
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const schema = yup.object({
  regular_holidays: yup.array().of(yup.number().min(0).max(6).label('定休日')).label('定休日'),
  business_time: yup.object({
    open_time: yup.string().required().label('営業開始時間'),
    close_time: yup.string().required().label('営業終了時間'),
  }),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    regular_holidays: data.value?.regular_holidays?.map(holiday => holiday.code) ?? [],
    business_time: {
      // もしDBに12:30:30 のように秒まで入っていても、分までしか使わない
      open_time: formatTimeToHHmm(data.value?.business_time?.open_time),
      close_time: formatTimeToHHmm(data.value?.business_time?.close_time),
    },
  },
});
const [regularHolidays, regularHolidaysProps] = defineField('regular_holidays', { validateOnModelUpdate: true });
const [openTime, openTimeProps] = defineField('business_time.open_time', yupFieldLazyVuetifyConfig);
const [closeTime, closeTimeProps] = defineField('business_time.close_time', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    const { $ownerApi } = useNuxtApp();
    await $ownerApi<any>('/owner/business-day', {
      method: 'PUT',
      body: values,
    });
    navigateTo('/owner/business-day');
    notifyBottomSheetStore.setMessage('営業時間・定休日を更新しました。');
  } catch (e: unknown) {
    if (e instanceof FetchError) {
      if (e.status === 422) {
        setErrors(e.data.errors);
      } else {
        console.error(e);
        notifyBottomSheetStore.setMessage(e.message);
      }
    }
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-form @submit="onSubmit">
    <h3 class="text-h3">営業時間・定休日 修正</h3>

    <h5 class="text-h5 mt-5">定休日</h5>
    <ErrorMessage as="div" name="regular_holidays" v-slot="{ message }">
      <v-messages :messages="message" :active="true" color="red" class="mt-5 text-body-1 font-weight-bold" />
    </ErrorMessage>
    <ul class="d-flex flex-row mt-5">
      <li
        v-for="n in Array.from(Array(7).keys())"
        :key="n"
      >
        <v-checkbox
          v-model="regularHolidays"
          v-bind="regularHolidaysProps"
          :label="weekDays[n]"
          :value="n"
        />
      </li>
    </ul>

    <h5 class="text-h5 mt-5">営業時間</h5>
    <v-row class="mt-5">
        <v-col cols="3" class="ml-5">
          <v-text-field
            v-model="openTime"
            v-bind="openTimeProps"
            label="営業開始時間"
            type="time"
          />
        </v-col>
        <v-col cols="1" class="mt-4">
          〜
        </v-col>
        <v-col cols="3">
          <v-text-field
            v-model="closeTime"
            v-bind="closeTimeProps"
            label="営業終了時間"
            type="time"
          />
        </v-col>
    </v-row>
    <v-row class="mt-5">
      <v-btn to="/owner/business-day">戻る</v-btn>
      <v-btn type="submit" class="ml-5" color="primary">修正</v-btn>
    </v-row>
  </v-form>
</template>

<style scoped>
ul {
  list-style: none;
}
</style>
