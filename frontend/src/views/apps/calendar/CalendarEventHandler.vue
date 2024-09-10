<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { themeConfig } from '@themeConfig'
import { ref } from "vue"
import dayjs from 'dayjs';
import customParseFormat from 'dayjs/plugin/customParseFormat'

dayjs.extend(customParseFormat);

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

const refForm = ref()

const resolveStatusPayment = payment_state_id => {
  if (payment_state_id === 1)
    return { color: 'error' }
  if (payment_state_id === 2)
    return { color: 'success' }
  if (payment_state_id === 3)
    return { color: 'success' }
  if (payment_state_id === 4)
    return { color: 'primary' }
}

const resolveStatusEvent = state_id => {
  if (state_id === 4)
    return { color: 'error' , name: 'Pendiente'}
  if (state_id === 6)
    return { color: 'success' , name: 'Rechazado'}
  if (state_id === 7)
    return { color: 'primary' , name: 'Entregado'}
}

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

// 👉 Event
const event = ref(JSON.parse(JSON.stringify(props.event)))
const image = ref(null)
const isCupcake = ref([])
const flavor = ref(null)
const filling = ref(null)
const cake_size = ref(null)
const quantity = ref(1)
const date = ref(null)
const user = ref(null)
const address = ref(null)
const email = ref(null)
const phone = ref(null)
const isFile = ref(false)
const file = ref(false)
const price = ref(false)

watchEffect(fetchData)

async function fetchData() {

  if(event.value.extendedProps.order_detail) {
    console.log('event', event.value)
    image.value = event.value.extendedProps.order_detail.service.image === null ? '' : themeConfig.settings.urlStorage + event.value.extendedProps.order_detail.service.image
    date.value = event.value.extendedProps.order_detail.date
    price.value = event.value.extendedProps.order_detail.total

    const note = event.value.extendedProps.order_detail.order.billing.note === null ? '.' : '. (' + event.value.extendedProps.order_detail.order.billing.note + ').'
    
    if(event.value.extendedProps.order_detail.order.client) {

      user.value = event.value.extendedProps.order_detail.order.client.user.name + ' ' + event.value.extendedProps.order_detail.order.client.user.last_name
      email.value = event.value.extendedProps.order_detail.order.client.user.email

      address.value = 
        event.value.extendedProps.order_detail.order.address.address + ', ' + 
        event.value.extendedProps.order_detail.order.address.street + ', ' +
        event.value.extendedProps.order_detail.order.address.city + ', ' +
        event.value.extendedProps.order_detail.order.address.postal_code + ', ' +
        event.value.extendedProps.order_detail.order.address.province.name +
        note;
        
      phone.value = event.value.extendedProps.order_detail.order.address.phone
    } else {
      user.value =  event.value.extendedProps.order_detail.order.billing.name + ' ' +  event.value.extendedProps.order_detail.order.billing.last_name
      email.value = event.value.extendedProps.order_detail.order.billing.email

      address.value = 
        event.value.extendedProps.order_detail.order.shipping_address + ', ' + 
        event.value.extendedProps.order_detail.order.shipping_street + ', ' + 
        event.value.extendedProps.order_detail.order.shipping_city + ', ' + 
        event.value.extendedProps.order_detail.order.shipping_postal_code + ', ' + 
        event.value.extendedProps.order_detail.order.province.name +
        note;

      phone.value = event.value.extendedProps.order_detail.order.shipping_phone
    }

    isCupcake.value = event.value.extendedProps.order_detail.service.cupcakes.length > 0 ? true : false

    if(isCupcake.value) {
      flavor.value = event.value.extendedProps.order_detail.flavor.name
      filling.value = event.value.extendedProps.order_detail.filling.name
      cake_size.value = event.value.extendedProps.order_detail.cake_size.name
      quantity.value = event.value.extendedProps.order_detail.quantity 

      isFile.value = event.value.extendedProps.order_detail.order_file === null ? false : true
      file.value = event.value.extendedProps.order_detail.order_file === null ? false : themeConfig.settings.urlStorage + event.value.extendedProps.order_detail.order_file.image
    }
  }
  
}

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


const dialogModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

</script>

<template>
  <VNavigationDrawer
    v-if="event.extendedProps.order_detail"
    :model-value="props.isDrawerOpen"
    temporary
    location="end"
    width="420"
    class="scrollable-content"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- 👉 Header -->
    <div class="d-flex align-center pa-6 pb-1">
      <h3 class="text-h3">
        Pedido #{{ event.title }}
      </h3>

      <VSpacer />

      <VBtn
        v-if="$can('eliminar', 'calendario')"
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
      <VCard flat class="px-2">
        <VCardTitle>{{ event.extendedProps.order_detail.service.name }}</VCardTitle>
        <VCardText>
            <VRow>
              <!-- 👉 img -->
              <VCol cols="12" md="12" class="px-1 py-0">
                <VImg
                  :src="image"
                  :height="200"
                  aspect-ratio="1/1"
                  class="border-img"
                  cover
                />
              </VCol>

              <VCol cols="12" md="12" class="px-1 py-0 d-flex flex-column" v-if="isCupcake">
                <div>
                  <span class="font-weight-medium text-primary text-h6">Sabor:</span>
                  <span class="text-h6 text-disabled ms-2">{{ flavor }}</span></div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Relleno:</span>
                  <span class="text-h6 text-disabled ms-2">{{ filling }}</span></div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Tamaño:</span>
                  <span class="text-h6 text-disabled ms-2">{{ cake_size }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Cantidad:</span>
                  <span class="text-h6 text-disabled ms-2">{{ quantity }} {{ quantity > 1 ? 'Unidades' : 'Unidad' }}</span>
                </div>
              </VCol>

              <!-- 👉 details -->
              <VCol cols="12" class="px-1 py-0">
                <div>
                  <span class="font-weight-medium text-primary text-h6">Fecha de entrega:</span>
                  <span class="text-h6 text-disabled ms-2">{{ dayjs(date, 'YYYY-MM-DD HH:mm:ss').format('YYYY-MM-DD hh:mm A') }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Cliente:</span>
                  <span class="text-h6 text-disabled ms-2">{{ user }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Email:</span>
                  <span class="text-h6 text-disabled ms-2">{{ email }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Dirección:</span>
                  <span class="text-h6 text-disabled ms-2">{{ address }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Teléfono:</span>
                  <span class="text-h6 text-disabled ms-2">{{ phone }}</span>
                </div>
                <div>
                  <span class="font-weight-medium text-primary text-h6">Precio:</span>
                  <span class="text-h6 text-disabled ms-2">{{ (parseFloat(price)).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2, style: "currency", currency: 'COP' }) }}</span>
                </div>
              </VCol>

               <!-- 👉 img -->
               <VCol cols="12" md="12" class="px-1 py-0" v-if="isCupcake && isFile">
                  <span class="font-weight-medium text-primary text-h6">Archivo Personalizado:</span>
                  <VImg
                    :src="file"
                    :height="200"
                    aspect-ratio="1/1"
                    class="border-img"
                    cover
                  />
              </VCol>

              <!-- 👉 Service -->
              <VCol cols="12" class="px-1 py-1">
                <div>
                  <span class="font-weight-medium text-primary text-h6">Estado del pago:</span>
                  <VChip
                    label
                    class="ms-2"
                    :color="resolveStatusPayment(event.extendedProps.order_detail.order.payment.id)?.color"
                    >
                    {{ event.extendedProps.order_detail.order.payment.name }}
                  </VChip>
                </div>
              </VCol>

              <!-- 👉 State -->
              <VCol cols="12" class="px-1 py-1" v-if="event.extendedProps.order_detail.order.payment.id === 4 && event.extendedProps.state_id === 4">
                <span class="font-weight-medium text-primary text-h6">Estado del pedido:</span>
                <VSelect
                  v-model="event.extendedProps.state_id"
                  label="Estado"
                  :items="states"
                  item-value="id"
                  item-title="name"
                />                
              </VCol>

              <VCol cols="12" class="px-1 py-0" v-else>
                <span class="font-weight-medium text-primary text-h6">Estado del pedido:</span>
                <VChip
                    label
                    class="ms-2"
                    :color="resolveStatusEvent(event.extendedProps.state_id)?.color"
                    >
                    {{resolveStatusEvent(event.extendedProps.state_id)?.name}}
                  </VChip>           
              </VCol>
            </VRow>
        
        <!-- !SECTION -->
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

<style scoped>
  .border-img {
    border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
    border-radius: 6px;
  }

  .border-img .v-img__img--contain {
    padding: 10px;
  }

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
