import { defineStore } from 'pinia';

interface State {
  isLogin: boolean;
}

/**
 * リロード時のログイン状態判定
 */
const isDefaultLogin = () => {
  const token = useCookie('member_token');
  return !!token.value;
};

export const useAuthMemberStore = defineStore('auth_member', {
  state: (): State => {
    return {
      isLogin: isDefaultLogin(),
    };
  },
  actions: {
    loginAsMember(tokenValue: string) {
      const token = useCookie('member_token', {
        maxAge: 60 * 60, // 1時間
        secure: true,
        sameSite: 'strict',
      });
      token.value = tokenValue;
      this.isLogin = true;
    },
    logout() {
      const token = useCookie('member_token');
      token.value = null;
      this.isLogin = false;
    },
  },
});
