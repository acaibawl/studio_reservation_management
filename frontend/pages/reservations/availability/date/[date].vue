<script setup lang="ts">
import {useNotifyBottomSheetStore} from "~/store/notifyBottomSheet";
import {useLoadingOverlayStore} from "~/store/loadingOverlay";

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $memberApi } = useNuxtApp();
const route = useRoute();
const dateString = route.params.date as string;
const date = new Date(dateString);

// パスパラメータdateが正しい日付じゃない場合は404とする
if (!isValidDateString(dateString)) {
  throw createError({ statusCode: 404, statusMessage: 'ページが見つかりません。' });
}


</script>

<template>
  <div>
    <h3>予約空き状況確認</h3>
  </div>
</template>

<style scoped>

</style>
