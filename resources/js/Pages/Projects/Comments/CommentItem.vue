<script setup lang="ts">
import { router, usePage } from "@inertiajs/vue3"
import { useTaskStore } from "@/Stores/taskStore"
import SentComment from "@/Pages/Projects/Comments/SentComment.vue";
import ReceivedComment from "@/Pages/Projects/Comments/ReceivedComment.vue";
import { storeToRefs } from "pinia";
import { computed } from "vue";

const props = defineProps<{

  comment: App.Data.CommentData

}>()

const localComment = computed(() => props.comment)

const { user } = usePage().props.auth

const taskStore = useTaskStore()

const { task } = storeToRefs(taskStore)

const { reFetchTask } = taskStore

const deleteComment = () => {

  router.delete(route('comments.destroy', { 'comment': localComment.value.id }), {

    preserveScroll: true,

    onSuccess: () => {

      reFetchTask()

    }

  })

}

</script>

<template>

  <ReceivedComment
    :delete-comment="deleteComment"
    v-if="user.email === props.comment.user.email"
    :comment="props.comment" />

  <SentComment
    :comment="props.comment"
    :delete-comment="deleteComment"
    v-else />

</template>

<style scoped></style>
