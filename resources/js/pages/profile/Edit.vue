<script setup lang="ts">
import DeleteUserForm from '@/pages/profile/partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/pages/profile/partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/pages/profile/partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineProps({
  mustVerifyEmail: {
    type: Boolean
  },
  status: {
    type: String
  }
});

defineOptions({
  layout: AppLayout
});
</script>

<template>
  <Head title="Profile" />

  <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
    Profile
  </h2>

  <div class="py-12 p-6 max-w-4xl space-y-6">
    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
      <UpdateProfileInformationForm
        :must-verify-email="mustVerifyEmail"
        :status="status"
        class="w-full"
      />
    </div>

    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
      <UpdatePasswordForm class="max-w-full" />
    </div>

    <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
      <DeleteUserForm class="max-w-full" />
    </div>
  </div>
</template>
