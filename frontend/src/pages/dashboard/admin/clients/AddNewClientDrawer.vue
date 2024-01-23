<script setup>

import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  client: {
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
  genders: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'clientData',
])

const isFormValid = ref(false)
const refForm = ref()

const listProvincesByCountry = ref([])
const id = ref(0)
const client_country_id = ref('Colombia')
const countryOld_id = ref('Colombia')
const province_id = ref('')
const provinceOld_id = ref('')
const name = ref('')
const last_name = ref('')
const username = ref('')
const document = ref('')
const email = ref('')
const phone = ref('')
const address = ref('')
const birthday = ref('')
const gender_id = ref('')
const password = ref('')
const passwordConfirmation = ref()
const isNewPasswordVisible = ref(false) 
const isConfirmPasswordVisible = ref(false)
const isEdit = ref(false)

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: 'Y-m-d'
  }

  const currentDate = new Date();
  const year = currentDate.getFullYear();
  const month = String(currentDate.getMonth() + 1).padStart(2, '0');
  const day = String(currentDate.getDate()).padStart(2, '0');
  const formattedDate = `${year}-${month}-${day}`;

  config.maxDate = formattedDate

  return config
})

const getTitle = computed(() => {
  return isEdit.value ? 'Actualizar Cliente': 'Agregar Cliente'
})

const selectCountry = country => {
  if (country) {
    let _country = props.countries.find(item => item.name === country)
    client_country_id.value = _country.name
 
    province_id.value = null
    
    listProvincesByCountry.value = props.provinces.filter(item => item.country_id === _country.id)
  }
}

watchEffect(async() => {
  selectCountry(countryOld_id.value)

  if (!(Object.entries(props.client).length === 0) && props.client.constructor === Object){
    province_id.value = props.client.user.user_detail.province.id ?? ''
    gender_id.value = props.client.gender_id
  }

    if (props.isDrawerOpen) {
        let data = { limit: -1 }

        if (!(Object.entries(props.client).length === 0) && props.client.constructor === Object) {
            isEdit.value = true
            id.value = props.client.id
            name.value = props.client.user.name
            last_name.value = props.client.user.last_name
            username.value = props.client.user.username
            document.value = props.client.user.user_detail.document
            email.value = props.client.user.email
            phone.value = props.client.user.user_detail.phone
            address.value = props.client.user.user_detail.address
            birthday.value = props.client.birthday
            
            password.value = props.client.password
            passwordConfirmation.value = props.client.password
            countryOld_id.value = props.client.user.user_detail.province.country.name
            client_country_id.value = props.client.user.user_detail?.province.country.name

            isNewPasswordVisible.value = false 
            isConfirmPasswordVisible.value = false 

            province_id.value = props.client.user.user_detail?.province.name
            provinceOld_id.value = props.client.user.user_detail?.province_id
      }
    }
})

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

const getGenders = computed(() => {
  return props.genders.map((gender) => {
    return {
      title: gender.name,
      value: gender.id,
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
    username.value = null
    document.value = null
    email.value = null
    phone.value = null
    address.value = null
    birthday.value = null
    gender_id.value = null
    client_country_id.value = 'Colombia'
    countryOld_id.value = 'Colombia'
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
      formData.append('username', username.value)
      formData.append('document', document.value)
      formData.append('phone', phone.value)
      formData.append('address', address.value)
      formData.append('birthday', birthday.value)
      formData.append('gender_id', gender_id.value)
      formData.append('province_id', (Number.isInteger(province_id.value)) ? province_id.value : provinceOld_id.value)
      formData.append('is_client', true)

      emit('clientData', { data: formData, id: id.value }, isEdit.value ? 'update' : 'create')

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

              <!-- 👉 User Name -->
              <VCol cols="6">
                <VTextField
                  v-model="username"
                  :rules="[requiredValidator]"
                  label="Usuario"
                />
              </VCol>

              <!-- 👉 Document -->
              <VCol cols="6">
                <VTextField
                  v-model="document"
                  :rules="[requiredValidator, phoneValidator]"
                  label="Documento de Identidad"
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
                  v-model="client_country_id"
                  label="País"
                  :rules="[requiredValidator]"
                  :items="props.countries"
                  item-title="name"
                  item-value="name"
                  :menu-props="{ maxHeight: '200px' }"
                  @update:model-value="selectCountry"
                >
                  <template
                    v-if="client_country_id"
                    #prepend
                    >
                    <VAvatar
                      start
                      style="margin-top: -8px;"
                      size="36"
                      :image="getFlagCountry(client_country_id)"
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

              <!-- 👉 Birthday -->
               <VCol cols="6" >
                    <AppDateTimePicker
                        :key="JSON.stringify(startDateTimePickerConfig)"
                        v-model="birthday"
                        :rules="[requiredValidator]"
                        label="Fecha de Cumpleaños"
                        :config="startDateTimePickerConfig"
                        />
                </VCol>

              <!-- 👉 Gender -->
              <VCol cols="6">
                <v-autocomplete
                  v-model="gender_id"
                  label="Genero"
                  :rules="[requiredValidator]"
                  :items="getGenders"
                  :menu-props="{ maxHeight: '200px' }"
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
