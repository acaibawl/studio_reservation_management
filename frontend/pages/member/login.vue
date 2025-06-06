<script setup lang="ts">
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useAuthMemberStore } from '~/store/authMember';

useHead({
  title: 'ログイン',
  meta: [
    { name: 'description', content: 'スタジオ予約管理システムのログインページです。' },
  ],
});

interface LoginResponse {
  expires_in: number;
  member_access_token: string;
  token_type: string;
}

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const { loginAsMemberWithToken } = useAuthMemberStore();

const { $api } = useNuxtApp();
const route = useRoute();

const isPasswordVisible = ref(false);

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  password: yup.string().required().min(8).max(32).label('パスワード'),
});
const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
});
const [email, emailProps] = defineField('email', yupFieldLazyVuetifyConfig);
const [password, passwordProps] = defineField('password', yupFieldLazyVuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    const response = await $api<LoginResponse>('/member-auth/login', {
      method: 'POST',
      body: values,
    });

    loginAsMemberWithToken(response.member_access_token, response.expires_in);
    const redirectedFrom = route.query.redirectedFrom;
    // リダイレクト先がない場合は当日の予約空き状況に遷移
    const date = new Date();
    const to = (redirectedFrom || `/reservations/availability/date/${date.toLocaleDateString('sv-SE')}`) as string;
    await navigateTo(to);
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
});
</script>

<template>
  <v-card class="mx-auto px-6 py-8 mt-10 d-flex align-center justify-center fill-height" max-width="640px">
    <v-form @submit="onSubmit">
      <h3 class="text-h3">スタジオ予約管理システム</h3>
      <h4 class="text-h4">ログイン</h4>
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
        <v-col cols="12">
          <v-text-field
            v-model="password"
            v-bind="passwordProps"
            label="パスワード"
            :append-inner-icon="isPasswordVisible ? 'mdi-eye-off' : 'mdi-eye'"
            :type="isPasswordVisible ? 'text' : 'password'"
            prepend-inner-icon="mdi-lock-outline"
            @click:append-inner="isPasswordVisible = !isPasswordVisible"
          />
        </v-col>
      </v-row>
      <v-row class="justify-center">
        <v-btn color="primary" type="submit">ログイン</v-btn>
        <v-btn color="secondary" class="ml-5" to="/member/signup">会員登録</v-btn>
      </v-row>
      <v-row class="justify-center">
        <NuxtLink to="/forgot-password" class="mt-5 text-decoration-none text-overline text-secondary">パスワードを忘れた方はこちら</NuxtLink>
      </v-row>
    </v-form>
  </v-card>
</template>

<style scoped>
.v-form {
  width: 100%;
}
</style>
