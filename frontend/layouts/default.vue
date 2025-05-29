<script setup lang="ts">
import { useDisplay } from 'vuetify';

// サイドメニューはモバイルの場合はしまっておく
const { mobile } = useDisplay();
const isDrawerOpen = ref(!mobile.value);
</script>

<template>
  <div>
    <v-system-bar color="secondary">
      System Bar
    </v-system-bar>

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
      <v-list-item title="オーナー"></v-list-item>
      <v-divider></v-divider>
      <v-list-item title="予約" to="/owner/studio/business-day"></v-list-item>
      <v-list-item title="部屋" to="/owner/studio/business-day"></v-list-item>
      <v-list-item title="ユーザー" to="/owner/studio/business-day"></v-list-item>
      <v-list-item title="営業時間・定休日" to="/owner/studio/business-day"></v-list-item>
      <v-list-item title="臨時休業日" to="/owner/temporary-closing-days"></v-list-item>
    </v-navigation-drawer>

    <v-main>
      <v-container class="d-flex align-center justify-center fill-height">
        <slot />
      </v-container>
    </v-main>

    <v-bottom-navigation>
      Button Navigation
    </v-bottom-navigation>

    <v-footer color="primary" app>
      Footer
    </v-footer>
  </div>
</template>

<style scoped>
.v-container {
  min-width: 390px;
}
</style>
