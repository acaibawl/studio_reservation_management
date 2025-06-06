import { useAuthOwnerStore } from '~/store/authOwner';

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('owner_token');

  if (!token.value) {
    return navigateTo(`/owner/login?redirectedFrom=${to.fullPath}`);
  }

  const { isLogin, loggedIn } = useAuthOwnerStore();
  if (isLogin) {
    return;
  }
  loggedIn();
});
