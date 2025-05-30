import { FetchError } from 'ofetch';
import { useNotifyBottomSheetStore } from '~/store/notifyBottomSheet';
import type { FlattenAndSetPathsType } from 'vee-validate';

const notifyBottomSheetStore = useNotifyBottomSheetStore();
export const handleFetchError = (e: unknown, setErrors: (fields: Partial<FlattenAndSetPathsType<any, any>>) => void) => {
  if (!(e instanceof FetchError)) {
    console.error(e);
    return;
  }

  switch (e.status) {
    case 400:
    case 401:
    case 404:
      notifyBottomSheetStore.setMessage(e.data.message);
      break;
    case 422:
      if (setErrors === null) {
        console.error(e);
        notifyBottomSheetStore.setMessage(e.message);
        return;
      }
      setErrors(e.data.errors);
      break;
    default:
      console.error(e);
      notifyBottomSheetStore.setMessage(e.message);
  }
};
