<script setup>

import Footer from '@/layouts/components/Footer.vue'
import navItems from '@/navigation/vertical'
import { useThemeConfig } from '@core/composable/useThemeConfig'
import { useEventsStore } from '@/stores/useEvents'

// Components
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'

// @layouts plugin
import { VerticalNavLayout } from '@layouts'

const { appRouteTransition, isLessThanOverlayNavBreakpoint } = useThemeConfig()
const { width: windowWidth } = useWindowSize()

const isTooltipVisible = ref(false)
const eventsStore = useEventsStore()
const countServices = ref(0)

watch(() => 
  eventsStore.getCount, async (value) => {
    countServices.value = value
  }
);

watchEffect(fetchData)

async function fetchData() {
  countServices.value = await eventsStore.getPendings()
}

</script>

<template>
  <VerticalNavLayout
    :nav-items="navItems"
  >
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <VBtn
          v-if="isLessThanOverlayNavBreakpoint(windowWidth)"
          icon
          variant="text"
          color="default"
          class="ms-n3"
          size="small"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            icon="tabler-menu-2"
            size="24"
          />
        </VBtn>

        <NavbarThemeSwitcher />

        <VSpacer />
                  
        <VTooltip
          :model-value="isTooltipVisible"
          location="top"
          v-if="$can('ver', 'servicios')"
        >
          <template #activator="{ props }">
            <VBadge
              color="primary"
              :content="countServices" 
              class="me-2" 
              v-if="countServices > 0">
              <VBtn
                v-bind="props"
                variant="outlined"
                color="success"
                size="small"
                icon="tabler-bell"
                :to="{ name: 'dashboard-calendar' }"
                >
                <VIcon size="24" icon="tabler-bell" />
              </VBtn>
            </VBadge>
            <VBtn
              v-bind="props"
              variant="outlined"
              color="success"
              size="small"
              icon="tabler-bell"
              class="me-2" 
              :to="{ name: 'dashboard-calendar' }"
              v-else
              >
              <VIcon size="24" icon="tabler-bell" />
            </VBtn>
          </template>
          <span>Servicios</span>
        </VTooltip>       

        <UserProfile />
      </div>
    </template>

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Transition
        :name="appRouteTransition"
        mode="out-in"
      >
        <Component :is="Component" />
      </Transition>
    </RouterView>

    
    <!--  👉 Footer -->
    <template #footer>
      <Footer />
    </template>
    

    <!-- 👉 Customizer -->
    <!-- <TheCustomizer /> -->
  </VerticalNavLayout>
</template>
