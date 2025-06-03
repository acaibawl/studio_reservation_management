import { defineStore } from 'pinia';
import type { FlattenAndSetPathsType } from 'vee-validate';
import { FetchError } from 'ofetch';

interface State {
  isVisible: boolean;
  message: string;
}

export const useNotifyBottomSheetStore = defineStore('notify_bottom_sheet', {
  state: (): State => {
    return {
      isVisible: false,
      message: '',
    };
  },
  actions: {
    resetMessage() {
      this.$reset();
    },
    setMessage(message: string) {
      this.message = message;
      this.isVisible = true;
    },
    handleFetchError(e: unknown, setErrors?: (fields: Partial<FlattenAndSetPathsType<any, any>>) => void) {
      if (!(e instanceof FetchError)) {
        console.error(e);
        return;
      }

      switch (e.status) {
        case 401:
          this.setMessage('認証に失敗しました。');
          break;
        case 400:
        case 404:
          this.setMessage(e.data.message);
          break;
        case 422:
          if (!setErrors) {
            console.error(e);
            this.setMessage(e.message);
            return;
          }
          setErrors(e.data.errors);
          break;
        default:
          console.error(e);
          this.setMessage(e.message);
      }
    },
  },
});
