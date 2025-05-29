import {defineStore} from "pinia";

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
  },
});
