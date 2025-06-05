<script setup lang="ts">
import { useDisplay } from 'vuetify';
import Default from '~/layouts/default.vue';
import { useAuthOwnerStore } from '~/store/authOwner';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const authOwnerStore = useAuthOwnerStore();
const { $ownerApi } = useNuxtApp();
// サイドメニューはモバイルの場合はしまっておく
const { mobile } = useDisplay();
const isDrawerOpen = ref(!mobile.value);

const isCurrentUrlMatching = (url: string) => {
  const currentUrl = useRoute().path;
  return currentUrl.startsWith(url);
};

const menuItems = [
  { title: '予約', icon: 'mdi-note-edit', path: `/owner/reservations/date/${new Date().toISOString().slice(0, 10)}`, activePath: '/owner/reservations' },
  { title: 'スタジオ', icon: 'mdi-home-group', path: '/owner/studios', activePath: '/owner/studios' },
  { title: '会員', icon: 'mdi-account', path: '/owner/members', activePath: '/owner/members' },
  { title: '営業時間・定休日', icon: 'mdi-clock', path: '/owner/business-day', activePath: '/owner/business-day' },
  { title: '臨時休業日', icon: 'mdi-tent', path: '/owner/temporary-closing-days', activePath: '/owner/temporary-closing-days' },
];

const handleLogoutClick = async () => {
  try {
    loadingOverlayStore.setActive();
    await $ownerApi<unknown>('/owner-auth/logout', { method: 'POST' });
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
  <default>
    <v-app-bar color="primary">
      <v-app-bar-nav-icon variant="text" @click.stop="isDrawerOpen = !isDrawerOpen"/>
      <v-app-bar-title>
        スタジオ予約管理
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
      <v-list-item title="オーナー"/>
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

    <v-footer class="text-center d-flex flex-column py-4" color="primary" app>
      <span class="text-white text-caption">© 2025 - スタジオ予約管理システム</span>
    </v-footer>
  </default>
</template>

<style scoped>

</style>
