<script setup lang="ts">
import {useNotifyBottomSheetStore} from "~/store/notifyBottomSheet";
import {useLoadingOverlayStore} from "~/store/loadingOverlay";

interface Members {
  members: {
    id: number;
    name: string;
    email: string;
    has_reservation: boolean;
  }[]
  page_size: number;
  current_page: number;
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const currentPage = computed(() => route.query.page ? Number(route.query.page) : 1);

let { data, error, refresh } = await useAsyncData<Members>(`/owner/members`, () => $ownerApi('/owner/members', {
  query: {
    page: currentPage.value,
  },
  immediate: false,
}));

// クエリパラメータの変更を監視して表をリフレッシュする
watch(
  () => route.query,
  async () => {
    loadingOverlayStore.setActive();
    await refresh();
    if (error.value) {
      console.error(error.value);
      notifyBottomSheetStore.setMessage(error.value.message);
    }
    loadingOverlayStore.resetLoading();
  },
  { immediate: true }
)

const generatePaginationLink = (page: number) => {
  return {
    path: '/owner/members',
    query: {
      page: page,
    },
  }
}
</script>

<template>
  <div>
    <h3 class="text-h3">会員</h3>

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
          名前
        </th>
        <th class="text-left">
          メール
        </th>
        <th class="text-left">
          予約
        </th>
      </tr>
      </thead>
      <tbody>
      <tr
        v-for="member in data?.members"
        :key="member.id"
      >
        <td>{{ member.id }}</td>
        <td>{{ member.name }}</td>
        <td>{{ member.email }}</td>
        <td>{{ member.has_reservation ? '有' : '' }}</td>
      </tr>
      </tbody>
    </v-table>
    <Pagination :currentPage="currentPage" :length="data!.page_size" :total-visible="6" :to="generatePaginationLink" ></Pagination>
  </div>
</template>

<style scoped>

</style>