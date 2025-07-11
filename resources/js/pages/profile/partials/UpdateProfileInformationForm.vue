<script setup>
import InputError from '@/components/InputError.vue';
import { Label as InputLabel } from '@/components/ui/label';
import { Button as PrimaryButton } from '@/components/ui/button';
import { Input as TextInput } from '@/components/ui/input';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
  mustVerifyEmail: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const user = usePage().props.auth.user;

const form = useForm({
  first_name: user.first_name,
  last_name: user.last_name,
  email: user.email,
});
</script>

<template>
  <section>
    <header>
      <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h2>

      <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        Update your account's profile information and email address.
      </p>
    </header>

    <form
      @submit.prevent="form.patch(route('profile.update'))"
      class="mt-6 space-y-6">

      <div>
        <InputLabel
          for="first_name"
          value="First Name" />

        <TextInput
          id="first_name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.first_name"
          required
          autofocus
          autocomplete="first_name" />

        <InputError
          class="mt-2"
          :message="form.errors.first_name" />
      </div>

      <div>
        <InputLabel
          for="last_name"
          value="Last Name" />

        <TextInput
          id="last_name"
          type="text"
          class="mt-1 block w-full"
          v-model="form.last_name"
          required
          autofocus
          autocomplete="last_name" />

        <InputError
          class="mt-2"
          :message="form.errors.last_name" />
      </div>

      <div>
        <InputLabel
          for="email"
          value="Email" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autocomplete="username" />

        <InputError
          class="mt-2"
          :message="form.errors.email" />
      </div>

      <div v-if="mustVerifyEmail && user.email_verified_at === null">
        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
          Your email address is unverified.
          <Link :href="route('verification.send')"
                method="post"
                as="button"
                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
          Click here to re-send the verification email.
          </Link>
        </p>

        <div v-show="status === 'verification-link-sent'"
             class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
          A new verification link has been sent to your email address.
        </div>
      </div>

      <div class="flex items-center gap-4">
        <PrimaryButton :disabled="form.processing" class="rounded py-2.5">Save</PrimaryButton>

        <Transition enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0">
          <p v-if="form.recentlySuccessful"
             class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
        </Transition>
      </div>
    </form>
  </section>
</template>
