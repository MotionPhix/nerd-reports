<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3"
import { useTaskStore } from "@/Stores/taskStore"
import UserComment from "@/Pages/Projects/Comments/UserComment.vue";
import ResponderComment from "./ResponderComment.vue";

const props = defineProps<{
  comment: App.Data.CommentData
}>()

const { user } = usePage().props.auth

const taskStore = useTaskStore()
const { reFetchTask } = taskStore

const deleteComment = () => {

  router.delete(route('comments.destroy', { 'comment': props.comment.id }), {

    preserveScroll: true,

    onSuccess: () => {

      reFetchTask()

    }

  })

}

</script>

<template>

  <ResponderComment
    v-if="user.email === props.comment.user.email"
    :delete-comment="deleteComment"
    :comment="props.comment" />

  <UserComment
    :comment="props.comment" v-else
    :delete-comment="deleteComment" />

</template>

<style scoped></style>
