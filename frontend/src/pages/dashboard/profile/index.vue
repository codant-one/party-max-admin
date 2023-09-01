<script setup>

import TabSecurity from '@/views/dashboard/profile/TabSecurity.vue'
import TabStatistics from '@/views/dashboard/profile/TabStatistics.vue'
import UserProfile from '@/views/dashboard/profile/UserProfile.vue'

const userData = ref(JSON.parse(localStorage.getItem('user_data') || 'null'))
const userTab = ref(null)

const tabs = [
  {
    icon: 'tabler-lock',
    title: 'Seguridad',
  },
  {
    icon: 'tabler-chart-pie-filled',
    title: 'Estadísticas',
  },
]
</script>

<template>
  <VRow v-if="userData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <UserProfile />
    </VCol>
    <VCol
      cols="12"
      md="7"
      lg="8"
    >
      <VTabs
        v-model="userTab"
        class="v-tabs-pill"
      >
        <VTab
          v-for="tab in tabs"
          :key="tab.icon"
        >
          <VIcon
            :size="18"
            :icon="tab.icon"
            class="me-1"
          />
          <span>{{ tab.title }}</span>
        </VTab>
      </VTabs>

      <VWindow
        v-model="userTab"
        class="mt-6 disable-tab-transition"
        :touch="false"
      >
        <VWindowItem>
          <TabSecurity />
        </VWindowItem>

        <VWindowItem>
          <TabStatistics />
        </VWindowItem>
      </VWindow>
    </VCol>
  </VRow>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: dashboard
</route>
