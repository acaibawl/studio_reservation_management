<script setup>
import { useForm } from 'vee-validate';
import * as yup from 'yup';

const schema = yup.object({
  name: yup.string().required().label('名前'),
  email: yup.string().email().required().label('メールアドレス'),
  password: yup.string().min(6).required().label('パスワード'),
  passwordConfirm: yup
    .string()
    .oneOf([yup.ref('password')], 'Passwords must match')
    .required()
    .label('パスワード確認'),
  terms: yup
    .boolean()
    .required()
    .oneOf([true], 'You must agree to terms and conditions')
    .label('同意'),
});

const { defineField, handleSubmit, resetForm } = useForm({
  validationSchema: schema,
});

// Refer to the docs for how to make advanced validation behaviors with dynamic configs
// TODO: Add link
const vuetifyConfig = (state) => ({
  props: {
    'error-messages': state.errors,
  }
});

const [name, nameProps] = defineField('name', vuetifyConfig);
const [email, emailProps] = defineField('email', vuetifyConfig);
const [password, passwordProps] = defineField('password', vuetifyConfig);
const [passwordConfirm, confirmProps] = defineField('passwordConfirm', vuetifyConfig);
const [terms, termsProps] = defineField('terms', vuetifyConfig);

const onSubmit = handleSubmit((values) => {
  console.log('Submitted with', values);
});
</script>

<template>
  <v-form @submit="onSubmit" class="px-4">
    <v-text-field v-model="name" v-bind="nameProps" label="Name" />
    <v-text-field
      v-model="email"
      v-bind="emailProps"
      label="Email"
      type="email"
    />
    <v-text-field
      v-model="password"
      v-bind="passwordProps"
      label="Password"
      type="password"
    />
    <v-text-field
      v-model="passwordConfirm"
      v-bind="confirmProps"
      label="Password confirmation"
      type="password"
    />

    <v-checkbox
      v-model="terms"
      v-bind="termsProps"
      label="Do you agree?"
      color="primary"
    />

    <v-btn color="primary" type="submit"> Submit </v-btn>
    <v-btn color="outline" class="ml-4" @click="resetForm()"> Reset </v-btn>
  </v-form>
</template>
