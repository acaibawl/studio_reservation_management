<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { useForm } from 'vee-validate';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

definePageMeta({
  layout: 'owner',
  middleware: ['only-owner'],
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();

const schema = yup.object({
  name: yup.string().required().max(50).label('スタジオ名'),
  start_at: yup.number().oneOf([0, 30]).label('開始時間'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    name: '',
    start_at: 0,
  },
});
const [name, nameProps] = defineField('name', yupFieldLazyVuetifyConfig);
const [startAt, startAtProps] = defineField('start_at', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $ownerApi<any>('/owner/studios', {
      method: 'POST',
      body: values,
    });
    navigateTo('/owner/studios');
    notifyBottomSheetStore.setMessage('スタジオを登録しました。');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-form @submit="onSubmit">
    <h3 class="text-h3">スタジオ登録</h3>

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
        <!-- @vue-ignore -->
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
      <v-btn type="submit" class="ml-5" color="primary">登録</v-btn>
    </v-row>
  </v-form>
</template>

<style scoped>

</style>
