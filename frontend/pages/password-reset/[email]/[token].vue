<script setup lang="ts">
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import * as yup from 'yup';
import { useForm } from 'vee-validate';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const { $api } = useNuxtApp();

const route = useRoute();
const email = route.params.email as string;
const token = route.params.token as string;
const passwordResetCompleted = ref(false);
const isPasswordVisible = ref(false);
const isPasswordConfirmationVisible = ref(false);

const schema = yup.object({
  password: yup.string().required().min(8).max(32).matches(/^[a-zA-Z0-9_-]+$/, 'パスワードは半角英数字または-と_の記号が使えます').label('パスワード'),
  password_confirmation: yup.string().oneOf([yup.ref('password')], 'パスワードが一致しません。').required().label('パスワード確認'),
  email: yup.string().email().required().label('メールアドレス'),
  email_verified_token: yup.string().required().label('トークン'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
  initialValues: {
    password: '',
    password_confirmation: '',
    email,
    email_verified_token: token,
  },
});
const [password, passwordProps] = defineField('password', yupFieldLazyVuetifyConfig);
const [passwordConfirmation, passwordConfirmationProps] = defineField('password_confirmation', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $api<unknown>('/member-auth/password-reset/reset', {
      method: 'POST',
      body: values,
    });

    passwordResetCompleted.value = true;
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
        <h3 class="text-h3">パスワード再設定</h3>
      </v-card-title>
    </v-card-item>

    <v-card-text v-if="!passwordResetCompleted">
      <v-form @submit.prevent="onSubmit">
        <v-row class="mt-5">
          <v-col cols="12">
            <p>メールアドレス： {{ email }}</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field
              v-model="password"
              v-bind="passwordProps"
              label="パスワード"
              :append-inner-icon="isPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
              :type="isPasswordVisible ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock"
              @click:append-inner="isPasswordVisible = !isPasswordVisible"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-text-field
              v-model="passwordConfirmation"
              v-bind="passwordConfirmationProps"
              label="パスワード確認"
              :append-inner-icon="isPasswordConfirmationVisible ? 'mdi-eye-off' : 'mdi-eye'"
              :type="isPasswordConfirmationVisible ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock-check"
              @click:append-inner="isPasswordConfirmationVisible = !isPasswordConfirmationVisible"
            />
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-btn color="primary" type="submit">パスワード変更</v-btn>
          </v-col>
        </v-row>
        <v-row class="justify-end">
          <NuxtLink to="/member/login" class="text-decoration-none text-overline text-secondary">ログインに戻る</NuxtLink>
        </v-row>
      </v-form>
    </v-card-text>

    <v-card-text v-else class="mt-5">
      <p>メールアドレス：{{ email }}</p>
      <p class="mt-5">パスワードを再設定しました。</p>
      <v-btn class="mt-5" to="/member/login">ログイン画面へ</v-btn>
    </v-card-text>
  </v-card>
</template>

<style scoped>

</style>
