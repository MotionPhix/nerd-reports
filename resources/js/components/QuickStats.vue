<script setup lang="ts">
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { type Component, computed } from 'vue';

const props = defineProps<{
  title: string;
  mainCount: number;
  mainIcon?: Component;
  subCountIcon?: Component;
  subCountLabel?: string;
  hasWarning?: boolean;
  warningCountLabel?: number;
  color?: string;
}>();

const theming = computed(() => {
  return props.color === 'blue' ? 'text-blue-600 dark:text-blue-400' :
    props.color === 'purple' ? 'text-purple-600 dark:text-purple-400' :
      props.color === 'green' ? 'text-green-600 dark:text-green-400' :
        props.color === 'orange' ? 'text-orange-600 dark:text-orange-400' :
          'text-gray-600 dark:text-gray-400';
});

const iconBgColor = computed(() => {
  return props.color === 'blue' ? 'bg-blue-100 dark:bg-blue-900/20' :
    props.color === 'purple' ? 'bg-purple-100 dark:bg-purple-900/20' :
      props.color === 'green' ? 'bg-green-100 dark:bg-green-900/20' :
        props.color === 'orange' ? 'bg-orange-100 dark:bg-orange-900/20' :
          'bg-gray-100 dark:bg-gray-900/20';
});
</script>

<template>
  <div class="grid grid-cols-2 gap-4 items-end">
    <div class="space-y-2">
      <p class="text-sm font-medium text-muted-foreground">
        {{ title }}
      </p>

      <p class="text-3xl font-bold" :class="theming">
        {{ mainCount || 0 }}
      </p>
    </div>

    <div class="items-end justify-items-end">
      <div class="size-10 rounded-full p-2" :class="iconBgColor">
        <component :is="mainIcon" class="h-6 w-6" :class="theming" />
      </div>
    </div>

    <div class="col-span-2 flex items-start gap-2 text-xs text-muted-foreground">
      <component :is="subCountIcon" class="h-4 w-4 text-green-500 shrink-0" />
      <span>{{ subCountLabel }}</span>
    </div>
  </div>

  <div class="flex-1"></div>

  <Separator class="my-4" v-if="hasWarning" />

  <div v-if="hasWarning">
    <Badge variant="destructive" class="text-xs">
      {{ warningCountLabel }}
    </Badge>
  </div>
</template>
