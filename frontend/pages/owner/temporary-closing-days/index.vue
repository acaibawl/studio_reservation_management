<script setup lang="ts">
import { getWeekDay } from '~/utils/weekDay';
import { padDateAndMonth } from '~/utils/padDateAndMonth';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';

definePageMeta({
  layout: 'owner',
  middleware: ['only-owner'],
});

useHead({
  title: '臨時休業日',
  meta: [
    { name: 'description', content: '臨時休業日を管理します。' },
  ],
});

interface TemporaryClosingDay {
  date: string;
  id: number;
}

const loadingOverlayStore = useLoadingOverlayStore();
const notifyBottomSheetStore = useNotifyBottomSheetStore();
const { $ownerApi } = useNuxtApp();
const selectedNewDate = ref(new Date());

loadingOverlayStore.setActive();
const { data, error } = await useAsyncData<TemporaryClosingDay[]>('/owner/temporary-closing-days', () => $ownerApi('/owner/temporary-closing-days'));
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}
loadingOverlayStore.resetLoading();

const allowedDates = (calendarDate: any) => {
  const calDate: Date = new Date(calendarDate);
  const todayNormalized = new Date();
  todayNormalized.setHours(0, 0, 0, 0);
  if (calDate < todayNormalized) {
    return false;
  }
  // 月は0始まりなので、1加える
  calDate.setMonth(calDate.getMonth() + 1);
  return !data.value?.some(day => `${calDate.getFullYear()}-${padDateAndMonth(calDate.getMonth())}-${padDateAndMonth(calDate.getDate())}` == day.date);
};

const handleAddClick = async () => {
  try {
    loadingOverlayStore.setActive();
    const date = selectedNewDate.value;
    const newTemporaryClosingDay = await $ownerApi<TemporaryClosingDay>('/owner/temporary-closing-days', {
      method: 'POST',
      body: {
        date: `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}`,
      },
    });
    data.value!.push(newTemporaryClosingDay);
    data.value?.sort((a, b) => a.date.localeCompare(b.date));
    notifyBottomSheetStore.setMessage(`${newTemporaryClosingDay.date} を追加しました。`);
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};

const handleDeleteClick = async (date: string) => {
  if (!confirm(`${date} を削除しますか？`)) {
    return;
  }
  try {
    loadingOverlayStore.setActive();
    await $ownerApi(`/owner/temporary-closing-days/${date}`, {
      method: 'DELETE',
    });
    data.value = data.value!.filter(day => day.date !== date);
    notifyBottomSheetStore.setMessage(`${date} を臨時休業日から削除しました。`);
  } catch (e: unknown) {
    console.error(e);
  } finally {
    loadingOverlayStore.resetLoading();
  }
};
</script>

<template>
  <div>
    <h3 class="text-h3">臨時休業日</h3>
    <v-locale-provider locale="ja">
      <v-date-picker
        v-model="selectedNewDate"
        show-adjacent-months
        elevation="24"
        bg-color="blue-lighten-5"
        :allowed-dates="allowedDates"
        hide-header
        class="mt-5"
      />
    </v-locale-provider>

    <v-btn
      class="mt-5"
      base-color="blue-lighten-5"
      :disabled="!allowedDates(selectedNewDate)"
      @click="handleAddClick"
    >
      休業日を追加
    </v-btn>

    <v-table
      fixed-header
      hover
      class="border mt-5"
    >
      <thead>
      <tr>
        <th class="text-left">
          日付
        </th>
        <th class="text-left">
          曜日
        </th>
        <th class="text-left">
          削除
        </th>
      </tr>
      </thead>
      <tbody>
      <tr
        v-for="temporaryClosingDay in data"
        :key="temporaryClosingDay.id"
      >
        <td>{{ temporaryClosingDay.date }}</td>
        <td>{{ getWeekDay(temporaryClosingDay.date) }}</td>
        <td><v-icon icon="mdi-delete" color="red-darken-4" @click="handleDeleteClick(temporaryClosingDay.date)"/></td>
      </tr>
      </tbody>
    </v-table>
  </div>
</template>

<style scoped>
.v-table {
  max-width: 400px;
  max-height: 80vh;
}
</style>
