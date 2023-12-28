<script setup>

import { avatarText } from '@/@core/utils/formatters'
import { emailValidator, requiredValidator, phoneValidator } from '@/@core/utils/validators'
import { useProfileStores } from '@/stores/useProfile'
import { useCountriesStores } from '@/stores/useCountries'
import { useProvincesStores } from '@/stores/useProvinces'

const profileStores = useProfileStores()
const countriesStores = useCountriesStores()
const provincesStores = useProvincesStores()

const listCountries = ref([])
const listProvinces = ref([])
const listProvincesByCountry = ref([])

const refVForm = ref()
const isUserEditDialog = ref(false)

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
const avatar = ref('')

const avatarOld = ref('')
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

    let data = JSON.parse(localStorage.getItem('user_data') || 'null')

    user_id.value = data.id
    email.value = data.email
    username.value = data.username
    name.value = data.name
    last_name.value = data.last_name ?? ''
    phone.value = data.user_details?.phone
    address.value = data.user_details?.address
    document.value = data.user_details?.document
    province_id.value = data.user_details?.province.name
    provinceOld_id.value = data.user_details?.province_id
    countryOld_id.value = data.user_details?.province.country.name
    country_id.value = data.user_details?.province.country.name
    province.value = data.user_details?.province.name
    country.value = data.user_details?.province.country.name

    avatarOld.value = data.avatar
    avatar.value = data.avatar

    roles.value = data.roles
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
                    alert.value.message = 'Ocurrió un error, intente nuevamente o contacte con el administrador...!'
                    
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

const onImageSelected = event => {
  const file = event.target.files[0]

  if (!file) return
  // avatarOld.value = file

  URL.createObjectURL(file)

  resizeImage(file, 400, 400, 0.9)
    .then(async blob => {
      avatarOld.value = blob
      let r = await blobToBase64(blob)
      avatar.value = 'data:image/jpeg;base64,' + r
    })
}

const resizeImage = function(file, maxWidth, maxHeight, quality) {
  return new Promise((resolve, reject) => {
    const img = new Image()

    img.src = URL.createObjectURL(file)
    img.onload = () => {
      const canvas = document.createElement('canvas')
      const ctx = canvas.getContext('2d')

      let width = img.width
      let height = img.height

      if (maxWidth && width > maxWidth) {
        height *= maxWidth / width
        width = maxWidth
      }

      if (maxHeight && height > maxHeight) {
        width *= maxHeight / height
        height = maxHeight
      }

      canvas.width = width
      canvas.height = height

      ctx.drawImage(img, 0, 0, width, height)

      canvas.toBlob(blob => {
        resolve(blob)
      }, file.type, quality)
    }
    img.onerror = error => {
      reject(error)
    }
  })
}

const blobToBase64 = blob => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()

    reader.readAsDataURL(blob)
    reader.onload = () => {
      resolve(reader.result.split(',')[1])
    }
    reader.onerror = error => {
      reject(error)
    }
  })
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
  <VRow>
    <VCol cols="12">
      <VCard>
        <VCardText class="text-center pt-6">
          <VAvatar
            rounded
            :size="120"
            color="primary"
            variant="tonal"
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
                  Apellidos:
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
              color="primary"
              variant="tonal"
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
                  @change="onImageSelected"
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
</template>

<style lang="scss">
  .v-list-item-title {
    white-space: normal;
  }
</style>
