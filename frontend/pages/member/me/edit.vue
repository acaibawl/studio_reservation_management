<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import type { Me } from '~/types/member/Me';
import * as yup from 'yup';
import { useForm } from 'vee-validate';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

useHead({
  title: '会員情報修正',
  meta: [
    { name: 'description', content: '会員の個人情報を修正します。' },
  ],
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();

loadingOverlayStore.setActive();
const { data, error } = await useAsyncData<Me>('/member-auth/me', () => $memberApi('/member-auth/me'));
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const schema = yup.object({
  name: yup.string().required().max(50).label('名前'),
  address: yup.string().required().max(128).label('住所'),
  tel: yup.string().required().min(10).max(11).matches(/^\d+$/).label('電話番号'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    name: data.value?.name ?? '',
    address: data.value?.address ?? '',
    tel: data.value?.tel ?? '',
  },
});
const [name, nameProps] = defineField('name', yupFieldLazyVuetifyConfig);
const [address, addressProps] = defineField('address', yupFieldLazyVuetifyConfig);
const [tel, telProps] = defineField('tel', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $memberApi<unknown>('/member-auth/member', {
      method: 'PUT',
      body: values,
    });
    navigateTo('/member/me');
    notifyBottomSheetStore.setMessage('会員情報を更新しました。');
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
      <v-col>
        <h3 class="text-h3">会員情報修正</h3>
      </v-col>
    </v-row>
    <v-sheet width="95vw">
      <v-form @submit.prevent="onSubmit">
        <v-row>
          <v-col>
            <v-text-field
              v-model="name"
              v-bind="nameProps"
              label="名前"
              type="text"
              prepend-inner-icon="mdi-card-account-details"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field
              v-model="address"
              v-bind="addressProps"
              label="住所"
              type="text"
              prepend-inner-icon="mdi-map-marker"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field
              v-model="tel"
              v-bind="telProps"
              label="電話番号（ハイフンなし）"
              type="text"
              prepend-inner-icon="mdi-phone"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-btn to="/member/me">戻る</v-btn>
            <v-btn color="primary" class="ml-5" type="submit">修正</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-sheet>
  </div>
</template>

<style scoped>

</style>
