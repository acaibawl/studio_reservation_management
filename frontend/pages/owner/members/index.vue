<script setup lang="ts">
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import { useLoadingOverlayStore } from '~/store/loadingOverlay';
import * as yup from 'yup';
import { useForm } from 'vee-validate';

interface Members {
  members: {
    id: number;
    name: string;
    email: string;
    has_reservation: boolean;
  }[];
  page_size: number;
  current_page: number;
}

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const loadingOverlayStore = useLoadingOverlayStore();
const { $ownerApi } = useNuxtApp();
const route = useRoute();
const router = useRouter();
const currentPage = computed(() => route.query.page ? Number(route.query.page) : 1);

const { data, error, refresh } = await useAsyncData<Members>(`/owner/members`, () => $ownerApi('/owner/members', {
  query: {
    page: currentPage.value,
    name: route.query.name,
  },
  immediate: false,
}));

const pageSize = computed(() => data.value?.page_size || 0);

// クエリパラメータの変更を監視して一覧をリフレッシュする
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
  { immediate: true },
);

const generatePaginationLink = (page: number) => {
  return {
    path: '/owner/members',
    query: {
      page: page,
      name: route.query.name,
    },
  };
};

// 名前検索フィールド用のバリデーション
const schema = yup.object({
  name: yup.string().nullable().max(50).label('名前'),
});
const { defineField, setErrors, handleSubmit } = useForm({
  validationSchema: schema,
  initialValues: {
    name: route.query.name,
  },
});

const onSubmit = handleSubmit(async (values) => {
  try {
    const urlNameQuery = values.name ? `&name=${values.name}` : '';
    await router.push(`/owner/members?page=1${urlNameQuery}`);
  } catch (e: unknown) {
    notifyBottomSheetStore.handleFetchError(e, setErrors);
  }
});
const [name, nameProps] = defineField('name', yupFieldImmediateVuetifyConfig);
</script>

<template>
  <div>
    <h3 class="text-h3">会員</h3>

    <v-form class="mt-5 d-inline-flex align-center" @submit="onSubmit">
      <v-text-field
        v-model="name"
        v-bind="nameProps"
        label="名前"
        type="text"
        prepend-inner-icon="mdi-account-search"
        min-width="20rem"
        clearable
      />
      <v-btn
        type="submit"
        class="ml-5"
        color="primary">
        検索
      </v-btn>
    </v-form>
    <v-row>
      <p class="text-caption">{{ route.query.name === undefined ? '' : `"${route.query.name}" で検索`}}</p>
    </v-row>
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
        <td class="font-weight-bold text-primary">{{ member.has_reservation ? '有' : '' }}</td>
      </tr>
      </tbody>
    </v-table>
    <Pagination :current-page="currentPage" :length="pageSize" :total-visible="6" :to="generatePaginationLink" />
  </div>
</template>

<style scoped>

</style>
