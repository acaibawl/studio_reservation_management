<script setup lang="ts">
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { useAuthOwnerStore } from '~/store/authOwner';
import { FetchError } from 'ofetch';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

const isPasswordVisible = ref(false);
const loginLoading = ref(false);

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  password: yup.string().required().min(8).max(32).label('パスワード'),
});

const { defineField, handleSubmit, setErrors } = useForm({
  validationSchema: schema,
});

const [email, emailProps] = defineField('email', yupFieldLazyVuetifyConfig);
const [password, passwordProps] = defineField('password', yupFieldLazyVuetifyConfig);
const errorMessage = ref('');

const onSubmit = handleSubmit(async (values) => {
  try {
    loginLoading.value = true;
    errorMessage.value = '';

    const { $api } = useNuxtApp();
    const response = await $api<any>('/owner-auth/login', {
      method: 'POST',
      body: values,
    });

    const { loginAsOwner } = useAuthOwnerStore();
    loginAsOwner(response.owner_access_token);
    const route = useRoute();
    const redirectedFrom = route.query.redirectedFrom;
    const date = new Date();
    // リダイレクト先がない場合は当日の予約状況一覧に遷移
    const to = (redirectedFrom || `/owner/reservations/date/${date.toLocaleDateString('sv-SE')}`) as string;
    navigateTo(to);
  } catch (e: unknown) {
    if (e instanceof FetchError) {
      if (e.status === 401) {
        errorMessage.value = 'メールアドレス又はパスワードが違います。';
      } else if (e.status === 422) {
        setErrors(e.data.errors);
      } else {
        errorMessage.value = e.message;
      }
    }
  } finally {
    loginLoading.value = false;
  }
});
</script>

<template>
  <v-card class="mx-auto px-6 py-8 mt-10 d-flex align-center justify-center fill-height" max-width="344">
    <v-form @submit="onSubmit">
      <h3 class="text-h3">スタジオ予約管理システム</h3>
      <h4 class="text-h4"><span class="text-red">オーナー</span>ログイン</h4>
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
      <v-btn color="primary" type="submit" :loading="loginLoading">ログイン</v-btn>
      <v-messages :messages="errorMessage" color="red" :active="!!errorMessage" class="mt-5 text-body-1 font-weight-bold"/>
    </v-form>
  </v-card>
</template>

<style scoped>
.v-form {
  width: 100%;
}
</style>
