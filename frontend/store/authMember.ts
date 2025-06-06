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
    loginAsMember(tokenValue: string, expiresIn: number) {
      const token = useCookie('member_token', {
        maxAge: expiresIn * 60, // expiresInは分単位で渡ってくるので、60を掛けて秒に変換
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
