<script setup lang="ts">
import { useDisplay } from 'vuetify';
import Default from "~/layouts/default.vue";

// サイドメニューはモバイルの場合はしまっておく
const { mobile } = useDisplay();
const isDrawerOpen = ref(!mobile.value);

const isCurrentUrlMatching = (url: string) => {
  const currentUrl = useRoute().path;
  return currentUrl.startsWith(url);
};

const menuItems = [
  { title: '予約', path: `/owner/reservations/date/${new Date().toISOString().slice(0, 10)}`, activePath: '/owner/reservations' },
  { title: 'スタジオ', path: '/owner/studios', activePath: '/owner/studios' },
  { title: '会員', path: '/owner/members', activePath: '/owner/members' },
  { title: '営業時間・定休日', path: '/owner/business-day', activePath: '/owner/business-day' },
  { title: '臨時休業日', path: '/owner/temporary-closing-days', activePath: '/owner/temporary-closing-days' },
];
</script>

<template>
  <default>
    <v-app-bar color="primary">
      <v-app-bar-nav-icon variant="text" @click.stop="isDrawerOpen = !isDrawerOpen"/>
      <v-app-bar-title>
        スタジオ予約管理
      </v-app-bar-title>
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
      />
    </v-navigation-drawer>

    <v-responsive>
      <v-main>
        <v-container class="d-flex align-center justify-center fill-height">
          <nuxt-layout v-if="!$slots.default" />
          <slot />
        </v-container>
      </v-main>
    </v-responsive>
  </default>
</template>

<style scoped>

</style>