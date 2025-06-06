import { useAuthMemberStore } from '~/store/authMember';

export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('member_token');

  if (!token.value) {
    return navigateTo(`/member/login?redirectedFrom=${to.fullPath}`);
  }

  const { isLogin, loggedIn } = useAuthMemberStore();
  if (isLogin) {
    return;
  }
  loggedIn();
});
