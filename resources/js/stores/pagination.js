import { reactive } from 'vue'

export const paginationStore = reactive({
  page: 1,
  perPage: 10,
  total: 0,
})

export function setPage(page) {
  paginationStore.page = page
}

export function setPerPage(perPage) {
  paginationStore.perPage = perPage
}

export function setTotal(total) {
  paginationStore.total = total
}
