<script setup>

import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { useUsersStores } from '@/stores/useUsers'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'

const props = defineProps({
  rolesList: {
    type: Object,
    required: true
  }

})

const emit = defineEmits([
  'close',
  'alert',
  'data'
])

const usersStores = useUsersStores()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()

const refFormCreate = ref()

const isUserCreateDialog = ref(false)
const isPasswordVisible = ref(false)
const email = ref('')
const name = ref('')
const password = ref('')
const last_name = ref('')
const phone = ref('')
const address = ref('')
const username = ref('')
const document = ref('')
const province_id = ref('')
const country_id = ref('')
const assignedRoles = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])

const getCountries = computed(() => {
  return listCountries.value.map((country) => {
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

onMounted(async () => {

    await countriesStores.fetchCountries();
    await provincesStores.fetchProvinces();

    loadCountries()
    loadProvinces()

})

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.id === country)
    country_id.value = _country.id
 
    province_id.value = ''
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

const closeUserCreateDialog  = function(){
  isUserCreateDialog.value = false

  nextTick(() => {
    refFormCreate.value?.reset()
    refFormCreate.value?.resetValidation()
    email.value = ''
    name.value = ''
    password.value = ''
    last_name.value = ''
    phone.value = ''
    address.value = ''
    username.value = ''
    document.value = ''
    province_id.value = ''
    country_id.value = ''
    assignedRoles.value = []
  })
}

const onSubmitCreate = () => {
  refFormCreate.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {
      
      let data = {
            name: name.value,
            email: email.value,
            password: password.value,
            last_name: last_name.value,
            phone: phone.value,
            address: address.value,
            username: username.value,
            document: document.value,
            province_id: province_id.value,
            roles: assignedRoles.value
        }

      usersStores.addUser(data)
        .then(response => {
          closeUserCreateDialog()

          window.scrollTo(0, 0)
          
          advisor.value.show = true
          advisor.value.type = 'success'
          advisor.value.message = 'Usuario Creado!'
          
          emit('alert', advisor)
          emit('data')
          emit('close')

          nextTick(() => {
            refFormCreate.value?.reset()
            refFormCreate.value?.resetValidation()
            email.value = ''
            name.value = ''
            password.value = ''
            last_name.value = ''
            phone.value = ''
            address.value = ''
            username.value = ''
            document.value = ''
            province_id.value = ''
            country_id.value = ''
            assignedRoles.value = []
          })

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)
        }).catch(error => {

          closeUserCreateDialog()
          window.scrollTo(0, 0)

          advisor.value.show = true
          advisor.value.type = 'error'
          
          if (error.feedback === 'params_validation_failed') {
            if(error.message.hasOwnProperty('email'))
              advisor.value.message = error.message.email[0]
            else if(error.message.hasOwnProperty('username'))
              advisor.value.message = error.message.username[0]
          } else {
            advisor.value.message = 'Ocurrió un error, intente nuevamente o contacte con el administrador...!'
          }

          emit('alert', advisor)
          emit('data')
          emit('close')

          setTimeout(() => {
            advisor.value.show = false
            advisor.value.type = ''
            advisor.value.message = ''
            emit('alert', advisor)
          }, 5000)   
        })
    }
  })
}
</script>

<template>
  <!-- 👉 CREATE USER -->
  <VDialog
    v-model="isUserCreateDialog"
    max-width="600"
    persistent
    >
    <template #activator="{ props }">
      <VBtn
        v-if="$can('crear','usuarios')"
        v-bind="props"
        prepend-icon="tabler-plus"
        >
        Crear usuario
      </VBtn>
    </template>

    <DialogCloseBtn @click="closeUserCreateDialog " />

    <VCard title="Crear usuario">
      <VCardText>
        <VForm
          ref="refFormCreate"
          @submit.prevent="onSubmitCreate"
          >
          <VCardText>
            <VRow>
              <VCol md="6" cols="12">
                <VTextField
                  v-model="name"
                  label="Nombres"
                  :rules="[requiredValidator]"
                  />
              </VCol>
              <VCol md="6" cols="12">
                <VTextField
                  v-model="last_name"
                  label="Apellidos"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol cols="12" md="12">
                <VTextField
                  v-model="email"
                  label="E-mail"
                  type="email"
                  :rules="[requiredValidator,emailValidator]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="username"
                  label="Username"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="password"
                  label="Contraseña"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :rules="[requiredValidator]"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>
              <VCol cols="12" md="6" >
                <VTextField
                  v-model="phone"
                  Label="Telefono"
                  placeholder="+(XX) XXXXXXXXX"
                  :rules="[requiredValidator, phoneValidator]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="address"
                  label="Direccion"
                  :rules="[requiredValidator]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <v-autocomplete
                  v-model="country_id"
                  label="País"
                  :rules="[requiredValidator]"
                  :items="getCountries"
                  :menu-props="{ maxHeight: '200px' }"
                  @update:model-value="selectCountry"
                />
              </VCol>
              <VCol cols="12" md="6">
                <v-autocomplete
                  v-model="province_id"
                  label="Estado"
                  :rules="[requiredValidator]"
                  :items="getProvinces"
                  :menu-props="{ maxHeight: '200px' }"
                />
              </VCol>
              <VCol cols="12" md="6">
                <VTextField
                  v-model="document"
                  label="Documento"
                />
              </VCol>
              <VCol cols="12">
                <VCombobox
                  v-model="assignedRoles"
                  chips
                  clearable
                  multiple
                  closable-chips
                  clear-icon="tabler-circle-x"
                  :items="rolesList"
                  label="Roles asignados al usuario"
                  :rules="[requiredValidator]"
                 />
              </VCol>
            </VRow>
            <VCardText class="d-flex justify-end gap-3 flex-wrap">
              <VBtn
                color="secondary"
                variant="tonal"
                @click="closeUserCreateDialog"
              >
                Cancelar
              </VBtn>
              <VBtn type="submit">
                Crear
              </VBtn>
            </VCardText>
          </VCardText>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
