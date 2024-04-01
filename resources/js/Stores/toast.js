import { reactive } from 'vue'

export default reactive({
  messages: [],

  add(toast) {
    this.messages.unshift({
      key: Symbol(),
      ...toast,
    })
  },

  remove(idx) {
    this.messages.splice(idx, 1)
  },
})
