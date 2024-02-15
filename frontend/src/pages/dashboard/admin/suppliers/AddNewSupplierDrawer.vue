<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  supplier: {
    type: Object,
    required: false
  },
  countries: {
    type: Object,
    required: true
  },
  provinces: {
    type: Object,
    required: true
  },
  documentTypes: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'supplierData',
])

const isFormValid = ref(false)
const refForm = ref()
const currentTab = ref('tab-1')

const listProvincesByCountry = ref([])
const id = ref(0)
const supplier_country_id = ref('Colombia')
const countryOld_id = ref('Colombia')
const province_id = ref('')
const name = ref('')
const last_name = ref('')
const main_document = ref('')
const email = ref('')
const phone = ref('')
const address = ref('')
const password = ref('')
const company_name = ref('')
const document_type_id = ref('')
const file_nit = ref([])
const file_rut = ref([])
const file_account = ref([])
const ncc = ref([])
const type_account = ref('1')
const name_bank = ref('')
const phone_contact = ref('')
const bank_account = ref('')
const passwordConfirmation = ref()
const isNewPasswordVisible = ref(false) 
const isConfirmPasswordVisible = ref(false)
const isEdit = ref(false)

const accountTypes = [
  {
    icon: {
      icon: 'mdi-cash-multiple',
      size: '40',
    },
    title: 'Cuenta Corriente',
    value: '1',
  },
  {
    icon: {
      icon: 'tabler-pig-money',
      size: '40',
    },
    title: 'Cuenta de Ahorros',
    value: '2',
  },
]

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Proveedor': 'Agregar Proveedor'
})

const selectCountry = country => {
  if (country) {
    let _country = props.countries.find(item => item.name === country)
    supplier_country_id.value = _country.name
 
    province_id.value = null
    
    listProvincesByCountry.value = props.provinces.filter(item => item.country_id === _country.id)
  }
}

watchEffect(async() => {
  selectCountry(countryOld_id.value)

  if (!(Object.entries(props.supplier).length === 0) && props.supplier.constructor === Object){
    province_id.value = props.supplier.user.user_detail.province.id ?? ''
  }

    if (props.isDrawerOpen) {
        let data = { limit: -1 }

        if (!(Object.entries(props.supplier).length === 0) && props.supplier.constructor === Object) {
            isEdit.value = true
            id.value = props.supplier.id
            name.value = props.supplier.user.name
            last_name.value = props.supplier.user.last_name
            main_document.value = props.supplier.document.main_document
            email.value = props.supplier.user.email
            phone.value = props.supplier.user.user_detail.phone
            address.value = props.supplier.user.user_detail.address
            
            password.value = props.supplier.password
            passwordConfirmation.value = props.supplier.password
            countryOld_id.value = props.supplier.user.user_detail.province.country.name
            
            isNewPasswordVisible.value = false 
            isConfirmPasswordVisible.value = false 
        }
    }
})

const getDocumentTypes = computed(() => {
  return props.documentTypes.map((documentType) => {
    return {
      title: '(' + documentType.code + ') - ' + documentType.name,
      value: documentType.id,
    }
  })
})

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    name.value = null
    last_name.value = null
    main_document.value = null
    file_nit.value = null
    file_rut.value = null
    file_account.value = []
    type_account.value = '1'
    email.value = null
    phone.value = null
    address.value = null
    bank_account.value = null
    supplier_country_id.value = null
    province_id.value = null
    password.value = null
    passwordConfirmation.value = null
    isNewPasswordVisible.value = false
    isConfirmPasswordVisible.value = false
  
    isEdit.value = false 
    id.value = 0
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      let formData = new FormData()

      if (!isEdit.value)
        formData.append('email', email.value)

      formData.append('name', name.value)
      formData.append('last_name', last_name.value)
      formData.append('main_document', main_document.value)
      formData.append('phone', phone.value)
      formData.append('address', address.value)
      formData.append('province_id', province_id.value)
      formData.append('bank_account', bank_account.value)
      formData.append('is_supplier', true)

      emit('supplierData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')

      closeNavigationDrawer()
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const getFlagCountry = country => {
  let val = props.countries.find(item => {
    return item.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === country.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  })

  if(val)
    return 'https://hatscripts.github.io/circle-flags/flags/'+val.iso.toLowerCase()+'.svg'
  else
    return ''
}

</script>

<template>
  <VNavigationDrawer
    temporary
    :width="700"
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
            <VTabs
              v-model="currentTab"
              grow
              stacked
            >
              <VTab>
                <VIcon
                  icon="mdi-domain"
                  class="mb-2"
                />
                <span>Empresa</span>
              </VTab>

              <VTab>
                <VIcon
                  icon="mdi-bank"
                  class="mb-2"
                />
                <span>Datos Bancarios</span>
              </VTab>

              <VTab>
                <VIcon
                  icon="mdi-account-tie"
                  class="mb-2"
                />
                <span>Contacto</span>
              </VTab>
            </VTabs>


            <VCardText>
              <VWindow v-model="currentTab">
                <!-- company -->
                <VWindowItem>
                  <VRow>
                    <!-- Company Name -->
                    <VCol cols="12">
                      <VTextField
                        v-model="company_name"
                        :rules="[requiredValidator]"
                        label="Empresa"
                      />
                    </VCol>

                    <!-- 👉 Document Types -->
                    <VCol cols="6">
                      <VAutocomplete
                        v-model="document_type_id"
                        label="Tipo de Documento"
                        :rules="[requiredValidator]"
                        :items="getDocumentTypes"
                        :menu-props="{ maxHeight: '200px' }"
                      />
                    </VCol>

                    <!-- 👉 Document -->
                    <VCol cols="6">
                      <VTextField
                        v-model="main_document"
                        :rules="[requiredValidator, phoneValidator]"
                        label="Documento"
                      />
                    </VCol>

                    <!-- NIT -->
                    <VCol cols="6">
                      <VFileInput
                        label="NIT"
                        prepend-icon="tabler-paperclip"
                      >
                        <template #selection="{ fileNames1 }">
                          <template
                            v-for="fileName in fileNames1"
                            :key="fileName">
                            <VChip
                              label
                              size="small"
                              variant="outlined"
                              color="primary"
                              class="me-2">
                              {{ fileName }}
                            </VChip>
                          </template>
                        </template>
                      </VFileInput>
                    </VCol>

                    <!-- RUT -->
                    <VCol cols="6">
                      <VFileInput
                        label="RUT"
                        prepend-icon="tabler-paperclip"
                      >
                        <template #selection="{ fileNames2 }">
                          <template
                            v-for="fileName in fileNames2"
                            :key="fileName">
                            <VChip
                              label
                              size="small"
                              variant="outlined"
                              color="primary"
                              class="me-2">
                              {{ fileName }}
                            </VChip>
                          </template>
                        </template>
                      </VFileInput>
                    </VCol>

                    <!-- 👉 NCC -->
                    <VCol cols="12">
                      <VTextField
                        v-model="ncc"
                        :rules="[requiredValidator]"
                        label="Número de la Cámara de Comercio"
                      />
                    </VCol>
                  </VRow>
                </VWindowItem>
                <!-- bank -->
                <VWindowItem>
                  <VRow>
                    <!-- Type Account  -->
                    <VCol cols="12" class="d-flex">
                      <CustomRadiosWithIcon
                        v-model:selected-radio="type_account"
                        :radio-content="accountTypes"
                        :grid-column="{ sm: '6', cols: '12' }"
                      />
                    </VCol>

                    <!-- 👉 Name Bank-->
                    <VCol cols="12">
                      <VTextField
                        v-model="name_bank"
                        :rules="[requiredValidator]"
                        label="Nombre del Banco"
                      />
                    </VCol>

                    <!-- 👉 Name Bank-->
                    <VCol cols="6">
                      <VTextField
                        v-model="bank_account"
                        :rules="[requiredValidator]"
                        label="Nro. de Cuenta"
                      />
                    </VCol>

                    <!-- ACCOUNT -->
                    <VCol cols="6">
                      <VFileInput
                        label="Certificación Bancaria"
                        prepend-icon="tabler-paperclip"
                      >
                        <template #selection="{ fileNames3 }">
                          <template
                            v-for="fileName in fileNames3"
                            :key="fileName">
                            <VChip
                              label
                              size="small"
                              variant="outlined"
                              color="primary"
                              class="me-2">
                              {{ fileName }}
                            </VChip>
                          </template>
                        </template>
                      </VFileInput>
                    </VCol>
                  </VRow>
                </VWindowItem>
                <!-- contact -->
                <VWindowItem>
                  <VRow>
                    <!-- 👉 Name -->
                    <VCol cols="6">
                      <VTextField
                        v-model="name"
                        :rules="[requiredValidator]"
                        label="Nombre"
                      />
                    </VCol>

                    <!-- 👉 Last Name -->
                    <VCol cols="6">
                      <VTextField
                        v-model="last_name"
                        :rules="[requiredValidator]"
                        label="Apellido"
                      />
                    </VCol>

                    <!-- 👉 Email -->
                    <VCol cols="6">
                      <VTextField
                        :rules="[emailValidator, requiredValidator]"
                        v-model="email"
                        label="Email"
                        :disabled="isEdit"
                      />
                    </VCol>

                    <!-- 👉 Phone -->
                    <VCol cols="6">
                      <VTextField
                        v-model="phone"
                        :rules="[requiredValidator, phoneValidator]"
                        label="Teléfono"
                      />
                    </VCol>

                    <!-- 👉 Paises -->
                    <VCol cols="6">
                      <VAutocomplete
                        v-model="supplier_country_id"
                        label="País"
                        :rules="[requiredValidator]"
                        :items="props.countries"
                        item-title="name"
                        item-value="name"
                        :menu-props="{ maxHeight: '200px' }"
                        @update:model-value="selectCountry"
                      >
                        <template
                          v-if="supplier_country_id"
                          #prepend
                          >
                          <VAvatar
                            start
                            style="margin-top: -8px;"
                            size="36"
                            :image="getFlagCountry(supplier_country_id)"
                          />
                        </template>
                      </VAutocomplete>
                    </VCol>

                    <!-- 👉 Provincias -->
                    <VCol cols="6">
                      <v-autocomplete
                        v-model="province_id"
                        label="Provincias"
                        :rules="[requiredValidator]"
                        :items="getProvinces"
                        :menu-props="{ maxHeight: '200px' }"
                      />
                    </VCol>

                    <!-- 👉 Address -->
                    <VCol cols="12">
                      <VTextarea
                        v-model="address"
                        rows="4"
                        label="Dirección"
                        :rules="[requiredValidator]"
                      />
                    </VCol>
                  </VRow>
                </VWindowItem>
              </VWindow>
            </VCardText>
            
            <VRow>
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
