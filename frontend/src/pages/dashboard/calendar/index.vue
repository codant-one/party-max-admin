<script setup>

import '@fullcalendar/core/vdom'
import { blankEvent, useCalendar } from '@/stores/useCalendar'
import { useEventsStore } from '@/stores/useEvents'
import { useResponsiveLeftSidebar } from '@core/composable/useResponsiveSidebar'
import FullCalendar from '@fullcalendar/vue3'
import CalendarEventHandler from '@/views/apps/calendar/CalendarEventHandler.vue'

const store = useEventsStore()

const event = ref(structuredClone(blankEvent))
const guestsOptions = ref([])
const isEventHandlerSidebarActive = ref(false)

watch(isEventHandlerSidebarActive, val => {
  if (!val)
    event.value = structuredClone(blankEvent)
})

store.fetchCategories()

const { isLeftSidebarOpen } = useResponsiveLeftSidebar()
const { advisor, isRequestOngoing, refCalendar, calendarOptions, addEvent, updateEvent, removeEvent, jumpToDate } = useCalendar(event, isEventHandlerSidebarActive, isLeftSidebarOpen)

// 👉 Check all
const checkAll = computed({
  get: () => store.selectedCalendars.length === store.availableCalendars.length,
  set: val => {
    if (val)
      store.selectedCalendars = store.availableCalendars.map(i => i.label)
    else if (store.selectedCalendars.length === store.availableCalendars.length)
      store.selectedCalendars = []
  },
})

watchEffect(fetchData)

async function fetchData() {

    guestsOptions.value = []

    await store.getPendings()
    await store.getUsers()

    store.getUsersArray.forEach(element => {
        guestsOptions.value.push({
          id: element.id,
          name: element.user_detail.store_name ?? (element.supplier?.company_name ?? (element.name + ' ' + (element.last_name ?? '')))
        })
    })

    store.selectedUsers = guestsOptions.value.map((element) => element.id)
}

const likesAllUser = computed(() => {
    return store.selectedUsers.length === guestsOptions.value.length
})

const likesSomeUser = computed(() => {
    return store.selectedUsers.length > 0
})

const toggle  = () => { 
    if (likesAllUser.value) {
        store.selectedUsers = []
    } else {
        store.selectedUsers = guestsOptions.value.map((element) => element.id)
    }
}

const closeDropdown = () => { 
    document.getElementById("selectFilterUsers").blur()
}

</script>

<template>
  <section>
    <v-alert
      v-if="advisor.show"
      :type="advisor.type"
      class="mb-6">
            
      {{ advisor.message }}
    </v-alert>

    <VDialog
      v-model="isRequestOngoing"
      width="300"
      persistent>
          
      <VCard
        color="primary"
        width="300">
            
        <VCardText class="pt-3">
          Cargando

          <VProgressLinear
            indeterminate
            color="white"
            class="mb-0"/>
        </VCardText>
      </VCard>
    </VDialog>
    <div>
      <div class="mt-5">
        <div class="p-0">
            <VCard>
                <VLayout style="z-index: 0; height: 1000px">
                  <VNavigationDrawer
                    v-model="isLeftSidebarOpen"
                    width="292"
                    absolute
                    touchless
                    location="start"
                    class="calendar-add-event-drawer"
                    :temporary="$vuetify.display.mdAndDown">
                    <!-- <div style="margin: 1.4rem;">
                      <VBtn
                        v-if="$can('crear','calendario')"
                        block
                        prepend-icon="tabler-plus"
                        @click="isEventHandlerSidebarActive = true">
                        Agregar Agenda
                      </VBtn>
                    </div>
                    <VDivider v-if="$can('administrador')" /> -->
                    <div class="d-flex align-center justify-center pa-2 mb-3">
                      <AppDateTimePicker
                        :model-value="new Date().toJSON().slice(0, 10)"
                        label="Inline"
                        :config="{ inline: true }"
                        class="calendar-date-picker"
                        @input="jumpToDate($event.target.value)"
                      />
                    </div>
                    <VDivider v-if="store.isAdmin"/>
                    <div v-if="store.isAdmin" class="pa-5">
                      <p class="text-sm text-uppercase text-disabled mb-3">
                        FILTROS POR PROVEEDORES
                      </p>
                      <VAutocomplete
                        id="selectFilterUsers"
                        v-model="store.selectedUsers"
                        label="Usuarios:"
                        :items="guestsOptions"
                        :item-title="item => item.name"
                        :item-value="item => item.id"
                        autocomplete="off"
                        multiple
                        :menu-props="{ maxHeight: '300px' }">
                        <template v-slot:prepend-item>
                          <v-list-item
                            title="Todos los Proveedores"
                            @click="toggle">
                            <template v-slot:prepend>
                              <v-checkbox-btn
                                :color="likesSomeUser ? 'primary' : 'indigo-darken-4'"
                                :indeterminate="likesSomeUser && !likesAllUser"
                                :model-value="likesSomeUser"
                              ></v-checkbox-btn>
                            </template>
                          </v-list-item>

                          <v-divider class="mt-2"></v-divider>
                        </template>
                          
                        <template v-slot:selection="{ item, index }">
                          <v-chip v-if="index < 2">
                            <span>{{ item.title }}</span>
                          </v-chip>
                          <span
                            v-if="index === 2"
                            class="text-grey text-caption align-self-center"
                          >
                            (+{{ store.selectedUsers.length - 2 }} otros)
                          </span>
                        </template>

                        <template v-slot:append-item>
                          <v-divider class="mt-2"></v-divider>
                          <v-list-item title="Cerrar Opciones" class="text-right">
                            <template v-slot:append>
                              <VBtn
                                size="small"
                                variant="plain"
                                icon="tabler-x"
                                color="black"
                                :ripple="false"
                                @click="closeDropdown"/>
                            </template>
                          </v-list-item>
                        </template>
                      </VAutocomplete>
                    </div>

                    <VDivider />

                    <div class="pa-5">
                      <p class="text-sm text-uppercase text-disabled mb-3">
                        FILTROS POR CATEGORÍAS
                      </p>

                      <div class="d-flex flex-column calendars-checkbox">
                        <VCheckbox
                          v-model="checkAll"
                          label="Ver Todas"
                        >
                          <template v-slot:label>
                            Ver Todas
                          </template>
                        </VCheckbox>
                        <VCheckbox
                            v-for="calendar in store.availableCalendars"
                            :key="calendar.label"
                            :value="calendar.label"
                            v-model="store.selectedCalendars"
                            color="primary"
                        >
                          <template v-slot:label>
                              {{ calendar.label }}
                          </template>
                        </VCheckbox>
                      </div>
                    </div>

                  </VNavigationDrawer>

                  <VMain>
                    <VCard flat>
                      <FullCalendar
                        ref="refCalendar"
                        :options="calendarOptions"
                      />
                    </VCard>
                  </VMain>
                </VLayout>
              </VCard>
              <CalendarEventHandler
                v-model:isDrawerOpen="isEventHandlerSidebarActive"
                :event="event"
                @add-event="addEvent"
                @update-event="updateEvent"
                @remove-event="removeEvent"
              />
        </div>
      </div>
    </div>
  </section>
</template>

<route lang="yaml">
  meta:
    action: ver
    subject: calendario
</route>

<style lang="scss">
@use "@core/scss/template/libs/full-calendar";

.p-0  {
  padding: 0 !important
}

.event-content {
  display: flex;
  padding: 8px 14px;
}

.users-content {
  display: block;
  font-size: 1.2em;
}

.fc-daygrid-day .users-content {
  font-size: 16.8px;
  font-weight: normal;
}

.event-icon {
  display: flex;
  align-items: center;
  margin-left: auto; /* Mueve el ícono hacia la derecha */
  font-size: 18px;
  color: red; /* Cambiar el color del ícono */
  cursor: pointer; /* Agregar cursor de puntero */
}

.fc-event-title {
  font-size: 1.2em !important;
}

#app > section > div > div > div > div > div.layout-content-wrapper > main > div > section:nth-child(3) > div > div > div.v-layout > main > div > div.fc.fc-media-screen.fc-direction-ltr.fc-theme-standard > div.fc-view-harness.fc-view-harness-active > div > table > thead > tr > th > div > div > table > thead > tr > th > div {
  text-align: start !important;
  background-color: #F8F7FA !important;
}

.day-setting {
  border-bottom: 1px solid #ddd !important;
  background-color: white !important;
  padding: 0 !important;
  margin: 0 !important;
}

.fc .fc-daygrid-day {
  padding: 0;
}

.fc-col-header-cell-cushion  {
  padding: 8px 14px !important;
}

.event-content svg {
  position: static;
}

.icon-content {
  margin-top: -2px;
}

.border-success {
  border-left: 8px solid rgba(var(--v-theme-success), 0.7) !important;
  padding-left: 5px;
}

.border-error {
  border-left: 8px solid rgb(var(--v-theme-error), 0.8) !important;
  padding-left: 5px;
}

.border-warning {
  border-left: 8px solid rgb(var(--v-theme-warning), 0.6) !important;
  padding-left: 5px;
}

.fc .fc-event-main {
  padding-inline: 0 !important;
}

.fc-list-event-title {
  padding: 0 !important;
}

.user-content {
  height: 30px;
  margin: 7px 0; 
  display: flex;
  flex-direction: unset;
  align-items: center;
  justify-content: space-between;
}

.user-text {
  // flex: 0.4;
  width: 150px;
  max-width: 150px;
}

.position-vchip {
  position: inherit;
}

.event-image {
  width: 32px !important;
  height: 32px !important;
  margin-right: 10px;
  border-radius: 50%; /* Hacer la imagen circular */
  object-fit: cover; /* Ajustar la imagen al tamaño del contenedor */
}

.event-text {
  font-size: 14px;
  font-weight: bold;
}

@media (max-width: 991px) {

  .users-content {
    font-size: 0.7rem !important;
  }

  .fc .fc-toolbar-chunk:last-child .fc-button-group .fc-button, .fc .fc-toolbar-chunk:last-child .fc-button-group .fc-button:active,
  .fc-toolbar-title {
    font-size: 0.7rem !important;
  }
}

.fc-list-event-time, .fc-list-event-graphic {
  display: none !important;
}

.calendars-checkbox {
  .v-label {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
    opacity: var(--v-high-emphasis-opacity);
  }
}

.calendar-add-event-drawer {
  &.v-navigation-drawer {
    border-end-start-radius: 0.375rem;
    border-start-start-radius: 0.375rem;
  }
}

.calendar-date-picker {
  display: none;

  +.flatpickr-input {
    +.flatpickr-calendar.inline {
      border: none;
      box-shadow: none;

      .flatpickr-months {
        border-block-end: none;
      }
    }
  }
}

.fc-toolbar-title, .flatpickr-monthDropdown-months {
    text-transform: uppercase!important;
}
</style>

<style lang="scss" scoped>
.v-layout {
  overflow: visible !important;

  .v-card {
    overflow: visible;
  }
}
</style>
