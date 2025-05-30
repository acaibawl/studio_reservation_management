import type { InputBindsConfig, LazyInputBindsConfig } from 'vee-validate';

export const yupFieldLazyVuetifyConfig: InputBindsConfig | LazyInputBindsConfig = state => ({
  props: {
    'error-messages': state.errors,
  },
});

export const yupFieldImmediateVuetifyConfig: InputBindsConfig | LazyInputBindsConfig = state => ({
  props: {
    'error-messages': state.errors,
  },
  validateOnModelUpdate: true,
});
