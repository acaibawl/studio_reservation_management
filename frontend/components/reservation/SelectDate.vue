<script setup lang="ts">
import * as yup from "yup";
import {useForm} from "vee-validate";
import {yupFieldImmediateVuetifyConfig} from "~/utils/yupFieldVuetifyConfig";

const props = defineProps<{
  dateString: string;
  isOwner: boolean;
}>();

const schema = yup.object({
  selectDate: yup.string().required().label('日付'),
});
const { defineField } = useForm({
  validationSchema: schema,
});
const [selectDate, selectDateProps] = defineField('selectDate', yupFieldImmediateVuetifyConfig);

const showDateDialog = ref(false);
const linkUrlPrefix = props.isOwner ? '/owner/reservations/date/' : '/reservations/availability/date/'
</script>

<template>
  <div>
    <v-btn
      color="primary"
      size="x-small"
      text="日付変更"
      class="ml-5"
      @click="showDateDialog = true"
    />
    <v-dialog v-model="showDateDialog" max-width="500">
      <v-card title="日付変更">
        <v-text-field
          v-model="selectDate"
          v-bind="selectDateProps"
          label="日付"
          type="date"
          prepend-inner-icon="mdi-calendar-edit"
        />
        <v-btn
          color="primary"
          :to="`${linkUrlPrefix}${selectDate}`"
          :disabled="!selectDate || selectDate === dateString"
        >
          確定
        </v-btn>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped>

</style>
