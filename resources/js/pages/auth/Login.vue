<script setup lang="ts">
import Checkbox from '@/components/Checkbox.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import GuestLayout from '@/layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';

defineProps({
  canResetPassword: {
    type: Boolean
  },
  status: {
    type: String
  }
});

const form = useForm({
  email: '',
  password: '',
  remember: false
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password')
  });
};
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" value="Email Address" />

        <TextInput
          id="email"
          type="email"
          class="block w-full mt-1"
          v-model="form.email"
          required
          autofocus
          placeholder="Enter your email address"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="mt-4">
        <InputLabel for="password" value="Password" />

        <TextInput
          id="password"
          type="password"
          class="block w-full mt-1"
          v-model="form.password"
          required
          placeholder="Enter your good password"
        />

        <InputError class="mt-2" :message="form.errors.password" />
      </div>

      <div class="block mt-4">
        <label class="flex items-center">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="text-sm text-gray-600 ms-2 dark:text-gray-400">Remember me</span>
        </label>
      </div>

      <div class="flex items-center justify-end mt-4">
        <Button
          :as="Link"
          variant="link"
          v-if="canResetPassword"
          :href="route('password.request')">
          Forgot your password?
        </Button>

        <Button
          class="ms-4 cursor-pointer"
          type="submit"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing">
          Log in
        </Button>
      </div>
    </form>
  </GuestLayout>
</template>
