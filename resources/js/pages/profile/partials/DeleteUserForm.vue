<script setup>
import { Button as DangerButton } from '@/components/ui/button';
import InputError from '@/components/InputError.vue';
import { Label as InputLabel } from '@/components/ui/label';
import { Button as SecondaryButton } from '@/components/ui/button';
import { Input as TextInput } from '@/components/ui/input';
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Modal, ModalLink } from '@inertiaui/modal-vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

const passwordInput = ref(null);

const form = useForm({
  password: ''
});

const deleteUser = () => {
  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value.focus(),
    onFinish: () => form.reset()
  });
};

const closeModal = () => {
  confirmingUserDeletion.value = false;

  form.reset();
};
</script>

<template>
  <section class="space-y-6">
    <header>
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Delete Account</h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting
        your account, please download any data or information that you wish to retain.
      </p>
    </header>

    <DangerButton
      variant="destructive"
      :as="ModalLink"
      href="#confirm-action">
      Delete Account
    </DangerButton>

    <Modal
      v-slot="{ close }"
      name="confirm-action"
      panel-classes="p-0"
      :close-button="false"
      :close-explicitly="true">
      <Card>
        <CardHeader>
          <CardTitle>
            Are you sure you want to delete your account?
          </CardTitle>

          <CardDescription>
            Once your account is deleted, all of its resources and data will be permanently deleted. Please
            enter your password to confirm you would like to permanently delete your account.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="mt-6">
            <InputLabel for="password" value="Password" class="sr-only" />

            <TextInput
              id="password"
              ref="passwordInput"
              v-model="form.password"
              type="password"
              class="mt-1 block w-full"
              placeholder="Password"
              @keyup.enter="deleteUser"
            />

            <InputError :message="form.errors.password" class="mt-2" />
          </div>

          <div class="mt-6 flex justify-end">
            <SecondaryButton
              @click="close" variant="outline">
              Cancel
            </SecondaryButton>

            <DangerButton
              class="ms-3"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              @click="deleteUser">
              Delete
            </DangerButton>
          </div>
        </CardContent>
      </Card>
    </Modal>
  </section>
</template>
