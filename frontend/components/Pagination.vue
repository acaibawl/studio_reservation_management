<script setup lang="ts">
const props = defineProps<{
  currentPage: number;
  length: number;
  totalVisible: number;
  to: (page: number) => { path: string; query: Record<string, string> };
}>();

const totalVisible = computed(() => props.totalVisible - 2 < 0 ? 3 : props.totalVisible - 2);
const total = ref(0);
const start = ref(0);
const end = ref(0);
let pages = reactive([] as number[]);

const isSinglePageRange = (start: number, end: number): boolean => {
  return start === 1 && end === 1;
};
const range = (start: number, stop: number, step: number = 1) =>
  Array(Math.ceil((stop - start) / step)).fill(start).map((x, y) => x + y * step);

watch(
  props,
  () => {
    total.value = Math.floor(totalVisible.value / 2);
    start.value = props.currentPage - total.value;
    if (start.value <= 0) {
      total.value = 0;
      start.value = 1;
    }
    total.value = totalVisible.value - total.value;
    end.value = props.currentPage + total.value;
    if (end.value > props.length) {
      end.value = props.length;
      start.value -= total.value;
      if (start.value <= 0) {
        start.value = 1;
      }
    }

    // もし先頭ページと末尾ページが同じ1なら配列は[1]だけにする
    if (isSinglePageRange(start.value, end.value)) {
      pages = [1];
    } else {
      pages = range(start.value, end.value);
      // ページの先頭が1じゃない場合は1を先頭に追加し、さらに2番目が2じゃなければ0（...にレンダリングする）を追加
      if (pages[0] != 1) {
        pages.unshift(1);
        if (pages[1] != 2) {
          pages.splice(1, 0, 0);
        }
      }
      // 末尾ページが全ページ数と一致しない場合は全ページ数を末尾に追加し、さらに末尾から2番目が全ページ数-1でないなら0（...にレンダリングする）を追加
      if (pages[pages.length - 1] != props.length) {
        pages.push(props.length);
        if (pages[pages.length - 2] != props.length - 1) {
          pages.splice(pages.length - 1, 0, 0);
        }
      }
    }
  },
  { immediate: true },
);
</script>

<template>
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
