<script setup lang="ts">
import QuotaTable from '~/components/reservation/QuotaTable.vue';
import { weekDays } from '~/utils/weekDay';
import SelectDate from '~/components/reservation/SelectDate.vue';

definePageMeta({
  layout: 'member',
  middleware: ['only-member'],
});

useHead({
  title: '予約空き状況確認',
  meta: [
    { name: 'description', content: 'スタジオの予約空き状況を確認します。' },
  ],
});

const route = useRoute();
const dateString = route.params.date as string;
// パスパラメータdateが正しい日付じゃない場合は404とする
if (!isValidDateString(dateString)) {
  throw createError({ statusCode: 404, statusMessage: 'ページが見つかりません。' });
}
const date = new Date(dateString);
</script>

<template>
  <div>
    <v-row class="d-flex align-center justify-center fill-height">
      <h3 class="text-h3">予約空き状況確認</h3>
      <h5 class="text-h5">{{ date.getFullYear() }}年{{ date.getMonth() + 1 }}月{{ date.getDate() }}日({{ weekDays[date.getDay()] }})</h5>
      <SelectDate :date-string="dateString" :is-owner="false" />
    </v-row>

    <QuotaTable :date-string="dateString" :is-owner="false"/>
  </div>
</template>

<style scoped>

</style>
