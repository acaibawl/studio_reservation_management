<script setup lang="ts">

import { weekDays } from '~/utils/weekDay';
import { formatTimeToHi } from '~/utils/formatTimeToHi';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import type { BusinessDay } from '~/types/owner/BusinessDay';

const notifyBottomSheetStore = useNotifyBottomSheetStore();
const { $ownerApi } = useNuxtApp();

const { data, error } = await useAsyncData<BusinessDay>('/owner/business-day', () => $ownerApi('/owner/business-day'));
if (error.value) {
  notifyBottomSheetStore.setMessage(error.value.message);
  console.error(error.value);
}
</script>

<template>
  <div>
    <h3 class="text-h3">営業時間・定休日</h3>
    <h5 class="text-h5 mt-5">定休日</h5>
    <ul class="d-flex flex-row mt-5">
      <li
        v-for="regularHoliday in data?.regular_holidays"
        :key="regularHoliday.code"
        class="ml-5"
      >
        {{ weekDays[regularHoliday.code] }}
      </li>
    </ul>
    <h5 class="text-h5 mt-5">営業時間</h5>
    <div class="mt-5">
      <span class="ml-5">
        {{ formatTimeToHi(data?.business_time.open_time) }}
      </span>
      <span>
        〜
      </span>
      <span>
        {{ formatTimeToHi(data?.business_time.close_time) }}
      </span>
    </div>
    <v-btn
      class="mt-5"
      color="primary"
      to="/owner/business-day/edit"
    >
      修正
    </v-btn>
  </div>
</template>

<style scoped>
ul {
  list-style: none;
}
</style>
