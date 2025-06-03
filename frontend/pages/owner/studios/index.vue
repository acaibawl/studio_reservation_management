<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import type { Studio } from '~/types/owner/Studio';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import { FetchError } from 'ofetch';

definePageMeta({
  layout: 'owner',
});

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();

const { data, error } = await useAsyncData<Studio[]>('/owner/studios', () => $ownerApi('/owner/studios'));
if (error.value) {
  console.error(error.value);
  notifyBottomSheetStore.setMessage(error.value.message);
}

const handleDeleteClick = async (studio: Studio) => {
  if (!confirm(`${studio.name} を削除しますか？`)) {
    return;
  }
  try {
    loadingOverlayStore.setActive();
    await $ownerApi(`/owner/studios/${studio.id}`, {
      method: 'DELETE',
    });
    data.value = data.value!.filter(iterateStudio => iterateStudio.id !== studio.id);
    notifyBottomSheetStore.setMessage(`${studio.name} を削除しました。`);
  } catch (e: unknown) {
    if (e instanceof FetchError) {
      if (e.status === 400 || e.status === 401 || e.status === 404 || e.status === 422) {
        notifyBottomSheetStore.setMessage(e.data.message);
      } else {
        notifyBottomSheetStore.setMessage(e.message);
        console.error(e);
      }
    }
  } finally {
    loadingOverlayStore.resetLoading();
  }
};
</script>

<template>
  <div>
    <h3 class="text-h3">スタジオ一覧</h3>
    <v-btn to="/owner/studios/create" class="mt-5" color="primary">スタジオ登録</v-btn>
    <v-table
      fixed-header
      hover
      class="border mt-5"
    >
      <thead>
      <tr>
        <th class="text-left">
          id
        </th>
        <th class="text-left">
          スタジオ名
        </th>
        <th class="text-left">
          開始時間
        </th>
        <th class="text-left">
          編集
        </th>
        <th class="text-left">
          削除
        </th>
      </tr>
      </thead>
      <tbody>
      <tr
        v-for="studio in data"
        :key="studio.id"
      >
        <td>{{ studio.id }}</td>
        <td>{{ studio.name }}</td>
        <td>{{ studio.start_at }} 分</td>
        <td><NuxtLink :to="`/owner/studios/${studio.id}/edit`"><v-icon icon="mdi-clock-edit" color="blue-darken-4"/></NuxtLink></td>
        <td><v-icon icon="mdi-delete" color="red-darken-4" @click="handleDeleteClick(studio)"/></td>
      </tr>
      </tbody>
    </v-table>
  </div>
</template>

<style scoped>

</style>
