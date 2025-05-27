<script setup>
import { useForm } from 'vee-validate';
import * as yup from 'yup';
import { useAuthOwnerStore } from '~/store/authOwner';

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  password: yup.string().required().min(8).max(32).label('パスワード'),
});

const { defineField, handleSubmit, resetForm } = useForm({
  validationSchema: schema,
});

const vuetifyConfig = state => ({
  props: {
    'error-messages': state.errors,
  },
});

const [email, emailProps] = defineField('email', vuetifyConfig);
const [password, passwordProps] = defineField('password', vuetifyConfig);

const onSubmit = handleSubmit(async (values) => {
  const { $api } = useNuxtApp();
  const response = await $api('/owner-auth/login', {
    method: 'POST',
    body: values,
  });

  const { loginAsOwner } = useAuthOwnerStore();
  loginAsOwner(response.owner_access_token);
  const route = useRoute();
  const redirectedFrom = route.query.redirectedFrom;
  const to = redirectedFrom || '/owner/top';
  navigateTo(to);
});
</script>

<template>
  <v-form class="px-4" @submit="onSubmit">
    <v-text-field
      v-model="email"
      v-bind="emailProps"
      label="メールアドレス"
      type="email"
    />
    <v-text-field
      v-model="password"
      v-bind="passwordProps"
      label="パスワード"
      type="password"
    />

    <v-btn color="primary" type="submit"> Submit </v-btn>
    <v-btn color="outline" class="ml-4" @click="resetForm()"> Reset </v-btn>
  </v-form>
</template>
