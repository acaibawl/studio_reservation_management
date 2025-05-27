<script setup lang="ts">
import { useDisplay } from 'vuetify';

// サイドメニューはモバイルの場合はしまっておく
const { mobile } = useDisplay();
const drawerPermanent = !mobile.value;
const drawer = ref(!mobile.value);
</script>

<template>
  <div>
    <v-system-bar color="secondary">
      System Bar
    </v-system-bar>

    <v-app-bar color="primary">
      <v-app-bar-nav-icon variant="text" @click.stop="drawer = !drawer"/>
      <v-app-bar-title>
        Application Bar
      </v-app-bar-title>
    </v-app-bar>

    <v-navigation-drawer
      v-model="drawer"
      :location="$vuetify.display.mobile ? 'bottom' : undefined"
      :permanent="drawerPermanent"
    >
      Navigation Drawer
    </v-navigation-drawer>

    <v-main>
      <v-container>
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

</style>
