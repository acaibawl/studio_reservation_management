<script setup lang="ts">

</script>

<template>
  <div>
    <v-system-bar color="secondary">
      System Bar
    </v-system-bar>

    <slot />

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
