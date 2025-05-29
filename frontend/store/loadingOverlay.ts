import {defineStore} from "pinia";

interface State {
  isLoading: boolean;
}

export const useLoadingOverlayStore = defineStore('loading_overlay', {
  state: (): State => {
    return {
      isLoading: false,
    };
  },
  actions: {
    resetLoading() {
      this.$reset();
    },
    setActive() {
      this.isLoading = true;
    },
  },
});
