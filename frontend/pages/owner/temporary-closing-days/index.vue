<script setup lang="ts">
import {getWeekDay} from "~/utils/weekDay";
import {FetchError} from "ofetch";
import {padDateAndMonth} from "~/utils/padDateAndMonth";

interface TemporaryClosingDay {
  date: string;
  id: number;
}

const selectedNewDate = ref(new Date());
const isLoading = ref(false);
const isNotifyMessageVisible = ref(false);
const notifyMessage = ref('');
const { $ownerApi } = useNuxtApp();

const { data, error } = await useAsyncData<TemporaryClosingDay[]>('/owner/temporary-closing-days', () => $ownerApi('/owner/temporary-closing-days'))
if (error.value) {
  console.error(error.value);
}
const allowedDates = (calendarDate: any) => {
  const calDate: Date = new Date(calendarDate);
  if (calDate < new Date()) {
    return false;
  }
  // 月は0始まりなので、1加える
  calDate.setMonth(calDate.getMonth() + 1);
  return !data.value!.some(day => `${calDate.getFullYear()}-${padDateAndMonth(calDate.getMonth())}-${padDateAndMonth(calDate.getDate())}` == day.date)
}

const handleAddClick = async () => {
  try {
    isLoading.value = true;
    const date = selectedNewDate.value;
    const newTemporaryClosingDay = await $ownerApi<TemporaryClosingDay>('/owner/temporary-closing-days', {
      method: 'POST',
      body: {
        date: `${date.getFullYear()}-${(date.getMonth() + 1).toString().padStart(2, '0')}-${date.getDate().toString().padStart(2, '0')}`,
      },
    });
    data.value!.push(newTemporaryClosingDay);
    data.value?.sort((a, b) => a.date.localeCompare(b.date));
    notifyMessage.value = `${newTemporaryClosingDay.date} を追加しました。`
    isNotifyMessageVisible.value = true;
  }catch (e: unknown) {
    if (e instanceof FetchError) {
      notifyMessage.value = e.message
      isNotifyMessageVisible.value = true;
    } else {
      console.error(e);
    }
  } finally {
    isLoading.value = false;
  }
}

const handleDeleteClick = async (date: string) => {
  if (!confirm(`${date} を削除しますか？`)) {
    return;
  }
  try {
    isLoading.value = true;
    await $ownerApi(`/owner/temporary-closing-days/${date}`, {
      method: 'DELETE',
    })
    data.value = data.value!.filter(day => day.date !== date);
    notifyMessage.value = `${date} を臨時休業日から削除しました。`;
    isNotifyMessageVisible.value = true;
  } catch (e: unknown) {
    console.error(e);
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div>
    <h3 class="text-h3">臨時休業日</h3>
    <v-locale-provider locale="ja">
      <v-date-picker
        show-adjacent-months
        elevation="24"
        bg-color="blue-lighten-5"
        :allowed-dates="allowedDates"
        hide-header
        v-model="selectedNewDate"
        class="mt-5"
      ></v-date-picker>
    </v-locale-provider>

    <v-btn
      class="mt-5"
      @click="handleAddClick"
      base-color="blue-lighten-5"
      :disabled="!allowedDates(selectedNewDate)"
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
        <td><v-icon icon="mdi-delete" @click="handleDeleteClick(temporaryClosingDay.date)" color="red-darken-4"></v-icon></td>
      </tr>
      </tbody>
    </v-table>
    <v-bottom-sheet
      v-model="isNotifyMessageVisible"
    >
      <v-card
        :text="notifyMessage"
      ></v-card>
    </v-bottom-sheet>
    <v-overlay
      :model-value="isLoading"
      class="align-center justify-center"
      persistent
    >
      <v-progress-circular
        color="primary"
        size="64"
        indeterminate
      ></v-progress-circular>
    </v-overlay>
  </div>
</template>

<style scoped>
.v-table {
  max-width: 400px;
  max-height: 80vh;
}
</style>
