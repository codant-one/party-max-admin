<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@/@core/utils/validators';
import { nextTick } from 'vue';

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  coupon: {
    type: Object,
    required: false
  },
  clients: {
    type: Array,
    required: false
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'couponData',
])

const isFormValid = ref(false)
const form = ref()

const id = ref(0)
const code = ref('')
const description = ref('')
const is_percentage = ref(false)
const amount = ref(0)
const min_purchase = ref(0)
const expiration_date = ref('')
const client = ref(null)
const order_id = ref(null)
const is_used = ref(0)

const isEdit = ref(false)
const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Cupón': 'Agregar Cupón'
})

const closeNavigationDrawer = () => {
  isEdit.value = false
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    form.value?.reset()
    form.value?.resetValidation()

    code.value = ''
    description.value = ''
    is_percentage.value = false
    amount.value = 0
    min_purchase.value = 0
    expiration_date.value = ''
    client.value = null
    order_id.value = null
    is_used.value = 0

  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const onSubmit = () => {
  form.value.validate().then(({ valid }) => {
    if (valid) {
      let formData = new FormData()
      
      formData.append('code', code.value)
      formData.append('description', description.value)
      formData.append('is_percentage', is_percentage.value ? 1 : 0)
      formData.append('amount', amount.value)
      formData.append('min_purchase', min_purchase.value)
      formData.append('expiration_date', expiration_date.value)
      formData.append('client_id', client.value.id)
      formData.append('order_id', order_id.value)
      formData.append('is_used', is_used.value)

      emit('couponData', {data: formData, id: id.value}, isEdit.value ? 'update' : 'create')

      closeNavigationDrawer()
    }
  })
}

</script>

<template>
  <VNavigationDrawer
    temporary
    :width="550"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
     <div class="d-flex align-center pa-6 pb-1">
      <h6 class="text-h6">
        {{ getTitle }}
      </h6>

      <VSpacer />

      <!-- 👉 Close btn -->
      <VBtn
        variant="tonal"
        color="default"
        icon
        size="32"
        class="rounded"
        @click="closeNavigationDrawer"
      >
        <VIcon
          size="18"
          icon="tabler-x"
        />
      </VBTn>
     </div>

     <VDivider class="mt-4"/>

     <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <VForm
            ref="form"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <VCol cols="6">
                <VTextField
                  v-model="code"
                  :rules="[requiredValidator]"
                  label="Código"
                />
              </VCol>

              <VCol cols="6">
                <VTextField
                  v-model="description"
                  :rules="[requiredValidator]"
                  label="Descripción"
                />
              </VCol>
              
              <VCol cols="6">
                <VTextField
                  v-model="amount"
                  label="Monto"
                  type="number"
                  :rules="[
                    requiredValidator,
                    v => (!is_percentage && v > 500000) ? 'El monto máximo es 500000' : true,
                    v => (is_percentage && v > 100) ? 'El porcentaje máximo es 100%' : true,
                  ]"
                  :max="is_percentage ? 100 : 500000"
                />
              </VCol>

              <VCol cols="6">
                <VTextField
                  v-model="min_purchase"
                  :rules="[requiredValidator]"
                  label="Monto mínimo de compra"
                  type="number"
                  :disabled="is_percentage ? true : false"
                />
              </VCol>

              <VCol cols="6">
                <VTextField
                  v-model="expiration_date"
                  :rules="[requiredValidator]"
                  label="Fecha de expiración"
                  type="date"
                />
              </VCol>

              <VCol cols="6">
                <VCheckbox
                  v-model="is_percentage"
                  label="Es porcentaje?"
                />
              </VCol>
  
              <VCol cols="12">
                <v-autocomplete
                  v-model="client"
                  :items="props.clients"
                  :item-title="item => item && item.user ? `${item.user.name} ${item.user.last_name ?? ''}` : 'Sin nombre'"
                  item-value="id"
                  :rules="[requiredValidator]"
                  label="Cliente"
                  :menu-props="{ maxHeight: '200px' }"
                  return-object
                />
              </VCol>

              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  {{ isEdit ? 'Actualizar': 'Agregar' }}
                </VBtn>

                <VBtn
                  type="reset"
                  variant="tonal"
                  color="secondary"
                  @click="closeNavigationDrawer"
                >
                  Cancelar
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
     </PerfectScrollbar>
  </VNavigationDrawer>
</template>


