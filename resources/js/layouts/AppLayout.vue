<script setup lang="ts">
import { ref } from 'vue'
import { useDark, useToggle } from '@vueuse/core'
import { Toaster } from 'vue-sonner'
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { ScrollArea } from '@/components/ui/scroll-area'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import {
  Menu,
  Sun,
  Moon,
  Home,
  Users,
  Briefcase,
  ClipboardList,
  Settings,
  LayoutDashboard
} from 'lucide-vue-next'
import { Link } from '@inertiajs/vue3'
import MobileNav from '@/components/MobileNav.vue';

const isDark = useDark()
const toggleDark = useToggle(isDark)
const isMobileNavOpen = ref(false)

const navigation = [
  { name: 'Dashboard', href: route('dashboard'), icon: LayoutDashboard },
  { name: 'Firms', href: route('firms.index'), icon: Briefcase },
  { name: 'Contacts', href: route('contacts.index'), icon: Users },
  { name: 'Projects', href: route('projects.index'), icon: Home },
  { name: 'Tasks', href: route('tasks.index'), icon: ClipboardList },
  // { name: 'Settings', href: route('settings.index'), icon: Settings }
]
</script>

<template>
  <div class="h-screen w-full">
    <Toaster position="top-right" />

    <!-- Mobile Navigation Trigger -->
    <Sheet v-model:open="isMobileNavOpen" class="lg:hidden">
      <SheetTrigger asChild>
        <Button
          variant="ghost"
          class="fixed left-4 top-4 lg:hidden">
          <Menu class="h-6 w-6" />
        </Button>
      </SheetTrigger>

      <SheetContent side="left" class="w-64 p-0">
        <MobileNav :navigation="navigation" />
      </SheetContent>
    </Sheet>

    <div class="flex h-full">
      <!-- Desktop Sidebar -->
      <div class="hidden lg:flex lg:w-64 lg:flex-col">
        <nav class="flex flex-1 flex-col border-r bg-white dark:bg-gray-900">
          <div class="p-6">
            <ApplicationLogo class="h-8 w-auto" />
          </div>

          <ScrollArea class="flex-1 px-3">
            <div class="space-y-1">
              <Link
                v-for="item in navigation"
                :key="item.name"
                :href="item.href"
                :class="[
                  route().current(item.href)
                    ? 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white'
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800',
                  'group flex items-center rounded-md px-3 py-2 text-sm font-medium'
                ]">
                <component
                  :is="item.icon"
                  :class="[
                    route().current(item.href)
                      ? 'text-gray-900 dark:text-white'
                      : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400',
                    'mr-3 h-5 w-5 flex-shrink-0'
                  ]"
                />
                {{ item.name }}
              </Link>
            </div>
          </ScrollArea>

          <div class="border-t p-4">
            <Button
              variant="ghost"
              size="icon"
              @click="toggleDark()"
              class="ml-auto">
              <Sun v-if="isDark" class="h-5 w-5" />
              <Moon v-else class="h-5 w-5" />
            </Button>
          </div>
        </nav>
      </div>

      <!-- Main Content -->
      <main class="flex-1 bg-gray-50 dark:bg-gray-900">
        <div class="h-full overflow-y-auto max-w-4xl p-6">
          <slot />
        </div>
      </main>
    </div>
  </div>
</template>
