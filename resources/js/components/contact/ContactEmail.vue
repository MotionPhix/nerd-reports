<script setup lang="ts">

interface Props {
  emails: App.Data.EmailData[]
}

const props = defineProps<Props>()

</script>

<template>
  <span>
    {{ (props.emails.filter(email => email.is_primary_email)[0] || {}).email || 'No primary email' }}
  </span>
</template>
