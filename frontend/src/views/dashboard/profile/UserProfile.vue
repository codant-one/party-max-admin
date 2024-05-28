<script setup>

import { avatarText } from '@/@core/utils/formatters'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { useProfileStores } from '@/stores/useProfile'

const props = defineProps({
  countries: {
    type: Object,
    required: true
  },
  provinces: {
    type: Object,
    required: true
  },
  user: {
    type: Object,
    required: true
  },
  avatarOld: {
    type: [String, Object],
    required: true
  },
  avatar: {
    type: String,
    required: true
  }
})

const emit = defineEmits([
  'onImageSelected',
])

const profileStores = useProfileStores()

const listCountries = ref(props.countries)
const listProvinces = ref(props.provinces)
const listProvincesByCountry = ref([])

const refVForm = ref()
const isUserEditDialog = ref(false)

const userData = ref(props.user)
const user_id = ref('')
const email = ref('')
const name = ref('')
const username = ref('')
const last_name = ref('')
const phone = ref('')
const address = ref('')
const document = ref('')
const provinceOld_id = ref('')
const province_id = ref('')
const province = ref('')
const countryOld_id = ref('')
const country_id = ref('')
const country = ref('')
const avatar = ref(props.avatar)

const avatarOld = ref(props.avatarOld)
const roles = ref('')

const alert = ref({
    message: '',
    show: false,  
    type: ''
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
    let _country = listCountries.value.find(item => item.name === country)
    country_id.value = _country.name
 
    province_id.value = ''
    
    listProvincesByCountry.value = listProvinces.value.filter(item => item.country_id === _country.id)
  }
}

watch(() =>  
  props.avatar, (avatar_) => {
    avatar.value = avatar_
  });

watch(() => 
  props.avatarOld, (avatarOld_) => {
    avatarOld.value = avatarOld_
  });

watchEffect(fetchData)

async function fetchData() { 

    user_id.value = userData.value.id
    email.value = userData.value.email
    username.value = userData.value.username
    name.value = userData.value.name
    last_name.value = userData.value.last_name ?? ''
    phone.value = userData.value.user_details?.phone
    address.value = userData.value.user_details?.address
    document.value = userData.value.user_details?.document
    countryOld_id.value = userData.value.user_details?.province.country.name
    country_id.value = userData.value.user_details?.province.country.name
    province.value = userData.value.user_details?.province.name
    country.value = userData.value.user_details?.province.country.name
    roles.value = userData.value.roles

    selectCountry(countryOld_id.value)

    province_id.value = userData.value.user_details?.province.name
    provinceOld_id.value = userData.value.user_details?.province_id
}

const resetAvatar = () => {
  avatar.value = null
}

const onSubmit = () =>{
    refVForm.value?.validate().then(({ valid: isValid }) => {

        if (isValid) {

            let formData = new FormData()
      
            formData.append('user_id', user_id.value)
            formData.append('email', email.value)
            formData.append('username', username.value)
            formData.append('name', name.value)
            formData.append('last_name', last_name.value)
            formData.append('phone', phone.value)
            formData.append('address', address.value)
            formData.append('document', document.value === null ? '' : document.value)
            formData.append('province_id', (Number.isInteger(province_id.value)) ? province_id.value : provinceOld_id.value)
            formData.append('image', avatarOld.value)

            profileStores.updateData(formData)
                .then(response => {    

                    window.scrollTo(0, 0)
                    
                    alert.value.type = 'success'
                    alert.value.message = 'Información personal actualizada. se recargara la página automaticamente para observar los efectos..!'
                    alert.value.show = true
                    
                    localStorage.setItem('user_data', JSON.stringify(response.user_data))
                    
                    fetchData()

                    setTimeout(() => {
                        alert.value.show = false,
                        alert.value.message = ''
                        location.reload()
                    }, 5000)

                }).catch(error => {
                    alert.value.type = 'error'
                    alert.value.show = true
                    alert.value.message = 'Se ha producido un error...! (Server Error)'
                    
                    setTimeout(() => {
                        alert.value.show = false,
                        alert.value.message = ''
                    }, 5000) 
                })
        }
            
    })
}

const deleteAvatar = ()=>{
    avatarOld.value = null
    resetAvatar()
}

const showUserEditDialog = u =>{
  isUserEditDialog.value = true
}

const closeUserEditDialog = ()=>{
  isUserEditDialog.value = false
  fetchData()
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
  <section>
    <VRow>
      <VCol cols="12">
        <VCard>
          <VCardText class="text-center pt-6">
            <VAvatar
              rounded
              :size="120"
              :color="avatar ? 'default' : 'primary'"
              :variant="avatar ? 'outlined' : 'tonal'"
            >
              <VImg
                v-if="avatar"
                style="border-radius: 6px;"
                :src="avatar"
              />
              <span
                v-else
                class="text-5xl font-weight-semibold"
              >
                {{ avatarText(name) }}
              </span>
            </VAvatar>
            <h6 class="text-h6 mt-4">
              {{ name.toUpperCase() }} {{ last_name.toUpperCase() }}
            </h6>

            <!-- 👉 Role chip -->
            <VChip
              v-for="rol in roles"
              :key="rol"
              label
              size="small"
              class="text-capitalize mt-4 mr-1"
            >
              {{ rol.name }}
            </VChip>
          </VCardText>

          <VDivider />

          <!-- 👉 Details -->
          <VCardText>
            <p class="text-sm text-uppercase text-disabled">
              Detalles
            </p>

            <VList class="card-list mt-2">
              <VListItem>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Nombre:
                    <span class="text-body-2">
                      {{ name }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Apellido:
                    <span class="text-body-2">
                      {{ last_name }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Email:
                    <span class="text-body-2">
                      {{ email }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Username:
                    <span class="text-body-2">
                      {{ username }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                      Teléfono:
                    <span class="text-body-2">
                      {{ phone }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Dirección:
                    <span class="text-body-2">
                      {{ address }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Estado:
                    <span class="text-body-2">
                      {{ province }}
                    </span>
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    País:
                    <span class="text-body-2 me-2">
                      {{ country }}
                    </span>
                    <VAvatar
                      start
                      size="25"
                      :image="getFlagCountry(country)"
                    />
                  </h6>
                </VListItemTitle>
                <VListItemTitle>
                  <h6 class="text-base font-weight-semibold">
                    Documento:
                    <span class="text-body-2">
                      {{ document }}
                    </span>
                  </h6>
                </VListItemTitle>
              </VListItem>
            </VList>
          </VCardText>

          <!-- 👉 Edit and Suspend button -->
          <VCardText class="d-flex justify-center">
            <VBtn
              variant="elevated"
              class="me-3"
              @click="showUserEditDialog()"
            >
              Editar
            </VBtn>
          </VCardText>
        </VCard>
      </VCol>

      <!-- DIALOGO DE EDITAR INFORMACION PERSONAL -->
      <VDialog
        v-model="isUserEditDialog"
        max-width="800"
        persistent
      >
        <!-- Dialog close btn -->
        <DialogCloseBtn @click="closeUserEditDialog" />

        <!-- Dialog Content -->
        <VCard title="Editar Información Personal">    
          <VDivider class="mt-4"/>    
          <VCol 
            v-if="alert.show" 
            cols="12" 
          >
            <VAlert
              v-if="alert.show"
              :type="alert.type"
            >
              {{ alert.message }}
            </VAlert>
          </VCol>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VCardText class="d-flex">
              <VAvatar
                rounded
                size="100"
                class="me-6"
                :color="avatar ? 'default' : 'primary'"
                :variant="avatar ? 'outlined' : 'tonal'"
              >
                <VImg
                  v-if="avatar"
                  style="border-radius: 6px;"
                  :src="avatar"
                />
                <span
                  v-else
                  class="text-5xl font-weight-semibold"
                >
                  {{ avatarText(name) }}
                </span>
              </VAvatar>

              <!-- 👉 Upload Photo -->
              <div class="d-flex flex-column justify-center gap-4">
                <div class="d-flex flex-wrap gap-2">
                  <VFileInput                          
                    label="Avatar"
                    accept="image/png, image/jpeg, image/bmp"
                    placeholder="Avatar"
                    prepend-icon="tabler-camera"
                    @change="$emit('onImageSelected', $event)"
                    @click:clear="resetAvatar"
                  />
                </div>
                <p class="text-body-1 mb-0">
                  Formatos Permitidos JPG, GIF, PNG.
                </p>
                <VBtn 
                  color="secondary"
                  variant="tonal"
                  @click="deleteAvatar"
                >
                  Eliminar Avatar
                </VBtn>
              </div>
            </VCardText>

            <VDivider />

            <VCardText class="pt-2 mt-6">
              <VRow>
                <VCol
                  md="6"
                  cols="12"
                >
                  <VTextField
                    v-model="name"
                    label="Nombres"
                    :rules="[requiredValidator]"
                    readonly
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
                    type="email"
                    :rules="[requiredValidator, emailValidator]"
                    readonly
                  />
                </VCol>
                <VCol
                  cols="12"
                  md="6"
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
                    @update:model-value="selectCountry">
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
                  <VAutocomplete
                    v-model="province_id"
                    label="Estado3"
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

                <!-- 👉 Form Actions -->
                <VCol
                  cols="12"
                  class="d-flex flex-wrap gap-4 justify-center"
                >
                  <VBtn type="submit">
                    Guardar Cambios
                  </VBtn>
                </VCol>
              </VRow>
            </VCardText>
          </VForm>
        </VCard>      
      </VDialog> 
    </VRow>
  </section>
</template>

<style lang="scss">
  .v-list-item-title {
    white-space: normal;
  }
</style>
