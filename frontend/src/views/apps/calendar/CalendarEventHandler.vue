<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { VForm } from 'vuetify/components'
import { useEventsStore } from '@/stores/useEvents'
import { ref } from "vue"
import dayjs from 'dayjs';

import {
  requiredValidator
} from '@validators'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  event: {
    type: null,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'addEvent',
  'updateEvent',
  'removeEvent',
])

const store = useEventsStore()
const refForm = ref()

const guestsOptions = ref([])
const states = ref([
  {
    id: 4,
    name: 'Pendiente'
  },
  {
    id: 7,
    name: 'Entregado'
  } 
])

const listServicesByCategory = ref([])

watchEffect(fetchData)

async function fetchData() {

  guestsOptions.value = []

  await store.getUsers()

  store.getUsersArray.forEach(element =>{
    guestsOptions.value.push({
      id: element.id,
      name: element.user_detail.store_name ?? (element.supplier?.company_name ?? (element.name + ' ' + (element.last_name ?? '')))
    })
  })

}

const getServices = computed(() => {
  return listServicesByCategory.value.map((state) => {
    return {
      title: state.name,
      value: state.id,
    }
  })
})

const selectCategory = (category) => {
  if (category) {
    listServicesByCategory.value = store.availableServices.filter(item => item.category === category)
  }
}

const copy = () =>{
  event.value.extendedProps.description = event.value.title
}

// 👉 Event
const event = ref(JSON.parse(JSON.stringify(props.event)))

const resetEvent = async () => {
  event.value = JSON.parse(JSON.stringify(props.event))

  if(event.value.end !== '')
    event.value.end = dayjs(event.value.end).subtract(1, 'day').toISOString()
  

  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

watch(() => props.isDrawerOpen, resetEvent)

const removeEvent = () => {
  emit('removeEvent', event.value.id)

  // Close drawer
  emit('update:isDrawerOpen', false)
}

const handleSubmit = () => {

  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      event.value.delta = 0
      // If id exist on id => Update event
      if ('id' in event.value) 
        emit('updateEvent', event.value)
      // Else => add new event
      else
        emit('addEvent', event.value)

      // Close drawer
      emit('update:isDrawerOpen', false)
    }
  })
}

// 👉 Form
const onCancel = () => {

  // Close drawer
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
    resetEvent()
  })
}

const dateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: 'Y-m-d'
  }

  if (event.value.start)
    config.minDate = event.value.start
  
  return config
})

const dialogModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="420"
    class="scrollable-content"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- 👉 Header -->
    <div class="d-flex align-center pa-6 pb-1">
      <h6 class="text-h6" v-if="$can('editar','calendario')">
        {{ event.id ? 'Actualizar' : 'Agregar' }} Agenda
      </h6>
      <h6 class="text-h6" v-else>Agenda</h6>

      <VSpacer />

      <VBtn
        v-if="$can('eliminar','calendario')"
        v-show="event.id"
        icon
        variant="tonal"
        size="32"
        class="rounded me-3"
        color="default"
        @click="removeEvent"
      >
        <VIcon
          size="18"
          icon="tabler-trash"
        />
      </VBtn>

      <VBtn
        variant="tonal"
        color="default"
        icon
        size="32"
        class="rounded"
        @click="onCancel"
      >
        <VIcon
          size="18"
          icon="tabler-x"
        />
      </VBTn>
    </div>

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- SECTION Form -->
          <VForm
            ref="refForm"
            @submit.prevent="handleSubmit"
          >
            <VRow>
              <!-- 👉 Title -->
              <VCol cols="12" md="11">
                <VTextField
                  v-model="event.title"
                  label="Título"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12" md="1" class="px-0">
                <VBtn
                  icon
                  size="x-small"
                  color="secondary"
                  variant="text">            
                  <VIcon size="22" icon="tabler-copy" @click="copy()"/>
                  <VTooltip activator="parent" location="top">
                    Copiar
                  </VTooltip>
                </VBtn>
              </VCol>

              <!-- 👉 Category -->
              <VCol cols="12">
                <VAutocomplete
                  v-model="event.extendedProps.calendar"
                  label="Categorías"
                  clearable
                  :rules="[requiredValidator]"
                  :items="store.availableCalendars"
                  :item-title="item => item.label"
                  :item-value="item => item.label"
                  @update:model-value="selectCategory(event.extendedProps.calendar)"
                />
              </VCol>

              <!-- 👉 Service -->
              <VCol cols="12">
                <VAutocomplete
                  v-model="event.extendedProps.service_id"
                  label="Servicios"
                  clearable
                  :rules="[requiredValidator]"
                  :items="getServices"
                />
              </VCol>

              <!-- 👉 State -->
              <VCol cols="12">
                <VSelect
                  v-model="event.extendedProps.state_id"
                  label="Estado"
                  :items="states"
                  item-value="id"
                  item-title="name"
                />                
              </VCol>

              <!-- 👉 End date -->
              <VCol cols="12">
                <AppDateTimePicker
                  :key="JSON.stringify(dateTimePickerConfig)"
                  v-model="event.end"
                  :rules="[requiredValidator]"
                  label="Fecha"
                  :config="dateTimePickerConfig"
                />
              </VCol>

              <!-- 👉 End date -->

              <!-- 👉 Description -->
              <VCol cols="12">
                <VTextarea
                  v-model="event.extendedProps.description"
                  :counter="500"
                  :rules="[v => (v || '' ).length <= 500 || 'Descripción: Máx. 500 caracteres.']"
                  rows="6"
                  label="Descripción"
                />
              </VCol>

              <!-- 👉 Form buttons -->
              <VCol cols="12">
                <VBtn
                  variant="tonal"
                  color="secondary"
                  class="me-3"
                  @click="onCancel"
                >
                  Cancelar
                </VBtn>
                <VBtn
                  v-if="$can('editar','calendario') && $can('crear','calendario')"
                  type="submit"
                >
                  {{ event.id ? 'Actualizar' : 'Agregar' }}
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        <!-- !SECTION -->
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

<style scoped>
  .p-0  {
    padding: 0 !important
  }

  .mt--2 {
    margin-top: -10px;
  }

  .button-color {
    height: 40px !important; 
    /* margin: 1px !important; */
    font-size: 10px !important;
    padding: 5px !important;
  } 

  .chip-radius {
    border-radius: 5px;
  }

  .w-40 {
    width: 40% !important;
  }
  .w-31 {
    width: 31% !important;
  }

  @media (max-width: 991px) {
    .w-40 {
      width: 100% !important;
    }
    .w-31 {
      width: 100% !important;
    }
  }
  
</style>
