<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@validators'
import { Vue3ColorPicker } from '@cyhnkckali/vue3-color-picker';
import '@cyhnkckali/vue3-color-picker/dist/style.css'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  color: {
    type: Object,
    required: false,
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'colorData',
])

const isFormValid = ref(false)
const refForm = ref()

const id = ref(0)
const name = ref('')
const color_ = ref('#FFFFFF')
const mode = ref('solid')
const isEdit = ref(false)

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Color': 'Agregar Color'
})

watchEffect(async() => {

    if (props.isDrawerOpen) {

        if (!(Object.entries(props.color).length === 0) && props.color.constructor === Object) {
            isEdit.value = true
            id.value = props.color.id
            name.value = props.color.name
            color_.value = props.color.color
            mode.value = props.color.is_gradient === 0 ? 'solid' : 'gradient'
        }
    }
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    name.value = ''
    color_.value = '#FFFFFF'
    isEdit.value = false
    mode.value = 'solid'
    id.value = 0
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      let formData = new FormData()

      formData.append('id', id.value)
      formData.append('name', name.value)
      formData.append('color', color_.value)
      formData.append('is_gradient', mode.value === 'solid' ? 0 : 1)

      emit('colorData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')
      emit('update:isDrawerOpen', false)

      closeNavigationDrawer()
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
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
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Name -->
              <VCol cols="12">
                <VTextField
                  v-model="name"
                  :rules="[requiredValidator]"
                  label="Nombre"
                />
              </VCol>

              <!-- Color -->
              <VCol cols="12">
                <VRadioGroup
                  v-model="mode"
                  inline
                >
                  <VRadio
                    label="Sólido"
                    value="solid"
                  />
                  <VRadio
                    label="Gradiente"
                    value="gradient"
                  />
                </VRadioGroup>
              </VCol>
              <VCol cols="7">
                <Vue3ColorPicker 
                  v-model="color_" 
                  type="HEX"
                  :mode="mode"
                  :key="mode"
                  :showColorList="false"
                  :showEyeDrop="true"
                  :showInputMenu="false"
                  :showPickerMode="false"
                  :showInputSet=false
                />
              </VCol>
              <VCol cols="5">
                <div 
                   :style="{ 
                      background: color_, 
                      width: '200px', 
                      height: '220px',
                      borderRadius: '10px',
                      border: 'thin solid rgba(var(--v-border-color), var(--v-border-opacity))'
                    }"
                  />
              </VCol>

              <!-- 👉 Submit and Cancel -->
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

<style scoped>
    .border-img {
        border: 1.8px solid rgba(var(--v-border-color), var(--v-border-opacity));
        border-radius: 6px;
    }

    .border-img .v-img__img--contain {
        padding: 10px;
    }
</style>
