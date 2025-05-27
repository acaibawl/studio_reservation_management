import { defineStore } from 'pinia';

interface State {
  isLogin: boolean;
}

/**
 * リロード時のログイン状態判定
 */
const isDefaultLogin = () => {
  const token = useCookie('owner_token');
  return !!token.value;
};

export const useAuthOwnerStore = defineStore('auth_owner', {
  state: (): State => {
    return {
      isLogin: isDefaultLogin(),
    };
  },
  actions: {
    loginAsOwner(tokenValue: string) {
      const token = useCookie('owner_token', {
        maxAge: 60 * 60, // 1時間
        secure: true,
        sameSite: 'strict',
      });
      token.value = tokenValue;
      this.isLogin = true;
    },
    logout() {
      const token = useCookie('owner_token');
      token.value = null;
      this.isLogin = false;
    },
  },
});
