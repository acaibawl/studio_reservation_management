<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import type { Studio } from '~/types/owner/Studio';
import * as yup from 'yup';
import { ErrorMessage, useForm } from 'vee-validate';
import { yupFieldVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';
import { handleFetchError } from '~/utils/handleFetchError';

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();

const { data: studio, error } = await useAsyncData<Studio>(`/owner/studios/${route.params.studioId}`, () => $ownerApi(`/owner/studios/${route.params.studioId}`));
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const schema = yup.object({
  id: yup.number().required().label('id'),
  name: yup.string().required().max(50).label('スタジオ名'),
  start_at: yup.number().oneOf([0, 30]).label('開始時間'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    id: studio.value?.id,
    name: studio.value?.name,
    start_at: studio.value?.start_at,
  },
});
// idはフィールドを用意せず、テキストとして表示する。
const [name, nameProps] = defineField('name', yupFieldVuetifyConfig);
const [startAt, startAtProps] = defineField('start_at', yupFieldVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $ownerApi<any>(`/owner/studios/${studio.value?.id}`, {
      method: 'PUT',
      body: values,
    });
    navigateTo('/owner/studios');
    notifyBottomSheetStore.setMessage('スタジオを更新しました。');
  } catch (e: unknown) {
    handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-form @submit="onSubmit">
    <h3 class="text-h3">スタジオ修正</h3>

    <h5 class="mt-5 text-h5">id</h5>
    <ErrorMessage as="div" name="id" v-slot="{ message }">
      <v-messages :messages="message" :active="true" color="red" class="mt-5 text-body-1 font-weight-bold" />
    </ErrorMessage>
    <p class="ml-5 text-body-1">{{ studio?.id }}</p>

    <v-row class="mt-5">
      <v-col>
        <v-text-field
          v-model="name"
          v-bind="nameProps"
          label="スタジオ名"
          type="text"
        />
      </v-col>
    </v-row>

    <v-row class="mt-5">
      <v-col>
        <v-select
          v-model="startAt"
          v-bind="startAtProps"
          label="開始時間"
          :items="[0, 30]"
        />
      </v-col>
    </v-row>

    <v-row class="mt-5">
      <v-btn to="/owner/studios">戻る</v-btn>
      <v-btn type="submit" class="ml-5" color="primary">修正</v-btn>
    </v-row>
  </v-form>
</template>

<style scoped>

</style>
