<script setup lang="ts">
import {getWeekDay} from "~/utils/weekDay";

interface TemporaryClosingDay {
  date: string;
  id: number;
}

interface TemporaryClosingDays {
  temporary_closing_days: TemporaryClosingDay[];
}

const isLoading = ref(false);
const isDeleteMessageVisible = ref(false);
const deletedMessage = ref('');
const { $ownerApi } = useNuxtApp();
const { data, error } = await useAsyncData<TemporaryClosingDays>('/owner/temporary-closing-days', () => $ownerApi('/owner/temporary-closing-days'))
if (error.value) {
  console.error(error.value);
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
    data.value!.temporary_closing_days = data.value!.temporary_closing_days.filter(day => day.date !== date);
    deletedMessage.value = `${date} を臨時休業日から削除しました。`;
    isDeleteMessageVisible.value = true;
  } catch (e: unknown) {
    console.error(e);
    reloadNuxtApp();
  } finally {
    isLoading.value = false;
  }
}
</script>

<template>
  <div>
    <h3 class="text-h3">臨時休業日管理</h3>

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
        v-for="temporaryClosingDay in data?.temporary_closing_days"
        :key="temporaryClosingDay.id"
      >
        <td>{{ temporaryClosingDay.date }}</td>
        <td>{{ getWeekDay(temporaryClosingDay.date) }}</td>
        <td><v-icon icon="mdi-delete" @click="handleDeleteClick(temporaryClosingDay.date)" color="red-darken-4"></v-icon></td>
      </tr>
      </tbody>
    </v-table>
    <v-bottom-sheet v-model="isDeleteMessageVisible" activator="none">
      <v-card
        :text="deletedMessage"
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
