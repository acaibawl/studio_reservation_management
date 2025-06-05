<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import type { Me } from '~/types/member/Me';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();

loadingOverlayStore.setActive();
const { data, error } = await useAsyncData<Me>('/member-auth/me', () => $memberApi('/member-auth/me'), {
  getCachedData: () => undefined,
});
loadingOverlayStore.resetLoading();
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
</script>

<template>
  <div>
    <v-row>
      <v-col>
        <h3 class="text-h3">会員情報</h3>
      </v-col>
    </v-row>
    <v-card
      color="primary"
      variant="outlined"
      class="mx-auto mt-5"
    >
      <v-card-item class="text-black">
        <div>
          <div class="text-overline mb-1">
            メールアドレス
          </div>
          <div class="text-body-1 mb-1">
            {{ data?.email }}
          </div>
        </div>
      </v-card-item>

      <v-card-actions>
        <v-btn to="/member/me/email-update" color="secondary" variant="text">
          修正する
        </v-btn>
      </v-card-actions>
    </v-card>
    <v-card
      color="primary"
      variant="outlined"
      class="mx-auto mt-5"
    >
      <v-card-item class="text-black">
        <div>
          <div class="text-overline mb-1">
            名前
          </div>
          <div class="text-body-1 mb-1">
            {{ data?.name }}
          </div>
        </div>
      </v-card-item>
      <v-card-item class="text-black">
        <div>
          <div class="text-overline mb-1">
            住所
          </div>
          <div class="text-body-1 mb-1">
            {{ data?.address }}
          </div>
        </div>
      </v-card-item>
      <v-card-item class="text-black">
        <div>
          <div class="text-overline mb-1">
            電話番号
          </div>
          <div class="text-body-1 mb-1">
            {{ data?.tel }}
          </div>
        </div>
      </v-card-item>

      <v-card-actions>
        <v-btn to="/member/me/edit" color="secondary" variant="text">
          修正する
        </v-btn>
      </v-card-actions>
    </v-card>
  </div>
</template>

<style scoped>

</style>
