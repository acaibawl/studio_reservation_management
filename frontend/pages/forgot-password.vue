<script setup lang="ts">
import { useForm } from 'vee-validate';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

useHead({
  title: 'パスワード再設定',
  meta: [
    { name: 'description', content: 'スタジオ予約管理システムのパスワード再設定ページです。' },
  ],
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $api } = useNuxtApp();
const sendCompleted = ref(false);

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
});
const { defineField, setErrors, handleSubmit } = useForm({
  validationSchema: schema,
});
const [email, emailProps] = defineField('email', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $api<unknown>('/member-auth/password-reset/send-email', {
      method: 'POST',
      body: values,
    });
    sendCompleted.value = true;
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});

</script>

<template>
  <v-card class="mx-auto px-6 py-8 mt-10 align-center justify-center fill-height" max-width="640px">
    <v-card-item>
      <v-card-title>
        <h4 class="text-h4">パスワード再設定メールの送信</h4>
      </v-card-title>
    </v-card-item>

    <v-card-text v-if="!sendCompleted">
      <v-form @submit.prevent="onSubmit">
        <v-row class="mt-5">
          <v-col cols="12">
            <v-text-field
              v-model="email"
              v-bind="emailProps"
              label="メールアドレス"
              type="email"
              prepend-inner-icon="mdi-email-outline"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-btn to="/member/login">戻る</v-btn>
            <v-btn color="primary" class="ml-5" type="submit">送信</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>

    <v-card-text v-else class="mt-5">
      <p>メールアドレス：{{ email }}</p>
      <p class="mt-5">パスワード再設定のメールを送信しました。</p>
      <p>メールに記載されているリンクからパスワードを再設定してください。</p>
      <v-btn class="mt-5" to="/member/login">ログイン画面へ</v-btn>
    </v-card-text>
  </v-card>
</template>

<style scoped>

</style>
