export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  const api = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    // onRequest({ request, options, error }) {
    // if (session.value?.token) {
    //   // note that this relies on ofetch >= 1.4.0 - you may need to refresh your lockfile
    //   options.headers.set('Authorization', `Bearer ${session.value?.token}`)
    // }
    // },
    // async onResponseError({ response }) {
    // if (response.status === 401) {
    //   await nuxtApp.runWithContext(() => navigateTo('/login'));
    // }
    // },
  });

  const ownerApi = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    onRequest({ options }) {
      const token = useCookie('owner_token');
      if (token.value) {
        options.headers.set('Authorization', `Bearer ${token.value}`);
      }
    },
    async onResponseError({ response }) {
      if (response.status === 401) {
        await nuxtApp.runWithContext(() => navigateTo('/owner/login'));
      }
    },
  });

  const memberApi = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    onRequest({ options }) {
      const token = useCookie('member_token');
      if (token.value) {
        options.headers.set('Authorization', `Bearer ${token.value}`);
      }
    },
    async onResponseError({ response }) {
      if (response.status === 401) {
        await nuxtApp.runWithContext(() => navigateTo('/member/login'));
      }
    },
  });

  // Expose to useNuxtApp().$api
  return {
    provide: {
      api,
      ownerApi,
      memberApi
    },
  };
});
