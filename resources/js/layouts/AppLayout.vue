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
  SidebarFooter,
  SidebarInset
} from '@/components/ui/sidebar'
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import {
  Home,
  Users,
  ClipboardList,
  BriefcaseBusiness,
  ChartLine
} from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3'
import { Separator } from '@/components/ui/separator'
import NavUser from '@/components/NavUser.vue'
import { computed } from 'vue'
import NavFooter from '@/components/NavFooter.vue';

const page = usePage()
const currentUrl = computed(() => page.url)

const items = computed(() => [
  {
    title: 'Dashboard',
    url: route('dashboard'),
    icon: ChartLine,
    active: currentUrl.value === new URL(route('dashboard')).pathname
  },
  {
    title: 'Firms',
    url: route('firms.index'),
    icon: BriefcaseBusiness,
    active: currentUrl.value.startsWith(new URL(route('firms.index')).pathname)
  },
  {
    title: 'Contacts',
    url: route('contacts.index'),
    icon: Users,
    active: currentUrl.value.startsWith(new URL(route('contacts.index')).pathname)
  },
  {
    title: 'Projects',
    url: route('projects.index'),
    icon: Home,
    active: currentUrl.value.startsWith(new URL(route('projects.index')).pathname)
  },
  {
    title: 'Tasks',
    url: route('tasks.index'),
    icon: ClipboardList,
    active: currentUrl.value.startsWith(new URL(route('tasks.index')).pathname)
  }
])
</script>

<template>
  <SidebarProvider>
    <Toaster rich-colors position="top-right" />

    <Sidebar variant="inset">
      <SidebarHeader>
        <div class="flex items-center gap-2 py-2">
          <ApplicationLogo class="size-10" />
          <span class="font-semibold text-2xl text-muted-foreground">
            {{ $page.props.appName }}
          </span>
        </div>
      </SidebarHeader>

      <Separator />

      <SidebarContent>
        <SidebarGroup>
          <SidebarGroupLabel>Navigation</SidebarGroupLabel>
          <SidebarGroupContent>
            <SidebarMenu>
              <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton :is-active="item.active" as-child>
                  <Link :href="item.url">
                    <component :is="item.icon" />
                    <span>{{ item.title }}</span>
                  </Link>
                </SidebarMenuButton>
              </SidebarMenuItem>
            </SidebarMenu>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarContent>

      <SidebarFooter>
        <NavFooter :items="[
          { title: 'GitHub', href: '', icon: ClipboardList },
          { title: 'Support', href: '', icon: Users },
          { title: 'Contact', href: '', icon: Home },
          { title: 'About', href: '', icon: BriefcaseBusiness }
        ]" />
        <NavUser :user="$page.props.auth.user" />
      </SidebarFooter>
    </Sidebar>

    <SidebarInset class="overflow-x-hidden">
      <main class="flex flex-1 flex-col">
        <div class="min-h-screen flex-1 rounded-xl bg-muted/50 dark:bg-muted md:min-h-min">
          <slot />
        </div>
      </main>
    </SidebarInset>
  </SidebarProvider>
</template>
