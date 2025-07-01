import ToastItem from '@/Components/ToastItem.vue'
import { random } from 'lodash'
import { defineStore } from 'pinia'
import { toast } from "vue-sonner"

interface toastState {
  type?: string
  title?: string|boolean
  message: string
}

export const useToastStore = defineStore('toast', () => {

  function notify({
    type = 'success',
    title = false as string | boolean,
    message = 'contact was created'
  } = {}) {

    const _title = [
      'Great!',
      'Awesome',
      'That\'s about right!'
    ][random(0, 2)];

    const props: toastState = {
      message: message,
      type: type
    }

    if (title && typeof title === 'string') {
      props.title = title
    }

    if (title && typeof title === 'boolean') {
      props.title = _title
    }

    const toastContent = {
      component: ToastItem,
      props,
    };

    toast(toastContent);
  }

  return { notify }
})
