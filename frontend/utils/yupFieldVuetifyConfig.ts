import type { InputBindsConfig, LazyInputBindsConfig } from 'vee-validate';

export const yupFieldVuetifyConfig: InputBindsConfig | LazyInputBindsConfig = state => ({
  props: {
    'error-messages': state.errors,
  },
});
