import { useAuthOwnerStore } from '~/store/authOwner';

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('owner_token', {
    maxAge: 60 * 60,
  });

  if (!token.value) {
    return navigateTo(`/owner/login?redirectedFrom=${to.fullPath}`);
  }

  const { isLogin, loginAsOwner } = useAuthOwnerStore();
  if (isLogin) {
    return;
  }
  loginAsOwner(token.value);
});
