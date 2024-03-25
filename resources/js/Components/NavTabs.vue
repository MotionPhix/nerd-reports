<script lang="ts" setup>
import MazTabs from 'maz-ui/components/MazTabs';
import MazTabsBar, { MazTabsBarItem } from 'maz-ui/components/MazTabsBar';
import MazTabsContent from 'maz-ui/components/MazTabsContent';
import MazTabsContentItem from 'maz-ui/components/MazTabsContentItem';
import { ref } from 'vue';

const tabs: MazTabsBarItem[] = [
  { label: 'Overview', disabled: false },
  { label: 'Interactions', disabled: false, },
]

const currentTab = ref(1)
</script>

<template>
  <MazTabs v-model="currentTab" class="mt-8">
    <div class="pb-2 maz-flex maz-items-center">
      <MazTabsBar :items="tabs" persistent no-elevation block />
    </div>

    <MazTabsContent>
      <MazTabsContentItem :tab="1" class="maz-py-4">
        <slot name="tab_1" />
      </MazTabsContentItem>

      <MazTabsContentItem :tab="2" class="maz-py-4">
        <slot name="tab_2" />
      </MazTabsContentItem>
    </MazTabsContent>
  </MazTabs>
</template>
