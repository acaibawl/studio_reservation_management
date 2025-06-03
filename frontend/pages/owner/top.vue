<script setup lang="ts">
import { useAuthOwnerStore } from '~/store/authOwner';

definePageMeta({
  layout: 'owner',
  middleware: ['only-owner'],
});

const authOwnerStore = useAuthOwnerStore();
const handleLogoutClick = () => {
  authOwnerStore.logout();
  navigateTo('/owner/login');
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
