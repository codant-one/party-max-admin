<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { requiredValidator } from '@validators'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  client: {
    type: Object,
    required: false,
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'clientData',
])

const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()

const isFormValid = ref(false)
const refForm = ref()

const countries = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)

const id = ref(0)
const client_country_id = ref('')
const title = ref('')
const description = ref('')
const isEdit = ref(false)

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Cliente': 'Agregar Cliente'
})

watchEffect(async() => {
  fetchData()

    if (props.isDrawerOpen) {
        let data = { limit: -1 }

        if (!(Object.entries(props.client).length === 0) && props.client.constructor === Object) {
            isEdit.value = true
            id.value = props.client.id
            title.value = props.client.title
            description.value = props.client.description
            client_country_id.value = props.client.client_country_id
        }
    }
})

async function fetchData() {

  let data = {
    search: searchQuery.value,
    orderByField: 'id',
    orderBy: 'desc',
    limit: rowPerPage.value,
    page: currentPage.value
  }

  await countriesStores.fetchCountries(data)
  countries.value = countriesStores.getCountries

  await provincesStores.fetchCountries(data)
  provinces.value = provincesStores.getProvinces

}

const getCountries = computed(() => {
  return countries.value.map((country) => {
    return {
      title: country.name,
      value: country.id,
    }
  })
})

const getProvinces = computed(() => {
  return provinces.value.map((country) => {
    return {
      title: country.name,
      value: country.id,
    }
  })
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    title.value = ''
    description.value = ''
    client_country_id.value = ''
    isEdit.value = false
    id.value = 0
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      let formData = new FormData()

      formData.append('id', id.value)
      formData.append('title', title.value)
      formData.append('description', description.value)
      formData.append('client_country_id', client_country_id.value)

      emit('clientData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')
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
              <!-- 👉 Paises -->
              <VCol cols="12">
                <v-autocomplete
                  v-model="client_country_id"
                  label="Países"
                  :rules="[requiredValidator]"
                  :items="getCountries"
                  :menu-props="{ maxHeight: '200px' }"
                />
              </VCol>

              <!-- 👉 Title -->
              <VCol cols="12">
                <VTextField
                  v-model="title"
                  :rules="[requiredValidator]"
                  label="Titulo"
                />
              </VCol>

              <!-- 👉 description -->
              <VCol cols="12">
                <VTextarea
                  v-model="description"
                  rows="4"
                  label="Descripción"
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
