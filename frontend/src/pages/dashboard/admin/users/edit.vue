<script setup>

import { requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { useUsersStores } from '@/stores/useUsers'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  rolesList: {
    type: Object,
    required: true
  }

})

const emit = defineEmits([
  'update:isDrawerOpen',
  'close',
  'alert',
  'data'
])

const usersStores = useUsersStores()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()

const refFormEdit = ref()

const id = ref('')
const email = ref('')
const name = ref('')
const last_name = ref('')
const phone = ref('')
const address = ref('')
const username = ref('')
const document = ref('')
const provinceOld_id = ref('')
const province_id = ref('')
const province = ref('')
const country_id = ref('')
const countryOld_id = ref('')
const country = ref('')
const assignedRoles = ref([])

const advisor = ref({
  type: '',
  message: '',
  show: false
})

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])

const getProvinces = computed(() => {
  return listProvincesByCountry.value.map((province) => {
    return {
      title: province.name,
      value: province.id,
    }
  })
})

const loadCountries = () => {
  listCountries.value = countriesStores.getCountries
}

const loadProvinces = () => {
  listProvinces.value = provincesStores.getProvinces
}

const selectCountry = country => {
  if (country) {
    let _country = listCountries.value.find(item => item.name === country)
    country_id.value = _country.name
 
    province_id.value = ''
    
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

onMounted(async () => {

    await countriesStores.fetchCountries();
    await provincesStores.fetchProvinces();

    loadCountries()
    loadProvinces()
    selectCountry(countryOld_id.value)
    fetchData()
})

watchEffect(fetchData)

async function fetchData() {
    if (props.isDrawerOpen) {

        if (!(Object.entries(props.user).length === 0) && props.user.constructor === Object) {
            id.value = props.user.id
            email.value = props.user.email
            name.value = props.user.name
            last_name.value = props.user.last_name
            phone.value = props.user.user_detail?.phone
            address.value = props.user.user_detail?.address
            username.value = props.user.username
            document.value = props.user.user_detail?.document
            province_id.value = props.user.user_detail?.province.name
            provinceOld_id.value = props.user.user_detail?.province_id
            countryOld_id.value = props.user.user_detail?.province.country.name
            country_id.value = props.user.user_detail?.province.country.name
            province.value = props.user.user_detail?.province.name
            country.value = props.user.user_detail?.province.country.name

            assignedRoles.value = props.user.assignedRoles

      }
  }
}

const closeUserEditDialog = function(){
    emit('update:isDrawerOpen', false)
    emit('close')
}

const onSubmitEdit = () =>{
  refFormEdit.value?.validate().then(({ valid: isValid }) => {
    if (isValid) {

        let data = {
          name: name.value,
          email: email.value,
          last_name: last_name.value,
          phone: phone.value,
          address: address.value,
          username: username.value,
          document: document.value,
          province_id: (Number.isInteger(province_id.value)) ? province_id.value : provinceOld_id.value,
          roles: assignedRoles.value
        }

        usersStores.updateUser(data, id.value)
            .then(response => {
                closeUserEditDialog()

                window.scrollTo(0, 0)

                advisor.value.show = true
                advisor.value.type = 'success'
                advisor.value.message = 'Usuario actualizado!'

                emit('alert', advisor)
                emit('data')
                emit('close')

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

const getFlagCountry = country => {
  let val = listCountries.value.find(item => {
    return item.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "") === country.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "")
  })

  if(val)
    return 'https://hatscripts.github.io/circle-flags/flags/'+val.iso.toLowerCase()+'.svg'
  else
    return ''
}

</script>

<template>
    <!-- DIALOGO DE EDITAR -->
    <VDialog
        :model-value="props.isDrawerOpen"
        max-width="600"
        persistent
        >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeUserEditDialog" />

        <!-- Dialog Content -->
        <VCard title="Editar usuario">
          <VDivider class="mt-4"/>
            <VForm
                ref="refFormEdit"
                @submit.prevent="onSubmitEdit">
                <VCardText>
                    <VRow>
                        <VCol
                          md="6"
                          cols="12"
                        >
                          <VTextField
                            v-model="name"
                            label="Nombres"
                            :rules="[requiredValidator]"
                          />
                        </VCol>
                        <VCol
                          md="6"
                          cols="12"
                        >
                          <VTextField
                            v-model="last_name"
                            label="Apellidos"
                            :rules="[requiredValidator]"
                          />
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VTextField
                            v-model="email"
                            label="E-mail"
                            readonly
                          />
                        </VCol>
                        <VCol
                          md="6"
                          cols="12"
                        >
                          <VTextField
                            v-model="username"
                            label="Username"
                            :rules="[requiredValidator]"
                          />
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VTextField
                            v-model="phone"
                            label="Telefono"
                            placeholder="+(XX) XXXXXXXXX"
                            :rules="[requiredValidator, phoneValidator]"
                          />
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VTextField
                            v-model="address"
                            label="Direccion"
                            :rules="[requiredValidator]"
                          />
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <VAutocomplete
                            v-model="country_id"
                            label="País"
                            :rules="[requiredValidator]"
                            :items="listCountries"
                            item-title="name"
                            item-value="name"
                            :menu-props="{ maxHeight: '200px' }"
                            @update:model-value="selectCountry"
                          >
                          <template
                            v-if="country_id"
                            #prepend
                            >
                            <VAvatar
                              start
                              style="margin-top: -8px;"
                              size="36"
                              :image="getFlagCountry(country_id)"
                            />
                          </template>
                        </VAutocomplete>
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
                          <v-autocomplete
                              v-model="province_id"
                              label="Estado"
                              :rules="[requiredValidator]"
                              :items="getProvinces"
                              :menu-props="{ maxHeight: '200px' }"
                            />
                        </VCol>
                        <VCol
                          cols="12"
                          md="6"
                        >
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
                            @click="closeUserEditDialog"
                        >
                            Cancelar
                        </VBtn>
                        <VBtn type="submit">
                            Editar
                        </VBtn>
                    </VCardText>
                </VCardText>
            </VForm>
        </VCard>
    </VDialog>
</template>
