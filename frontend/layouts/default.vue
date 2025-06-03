<script setup lang="ts">

</script>

<template>
  <div>
    <v-system-bar color="secondary">
      System Bar
    </v-system-bar>

    <slot />

    <v-footer color="primary" app>
      Footer
    </v-footer>
    <v-bottom-navigation>
      Button Navigation
    </v-bottom-navigation>
  </div>
</template>

<style scoped>
.v-container {
  min-width: 390px;
}
</style>
