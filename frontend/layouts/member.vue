<script setup lang="ts">
import { useDisplay } from 'vuetify';
import Default from '~/layouts/default.vue';
import { useAuthMemberStore } from '~/store/authMember';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const authMemberStore = useAuthMemberStore();
const { $memberApi } = useNuxtApp();
// サイドメニューはモバイルの場合はしまっておく
const { mobile } = useDisplay();
const isDrawerOpen = ref(!mobile.value);

const isCurrentUrlMatching = (url: string) => {
  const currentUrl = useRoute().path;
  return currentUrl.startsWith(url);
};

const menuItems = [
  { title: '予約空き状況', icon: 'mdi-note-edit', path: `/reservations/availability/date/${new Date().toISOString().slice(0, 10)}`, activePath: '/reservations' },
  { title: '予約済み', icon: 'mdi-guitar-acoustic', path: '/reserved', activePath: '/reserved' },
  { title: '会員情報', icon: 'mdi-account', path: '/member/me', activePath: '/member/me' },
];

const handleLogoutClick = async () => {
  try {
    loadingOverlayStore.setActive();
    await $memberApi<unknown>('/member-auth/logout', { method: 'POST' });
    authMemberStore.logout();
    navigateTo('/member/login');
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};
</script>

<template>
  <default>
    <v-app-bar color="primary">
      <v-app-bar-nav-icon variant="text" @click.stop="isDrawerOpen = !isDrawerOpen"/>
      <v-app-bar-title>
        スタジオ予約
      </v-app-bar-title>
      <v-menu>
        <template #activator="{ props }">
          <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"/>
        </template>

        <v-list>
          <v-list-item append-icon="mdi-logout" @click="handleLogoutClick">
            <v-list-item-title>ログアウト</v-list-item-title>
          </v-list-item>
        </v-list>
      </v-menu>
    </v-app-bar>

    <v-navigation-drawer
      v-model="isDrawerOpen"
      :location="$vuetify.display.mobile ? 'bottom' : undefined"
      :permanent="!mobile"
    >
      <v-list-item title="会員"/>
      <v-divider/>
      <v-list-item
        v-for="item in menuItems"
        :key="item.path"
        :title="item.title"
        :to="item.path"
        :active="isCurrentUrlMatching(item.activePath)"
      >
        <template #prepend>
          <v-icon :icon="item.icon"/>
        </template>
      </v-list-item>
    </v-navigation-drawer>

    <v-responsive>
      <v-main>
        <v-container class="d-flex align-center justify-center fill-height" max-width="1200px">
          <nuxt-layout v-if="!$slots.default" />
          <slot />
        </v-container>
      </v-main>
    </v-responsive>

    <v-bottom-navigation>
      Button Navigation
    </v-bottom-navigation>
    <v-footer color="primary" app>
      Footer
    </v-footer>
  </default>
</template>

<style scoped>

</style>
