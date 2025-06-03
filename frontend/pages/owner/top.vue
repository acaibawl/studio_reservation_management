<script setup lang="ts">
import { useAuthOwnerStore } from '~/store/authOwner';
import {useLoadingOverlayStore} from "~/store/loadingOverlay";
import {useNotifyBottomSheetStore} from "~/store/notifyBottomSheet";
import type {BusinessDay} from "~/types/owner/BusinessDay";

definePageMeta({
  layout: 'owner',
  middleware: ['only-owner'],
});

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const authOwnerStore = useAuthOwnerStore();
const { $ownerApi } = useNuxtApp();

const handleLogoutClick = async () => {
  try {
    loadingOverlayStore.setActive();
    const response = await $ownerApi<any>('/owner-auth/logout', {method: 'POST'});
    authOwnerStore.logout();
    navigateTo('/owner/login');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e);
  } finally {
    loadingOverlayStore.resetLoading();
  }

};
</script>

<template>
  <div>
    <h1>管理画面トップ</h1>

    <p>ようこそ、オーナーさん</p>
    <v-btn type="button" @click="handleLogoutClick">ログアウト</v-btn>
    <ul>
      <li>
        <NuxtLink to="/owner/1">管理者1</NuxtLink>
      </li>
      <li>
        <NuxtLink to="/owner/2">管理者2</NuxtLink>
      </li>
      <li>
        <NuxtLink to="/owner/3">管理者3</NuxtLink>
      </li>
    </ul>
  </div>
</template>

<style scoped>

</style>
