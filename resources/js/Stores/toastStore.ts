import ToastItem from '@/Components/ToastItem.vue'
import { random } from 'lodash'
import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'

interface toastState {
  type?: string
  title?: string|boolean
  message: string
}

const toast = useToast()

export const useToastStore = defineStore('toast', () => {

  function notify({
    type = 'success',
    title = false as string | boolean,
    message = 'Contact was created'
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
