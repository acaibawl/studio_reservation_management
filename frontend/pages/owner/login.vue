<script setup>
import { useForm } from 'vee-validate';
import * as yup from 'yup';

const schema = yup.object({
  email: yup.string().email().required().label('メールアドレス'),
  password: yup.string().required().min(8).max(32).label('パスワード'),
});

const { defineField, handleSubmit, resetForm } = useForm({
  validationSchema: schema,
});

// Refer to the docs for how to make advanced validation behaviors with dynamic configs
// TODO: Add link
const vuetifyConfig = (state) => ({
  props: {
    'error-messages': state.errors,
  },
});

const [email, emailProps] = defineField('email', vuetifyConfig);
const [password, passwordProps] = defineField('password', vuetifyConfig);

const onSubmit = handleSubmit((values) => {
  const { $api } = useNuxtApp();
  $api('/owner-auth/login', {
    method: 'POST',
    body: values,
  })
  console.log('Submitted with', values);
});
</script>

<template>
  <v-form @submit="onSubmit" class="px-4">
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
