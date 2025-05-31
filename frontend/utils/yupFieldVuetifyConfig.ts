import type { InputBindsConfig, LazyInputBindsConfig } from 'vee-validate';

export const createYupFieldVuetifyConfig = (options?: { validateOnModelUpdate?: boolean }): InputBindsConfig | LazyInputBindsConfig => state => ({
  props: {
    'error-messages': state.errors,
  },
  ...(options?.validateOnModelUpdate && { validateOnModelUpdate: true }),
});

export const yupFieldLazyVuetifyConfig = createYupFieldVuetifyConfig();
export const yupFieldImmediateVuetifyConfig = createYupFieldVuetifyConfig({ validateOnModelUpdate: true });
