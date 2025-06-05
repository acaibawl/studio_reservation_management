<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { ErrorMessage, useForm } from 'vee-validate';
import { yupFieldLazyVuetifyConfig } from '~/utils/yupFieldVuetifyConfig';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

useHead({
  title: 'メールアドレス修正',
  meta: [
    { name: 'description', content: '会員のメールアドレスを修正します。' },
  ],
});

const enum UpdateEmailStep {
  EnterEmail = 1,
  EnterVerifiedCode = 2,
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();
const currentFormStep = ref(UpdateEmailStep.EnterEmail);

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  code: yup.string().required().length(6).label('認証コード'),
});
const { defineField, setErrors, errors, isFieldValid, validateField } = useForm({
  validationSchema: schema,
});
const [email, emailProps] = defineField('email', yupFieldLazyVuetifyConfig);
const [code] = defineField<string>('code', yupFieldLazyVuetifyConfig);

const onSendVerifiedCodeSubmit = async () => {
  try {
    await validateField('email');
    if (!isFieldValid('email')) {
      return;
    }
    loadingOverlayStore.setActive();
    await $memberApi<unknown>('/member-auth/change-email-verified-code/send', {
      method: 'POST',
      body: {
        email: email.value,
      },
    });

    currentFormStep.value = UpdateEmailStep.EnterVerifiedCode;
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

const onVerifyVerifiedCodeSubmit = async () => {
  try {
    await validateField('email');
    await validateField('code');
    if (!isFieldValid('email') || !isFieldValid('code')) {
      return;
    }
    loadingOverlayStore.setActive();
    await $memberApi<unknown>('/member-auth/email', {
      method: 'PATCH',
      body: {
        email: email.value,
        code: code.value,
      },
    });

    navigateTo('/member/me');
    notifyBottomSheetStore.setMessage('メールアドレスを更新しました。');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};
</script>

<template>
  <v-card class="mx-auto px-6 py-8 mt-10 align-center justify-center fill-height" max-width="640px">
    <v-card-item>
      <v-card-title>
        <h3 class="text-h3">メールアドレス修正</h3>
      </v-card-title>
    </v-card-item>

    <!-- メールアドレス検証コード送信ステップ -->
    <v-card-text v-if="currentFormStep === UpdateEmailStep.EnterEmail">
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
        </v-row>
        <v-row>
          <v-col>
            <v-btn to="/member/me">戻る</v-btn>
            <v-btn color="primary" class="ml-5" type="submit">認証コードを送る</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>

    <!-- メールアドレス検証コード入力ステップ -->
    <v-card-text v-if="currentFormStep === UpdateEmailStep.EnterVerifiedCode">
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
            <v-btn @click="currentFormStep = UpdateEmailStep.EnterEmail">戻る</v-btn>
            <v-btn color="primary" class="ml-5" type="submit">認証コードを検証</v-btn>
          </v-col>
        </v-row>
      </v-form>
    </v-card-text>
  </v-card>
</template>

<style scoped>

</style>
