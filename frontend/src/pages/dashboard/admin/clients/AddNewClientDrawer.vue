<script setup>
import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'


import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import { confirmedValidator, passwordValidator, requiredValidator, emailValidator } from '@/@core/utils/validators'
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
const provinces = ref([])
const listProvincesByCountry = ref([])
const searchQuery = ref('')
const rowPerPage = ref(10)
const currentPage = ref(1)

const id = ref(0)
const client_country_id = ref('')
const province_id = ref('')
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

  return config
})

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
            name.value = props.client.user.name
            last_name.value = props.client.user.last_name
            username.value = props.client.user.username
            document.value = props.client.user.user_detail.document
            email.value = props.client.user.email
            phone.value = props.client.user.user_detail.phone
            address.value = props.client.user.user_detail.address
            birthday.value = props.client.birthday
            gender_id.value = props.client.gender_id
            password.value = props.client.password
            passwordConfirmation.value = props.client.password

            client_country_id.value = props.client.user.user_detail.province.country.id
            province_id.value = props.client.user.user_detail.province.id

            
            isNewPasswordVisible.value = false 
            isConfirmPasswordVisible.value = false 
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

  await provincesStores.fetchProvinces(data)
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
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

const selectCountry = country => {
  if (country) {
    let _country = countries.value.find(item => item.id === country)
    client_country_id.value = _country.id
 
    province_id.value = ''
    
    listProvincesByCountry.value = provinces.value.filter(item => item.country_id === _country.id)
  }
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()

    name.value = ''  //name.value = 'Freddy'
    last_name.value = ''  //last_name.value = 'Castro'
    username.value = ''  //username.value = 'fcastro'
    document.value = ''  //document.value = '15989101'
    email.value = ''  //email.value = 'fcastro@gmail.com'
    phone.value = ''  //phone.value = '04166097023'
    address.value = ''  //address.value = 'tal tal tal'
    birthday.value = ''  //birthday.value = '16-10-1982'
    gender_id.value = ''  //gender_id.value = '1'
    client_country_id.value = ''
    province_id.value = ''
    password.value = '' //password.value = 'As1dddrrrff'
    passwordConfirmation.value = '' //passwordConfirmation.value = 'As1dddrrrff'
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
      const roles = ['Cliente'] //ref({0: 'Cliente'})

      formData.append('name', name.value)
      formData.append('last_name', last_name.value)
      formData.append('username', username.value)
      formData.append('document', document.value)
      formData.append('email', email.value)
      formData.append('phone', phone.value)
      formData.append('address', address.value)
      formData.append('birthday', birthday.value)
      formData.append('gender_id', gender_id.value)
      // formData.append('client_country_id', client_country_id.value)
      formData.append('province_id', province_id.value)
      formData.append('password', password.value) 
      // formData.append('roles', roles ) 

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
                  :rules="[requiredValidator]"
                  label="Documento de Identidad"
                />
              </VCol>

              <!-- 👉 Email -->
              <VCol cols="6">
                <VTextField
                  v-model="email"
                  :rules="[requiredValidator, emailValidator]"
                  label="Email"
                />
              </VCol>

              <!-- 👉 Phone -->
              <VCol cols="6">
                <VTextField
                  v-model="phone"
                  :rules="[requiredValidator]"
                  label="Teléfono"
                />
              </VCol>

              <!-- 👉 Paises -->
              <VCol cols="6">
                <v-autocomplete
                  v-model="client_country_id"
                  label="Países"
                  :rules="[requiredValidator]"
                  :items="getCountries"
                  :menu-props="{ maxHeight: '200px' }"
                  @update:model-value="selectCountry"
                />
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
                <VTextField
                  v-model="gender_id"
                  :rules="[requiredValidator]"
                  label="Genero"
                />
              </VCol>

              <!-- 👉 Contraseña -->
              <VCol cols="6">
                <VTextField
                  v-model="password"
                  label="Nueva contraseña"
                  :type="isNewPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isNewPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, passwordValidator]"
                  @click:append-inner="isNewPasswordVisible = !isNewPasswordVisible"
                />
              </VCol>
              <!-- 👉 Confirmar Contraseña -->
              <VCol cols="6">
                <VTextField
                  v-model="passwordConfirmation"
                  label="Confirmar Contraseña"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, confirmedValidator(passwordConfirmation, password)]"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
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
