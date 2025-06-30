<script setup lang="ts">
import { Toaster } from 'vue-sonner'
import {
  Sidebar,
  SidebarContent,
  SidebarProvider,
  SidebarGroup,
  SidebarGroupContent,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarHeader,
  SidebarFooter
} from '@/components/ui/sidebar'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import {
  Home,
  Users,
  ClipboardList,
  LayoutDashboard, BriefcaseBusinessIcon
} from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import { Separator } from '@/components/ui/separator';
import NavUser from '@/components/NavUser.vue';

const items = [
  {
    title: 'Dashboard',
    url: route('dashboard'),
    icon: LayoutDashboard,
    active: usePage().url === new URL(route('dashboard')).pathname
  },
  {
    title: 'Firms',
    url: route('firms.index'),
    icon: BriefcaseBusinessIcon,
    active: usePage().url.startsWith(new URL(route('firms.index'))).pathname
  },
  {
    title: 'Projects',
    url: route('projects.index'),
    icon: Home,
    active: usePage().url.startsWith(new URL(route('projects.index')).pathname)
  },
  {
    title: 'Contacts',
    url: route('contacts.index'),
    icon: Users,
    active: usePage().url.startsWith(new URL(route('contacts.index')).pathname)
  },
  {
    title: 'Tasks',
    url: route('tasks.index'),
    icon: ClipboardList,
    active: usePage().url.startsWith(new URL(route('tasks.index')).pathname)
  }
]
</script>

<template>
  <SidebarProvider class="h-full">
    <div class="h-screen w-full">
      <Toaster rich-colors position="top-right" />

      <div class="flex h-full">
        <Sidebar class="border-r" variant="inset">
          <SidebarHeader class="border-b p-4">
            <div class="flex items-center gap-2">
              <ApplicationLogo class="h-6 w-6" />
              <span class="font-semibold">Dashboard</span>
            </div>
          </SidebarHeader>

          <SidebarContent>
            <SidebarGroup>
              <SidebarGroupLabel>Navigation</SidebarGroupLabel>
              <SidebarGroupContent>
                <SidebarMenu>
                  <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton asChild>
                      <Link
                        :href="item.url"
                        :class="[
                          item.active
                            ? 'bg-neutral-200 dark:bg-neutral-800 text-neutral-900 dark:text-white'
                            : 'text-neutral-600 dark:text-neutral-400 hover:bg-neutral-50 dark:hover:bg-neutral-800'
                        ]">
                        <component
                          :is="item.icon"
                          class="mr-2 h-4 w-4"
                          :class="[
                            item.active
                              ? 'text-gray-900 dark:text-white'
                              : 'text-gray-500 dark:text-gray-400'
                          ]"
                        />
                        <span>{{ item.title }}</span>
                      </Link>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                </SidebarMenu>
              </SidebarGroupContent>
            </SidebarGroup>
          </SidebarContent>

          <Separator />

          <SidebarFooter class="border-t p-4">
            <NavUser :user="$page.props.auth.user" />
          </SidebarFooter>
        </Sidebar>

        <!-- Main Content -->
        <main class="flex-1 bg-gray-50 dark:bg-gray-900">
          <div class="h-full overflow-y-auto">
            <slot />
          </div>
        </main>
      </div>
    </div>
  </SidebarProvider>
</template>
