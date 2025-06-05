<script setup lang="ts">
import NotifyBottomSheet from '~/components/NotifyBottomSheet.vue';
import LoadingOverlay from '~/components/LoadingOverlay.vue';
import { useAuthMemberStore } from '~/store/authMember';
import { useAuthOwnerStore } from '~/store/authOwner';
import type { LayoutKey } from '#build/types/layouts';

const authMemberStore = useAuthMemberStore();
const authOwnerStore = useAuthOwnerStore();
const error = useError();

const layout = ref<false | LayoutKey>('default');
let topPageUrl = '/';

if (authMemberStore.isLogin) {
  layout.value = 'member';
}
if (authOwnerStore.isLogin) {
  layout.value = 'owner';
  topPageUrl = '/owner/reservations/date/' + new Date().toISOString().slice(0, 10);
}

</script>

<template>
  <v-app>
    <NuxtLayout :name="layout">

      <v-sheet class="mt-10 mx-auto pa-5" max-width="600">
        <p class="mt-5"><v-icon icon="mdi-alert-circle" class="mr-3" color="red-darken-4"/>ステータスコード：{{ error?.statusCode }}<v-icon icon="mdi-alert-circle" class="ml-3" color="red-darken-4"/></p>
        <p class="mt-5">{{ error?.message }}</p>
        <p  class="mt-5"><NuxtLink class="text-decoration-none text-primary" :to="topPageUrl">トップページへ戻る</NuxtLink></p>
      </v-sheet>

      <loading-overlay />
      <notify-bottom-sheet />
    </NuxtLayout>
  </v-app>
</template>
