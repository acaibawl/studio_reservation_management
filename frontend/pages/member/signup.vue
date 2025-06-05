<script setup lang="ts">
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import * as yup from 'yup';
import { ErrorMessage, useForm } from 'vee-validate';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

useHead({
  title: '会員登録',
  meta: [
    { name: 'description', content: 'スタジオ予約管理システムの会員登録ページです。' },
  ],
});
const enum SignUpStep {
  EnterEmail = 1,
  EnterVerifiedCode = 2,
  EnterMemberInformation = 3,
  Completed = 4,
}

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();

const { $api } = useNuxtApp();
const currentFormStep = ref(SignUpStep.EnterEmail);
const isPasswordVisible = ref(false);
const isPasswordConfirmationVisible = ref(false);

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  code: yup.string().required().length(6).label('認証コード'),
  name: yup.string().required().max(50).label('名前'),
  address: yup.string().required().max(128).label('住所'),
  tel: yup.string().required().min(10).max(11).matches(/^\d+$/).label('電話番号'),
  password: yup.string().required().min(8).max(32).matches(/^[a-zA-Z0-9_-]+$/, 'パスワードは半角英数字または-と_の記号が使えます').label('パスワード'),
  password_confirmation: yup.string().oneOf([yup.ref('password')], 'パスワードが一致しません。').required().label('パスワード確認'),
});
const { defineField, handleSubmit, setErrors, errors, isFieldValid, validateField } = useForm({
  validationSchema: schema,
});
const [email, emailProps] = defineField('email', yupFieldLazyVuetifyConfig);
const [code] = defineField<string>('code', yupFieldLazyVuetifyConfig);
const [name, nameProps] = defineField('name', yupFieldLazyVuetifyConfig);
const [address, addressProps] = defineField('address', yupFieldLazyVuetifyConfig);
const [tel, telProps] = defineField('tel', yupFieldLazyVuetifyConfig);
const [password, passwordProps] = defineField('password', yupFieldLazyVuetifyConfig);
const [passwordConfirmation, passwordConfirmationProps] = defineField('password_confirmation', yupFieldLazyVuetifyConfig);

const onSendVerifiedCodeSubmit = async () => {
  try {
    await validateField('email');
    if (!isFieldValid('email')) {
      return;
    }
    loadingOverlayStore.setActive();
    await $api<unknown>('/member-auth/sign-up-email-verified-code/send', {
      method: 'POST',
      body: {
        email: email.value,
      },
    });

    currentFormStep.value = SignUpStep.EnterVerifiedCode;
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};

const onVerifyVerifiedCodeSubmit = async () => {
  try {
    await validateField('email');
    await validateField('code');
    if (!isFieldValid('email') || !isFieldValid('code')) {
      return;
    }
    loadingOverlayStore.setActive();
    await $api<unknown>('/member-auth/sign-up-email-verified-code/verify', {
      method: 'POST',
      body: {
        email: email.value,
        code: code.value,
      },
    });

    currentFormStep.value = SignUpStep.EnterMemberInformation;
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};

// 認証コードの入力で、入力しきったら自動的に送信する
watch(
  code,
  () => {
    if (code.value && code.value.length === 6) {
      onVerifyVerifiedCodeSubmit();
    }
  },
);

const onMemberInformationSubmit = handleSubmit(async (values) => {
  try {
    loadingOverlayStore.setActive();
    await $api<unknown>('/member-auth/member', {
      method: 'POST',
      body: values,
    });

    currentFormStep.value = SignUpStep.Completed;
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
          <h3 class="text-h3">スタジオ予約システム</h3>
          <h4 class="text-h4">会員登録</h4>
          <p>ステップ {{ `${currentFormStep}/${SignUpStep.Completed}` }}</p>
        </v-card-title>
      </v-card-item>

      <!-- メールアドレス検証コード送信ステップ -->
      <v-card-text v-if="currentFormStep === SignUpStep.EnterEmail">
        <v-form @submit.prevent="onSendVerifiedCodeSubmit">
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
          <v-btn color="primary" type="submit">認証コードを送る</v-btn>
        </v-row>
        </v-form>
      </v-card-text>

      <!-- メールアドレス検証コード入力ステップ -->
      <v-card-text v-if="currentFormStep === SignUpStep.EnterVerifiedCode">
        <v-form @submit.prevent="onVerifyVerifiedCodeSubmit">
          <v-row class="mt-5">
            <v-col cols="12">
              <p>メールアドレス： {{ email }}</p>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-sheet>
                <v-otp-input
                  v-model="code"
                  autofocus
                  variant="filled"
                  :error="!!errors.code"
                />
                <ErrorMessage as="div" name="code" v-slot="{ message }">
                  <v-messages :messages="message" :active="true" color="red" class="mt-5 text-body-1 font-weight-bold" />
                </ErrorMessage>
              </v-sheet>
            </v-col>
          </v-row>
          <v-row>
            <v-col>
              <v-btn color="primary" type="submit">認証コードを検証</v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <!-- 会員情報入力ステップ -->
      <v-card-text v-if="currentFormStep === SignUpStep.EnterMemberInformation">
        <v-form @submit.prevent="onMemberInformationSubmit">
          <v-row class="mt-5">
            <v-col cols="12">
              <p>メールアドレス： {{ email }}</p>
            </v-col>
          </v-row>
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
              <v-btn color="primary" type="submit">登録</v-btn>
            </v-col>
          </v-row>
        </v-form>
      </v-card-text>

      <!-- 登録完了ステップ -->
      <v-card-text v-if="currentFormStep === SignUpStep.Completed">
        <v-row class="mt-5">
          <v-col cols="12">
            <p>メールアドレス： {{ email }}</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <p>名前： {{ name }}様</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <p>住所：{{ address }}</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <p>電話番号：{{ tel }}</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <p>上記の内容で、登録が完了しました。</p>
            <p>ログイン画面よりログイン後、スタジオの予約をご利用ください。</p>
          </v-col>
        </v-row>
        <v-row>
          <v-col>
            <v-btn color="primary" to="/member/login">ログイン画面へ</v-btn>
          </v-col>
        </v-row>
      </v-card-text>

    </v-card>
</template>

<style scoped>

</style>
