<script setup lang="ts">
import { VPagination } from 'vuetify/components/VPagination'
const props = defineProps<{
  currentPage: number,
  length: number,
  totalVisible: number,
  to: Function
}>()
const range = (start: number, stop: number, step: number = 1) =>
  Array(Math.ceil((stop - start) / step)).fill(start).map((x, y) => x + y * step)
let totalVisible = props.totalVisible - 2;
if (totalVisible < 0) {
  totalVisible = 3
}
let total = Math.floor(totalVisible/2)
let start = props.currentPage - total
if (start <= 0) {
  total = 0
  start = 1
}
total = totalVisible - total
let end = props.currentPage + total
if (end > props.length) {
  end = props.length
  start -= total
  if (start <= 0) {
    start = 1
  }
}
let pages = range(start, end)
if (pages[0] != 1) {
  pages.unshift(1)
  if (pages[1] != 2) {
    pages.splice(1, 0, 0);
  }
}
if (pages[pages.length - 1] != props.length) {
  pages.push(props.length)
  if (pages[pages.length - 2] != props.length-1) {
    pages.splice(pages.length-1, 0, 0);
  }
}
</script>
<template>
  <!-- need this to trigger dynamic import due to treeshaking, or import VPagination.sass -->
  <v-pagination v-show="false" />
  <nav class="v-pagination" role="navigation" aria-label="Pagination Navigation">
    <ul class="v-pagination__list">
      <li class="v-pagination__prev">
        <v-btn
          :disabled="currentPage == 1"
          icon
          variant="text"
          exact
          :to="currentPage != 1 ? to(currentPage - 1) : {}"
        >
          <v-icon icon="mdi-chevron-left"/>
        </v-btn>
      </li>
      <li
        v-for="page in pages"
        :key="page"
        class="v-pagination__item"
      >
        <v-btn
          :disabled="page == 0"
          icon
          :variant="page === currentPage ? 'tonal' : 'plain'"
          exact
          :to="page > 0 ? to(page) : {}"
          active-color="primary"
        >
          {{ page == 0 ? '...' : page }}
        </v-btn>
      </li>
      <li class="v-pagination__next">
        <v-btn
          :disabled="currentPage == length"
          icon
          variant="text"
          exact
          :to="currentPage != length ? to(currentPage + 1) : {}"
        >
          <v-icon icon="mdi-chevron-right"/>
        </v-btn>
      </li>
    </ul>
  </nav>
</template>
<style>
/* @import 'vuetify/lib/components/VPagination/VPagination.sass'; */
</style>
